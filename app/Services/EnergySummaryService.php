<?php

namespace App\Services;

use App\Models\Device;
use App\Models\DeviceReading;
use App\Models\EnergySummary;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;

class EnergySummaryService
{
    public function recordReading(
        Device $device,
        DeviceReading $reading,
        ?DeviceReading $previousReading
    ): EnergySummary {
        $readingDate = $reading->created_at->toDateString();
        $delta = $this->consumptionDelta($reading, $previousReading);

        $summary = EnergySummary::query()
            ->where('device_id', $device->id)
            ->whereDate('summary_date', $readingDate)
            ->lockForUpdate()
            ->first();

        if (! $summary) {
            $summary = new EnergySummary([
                'device_id' => $device->id,
                'summary_date' => $readingDate,
                'daily_kwh' => 0,
                'monthly_kwh' => 0,
            ]);
        }

        $summary->daily_kwh = round((float) $summary->daily_kwh + $delta, 6);
        $summary->monthly_kwh = $this->monthConsumption(
            $device,
            $reading->created_at,
            $readingDate,
            (float) $summary->daily_kwh,
        );
        $summary->save();

        return $summary;
    }

    public function rebuildDevice(Device $device): int
    {
        $readings = $device->readings()
            ->whereNotNull('created_at')
            ->orderBy('created_at')
            ->orderBy('id')
            ->cursor();

        $dailyTotals = $this->dailyTotals($readings);

        EnergySummary::query()
            ->where('device_id', $device->id)
            ->delete();

        $monthlyRunningTotals = [];

        foreach ($dailyTotals as $date => $dailyKwh) {
            $month = Carbon::parse($date)->format('Y-m');
            $monthlyRunningTotals[$month] = ($monthlyRunningTotals[$month] ?? 0) + $dailyKwh;

            EnergySummary::create([
                'device_id' => $device->id,
                'summary_date' => $date,
                'daily_kwh' => round($dailyKwh, 6),
                'monthly_kwh' => round($monthlyRunningTotals[$month], 6),
            ]);
        }

        return $dailyTotals->count();
    }

    private function consumptionDelta(
        DeviceReading $reading,
        ?DeviceReading $previousReading
    ): float {
        if (! $previousReading) {
            return 0;
        }

        return round(max(
            0,
            (float) $reading->energy_kwh - (float) $previousReading->energy_kwh,
        ), 6);
    }

    private function monthConsumption(
        Device $device,
        CarbonInterface $readingTime,
        string $readingDate,
        float $currentDailyKwh
    ): float {
        $previousDays = EnergySummary::query()
            ->where('device_id', $device->id)
            ->whereBetween('summary_date', [
                $readingTime->copy()->startOfMonth()->toDateString(),
                $readingTime->copy()->endOfMonth()->toDateString(),
            ])
            ->whereDate('summary_date', '!=', $readingDate)
            ->sum('daily_kwh');

        return round((float) $previousDays + $currentDailyKwh, 6);
    }

    private function dailyTotals(iterable $readings): Collection
    {
        $previousReading = null;
        $dailyTotals = collect();

        foreach ($readings as $reading) {
            $date = $reading->created_at->toDateString();
            $dailyTotals->put(
                $date,
                (float) $dailyTotals->get($date, 0)
                    + $this->consumptionDelta($reading, $previousReading),
            );
            $previousReading = $reading;
        }

        return $dailyTotals;
    }
}
