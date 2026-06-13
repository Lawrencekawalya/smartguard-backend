<?php

namespace App\Services;

use App\Models\DeviceReading;
use App\Models\EnergySetting;
use App\Models\EnergySummary;
use App\Models\Fault;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class EnergyAnalyticsService
{
    private const DEFAULT_TARIFF_RATE = 805;

    private const DEFAULT_CURRENCY = 'UGX';

    public function getSummary(?string $startDate = null, ?string $endDate = null): array
    {
        $setting = $this->getSetting();
        $rate = (float) $setting->tariff_rate;
        $todayKwh = EnergySummary::whereDate('summary_date', Carbon::today())->sum('daily_kwh');
        $weeklyKwh = EnergySummary::whereBetween('summary_date', [
            Carbon::now()->startOfWeek()->toDateString(),
            Carbon::now()->endOfWeek()->toDateString(),
        ])->sum('daily_kwh');
        $monthlyKwh = EnergySummary::whereMonth('summary_date', Carbon::now()->month)
            ->whereYear('summary_date', Carbon::now()->year)
            ->sum('daily_kwh');
        $selectedKwh = $this->dailyQuery($startDate, $endDate, 30)->sum('daily_kwh');

        return [
            'today_kwh' => (float) $todayKwh,
            'weekly_kwh' => (float) $weeklyKwh,
            'monthly_kwh' => (float) $monthlyKwh,
            'total_kwh' => (float) $selectedKwh,
            'estimated_cost' => round((float) (($startDate || $endDate ? $selectedKwh : $monthlyKwh) * $rate), 2),
            'tariff_rate' => $rate,
            'currency' => $setting->currency,
            'tariff_description' => $setting->description,
            'cost_analysis' => [
                $this->costPeriod('Today', (float) $todayKwh, $rate),
                $this->costPeriod('Week', (float) $weeklyKwh, $rate),
                $this->costPeriod('Month', (float) $monthlyKwh, $rate),
            ],
        ];
    }

    public function getDailyAnalytics(?string $startDate = null, ?string $endDate = null): Collection
    {
        return $this->dailyQuery($startDate, $endDate, 30)
            ->sortBy('date')
            ->values();
    }

    public function getWeeklyAnalytics(?string $startDate = null, ?string $endDate = null): Collection
    {
        return $this->dailyQuery($startDate, $endDate, 84)
            ->groupBy(fn ($item) => Carbon::parse($item->date)->startOfWeek()->toDateString())
            ->map(fn (Collection $days, string $week) => (object) [
                'week' => Carbon::parse($week)->format('\W\e\e\k W, Y'),
                'week_start' => $week,
                'weekly_kwh' => round((float) $days->sum('daily_kwh'), 6),
            ])
            ->sortBy('week_start')
            ->values();
    }

    public function getMonthlyAnalytics(?string $startDate = null, ?string $endDate = null): Collection
    {
        return $this->dailyQuery($startDate, $endDate, 365)
            ->groupBy(fn ($item) => Carbon::parse($item->date)->format('Y-m'))
            ->map(fn (Collection $days, string $month) => (object) [
                'month' => Carbon::createFromFormat('Y-m', $month)->format('M Y'),
                'month_key' => $month,
                'monthly_kwh' => round((float) $days->sum('daily_kwh'), 6),
            ])
            ->sortBy('month_key')
            ->values();
    }

    public function getEnergyReport(?string $startDate = null, ?string $endDate = null): Collection
    {
        $rate = (float) $this->getSetting()->tariff_rate;
        $summaries = $this->dailyQuery($startDate, $endDate);
        $dates = $summaries->pluck('date');
        $firstDate = $dates->min();
        $lastDate = $dates->max();

        if (! $firstDate || ! $lastDate) {
            return collect();
        }

        $peakPower = DeviceReading::query()
            ->whereBetween('created_at', [
                Carbon::parse($firstDate)->startOfDay(),
                Carbon::parse($lastDate)->endOfDay(),
            ])
            ->get(['created_at', 'real_power'])
            ->groupBy(fn (DeviceReading $reading) => $reading->created_at->toDateString())
            ->map->max('real_power');
        $faultCounts = Fault::query()
            ->whereBetween('occurred_at', [
                Carbon::parse($firstDate)->startOfDay(),
                Carbon::parse($lastDate)->endOfDay(),
            ])
            ->get(['occurred_at'])
            ->groupBy(fn (Fault $fault) => Carbon::parse($fault->occurred_at)->toDateString())
            ->map->count();

        return $summaries->sortByDesc('date')->values()->map(function ($item) use ($rate, $peakPower, $faultCounts) {
            return [
                'date' => $item->date,
                'energy_used' => (float) $item->daily_kwh,
                'estimated_cost' => round((float) $item->daily_kwh * $rate, 2),
                'peak_power' => (float) ($peakPower->get($item->date) ?? 0),
                'fault_count' => (int) ($faultCounts->get($item->date) ?? 0),
            ];
        });
    }

    public function getSetting(): EnergySetting
    {
        return EnergySetting::firstOrCreate([], [
            'tariff_rate' => self::DEFAULT_TARIFF_RATE,
            'currency' => self::DEFAULT_CURRENCY,
            'description' => 'UMEME Residential Tariff',
        ]);
    }

    private function dailyQuery(
        ?string $startDate,
        ?string $endDate,
        ?int $defaultDays = null
    ): Collection {
        $query = EnergySummary::query()
            ->selectRaw('summary_date as date, SUM(daily_kwh) as daily_kwh')
            ->groupBy('summary_date')
            ->orderByDesc('summary_date');

        if ($startDate) {
            $query->whereDate('summary_date', '>=', $startDate);
        }

        if ($endDate) {
            $query->whereDate('summary_date', '<=', $endDate);
        }

        if (! $startDate && ! $endDate && $defaultDays !== null) {
            $query->limit($defaultDays);
        }

        return $query->get();
    }

    private function costPeriod(string $period, float $energy, float $rate): array
    {
        return [
            'period' => $period,
            'energy_kwh' => $energy,
            'tariff_rate' => $rate,
            'cost' => round($energy * $rate, 2),
        ];
    }
}
