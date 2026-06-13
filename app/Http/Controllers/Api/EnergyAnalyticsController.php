<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\EnergyAnalyticsRequest;
use App\Http\Resources\EnergyReportResource;
use App\Http\Resources\EnergyUsageResource;
use App\Services\EnergyAnalyticsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Collection;
use Symfony\Component\HttpFoundation\Response;

class EnergyAnalyticsController extends Controller
{
    private const REPORT_PAGE_SIZE = 25;

    public function __construct(private readonly EnergyAnalyticsService $energyService) {}

    public function summary(EnergyAnalyticsRequest $request): JsonResponse
    {
        [$startDate, $endDate] = $request->dateRange();

        return response()->json($this->energyService->getSummary($startDate, $endDate));
    }

    public function daily(EnergyAnalyticsRequest $request): JsonResponse
    {
        [$startDate, $endDate] = $request->dateRange();

        return response()->json([
            'data' => EnergyUsageResource::collection(
                $this->energyService->getDailyAnalytics($startDate, $endDate)
            )->resolve($request),
        ]);
    }

    public function weekly(EnergyAnalyticsRequest $request): JsonResponse
    {
        [$startDate, $endDate] = $request->dateRange();

        return response()->json([
            'data' => EnergyUsageResource::collection(
                $this->energyService->getWeeklyAnalytics($startDate, $endDate)
            )->resolve($request),
        ]);
    }

    public function monthly(EnergyAnalyticsRequest $request): JsonResponse
    {
        [$startDate, $endDate] = $request->dateRange();

        return response()->json([
            'data' => EnergyUsageResource::collection(
                $this->energyService->getMonthlyAnalytics($startDate, $endDate)
            )->resolve($request),
        ]);
    }

    public function report(EnergyAnalyticsRequest $request): JsonResponse
    {
        [$startDate, $endDate] = $request->dateRange();
        $report = $this->energyService->getEnergyReport($startDate, $endDate);
        $total = $report->count();
        $lastPage = max(1, (int) ceil($total / self::REPORT_PAGE_SIZE));
        $page = min((int) $request->validated('page', 1), $lastPage);
        $rows = $report->forPage($page, self::REPORT_PAGE_SIZE)->values();

        return response()->json([
            'data' => EnergyReportResource::collection(
                $rows
            )->resolve($request),
            'meta' => [
                'current_page' => $page,
                'from' => $rows->isEmpty()
                    ? null
                    : (($page - 1) * self::REPORT_PAGE_SIZE) + 1,
                'last_page' => $lastPage,
                'per_page' => self::REPORT_PAGE_SIZE,
                'to' => $rows->isEmpty()
                    ? null
                    : (($page - 1) * self::REPORT_PAGE_SIZE) + $rows->count(),
                'total' => $total,
            ],
        ]);
    }

    public function exportCsv(EnergyAnalyticsRequest $request): Response
    {
        [$startDate, $endDate] = $request->dateRange();
        $summary = $this->energyService->getSummary($startDate, $endDate);
        $rows = $this->energyService->getEnergyReport($startDate, $endDate);
        $stream = fopen('php://temp', 'r+');

        fputcsv($stream, ['Date', 'Energy Used (kWh)', "Estimated Cost ({$summary['currency']})", 'Peak Power (W)', 'Fault Count']);
        foreach ($rows as $row) {
            fputcsv($stream, [
                $row['date'],
                $row['energy_used'],
                $row['estimated_cost'],
                $row['peak_power'],
                $row['fault_count'],
            ]);
        }

        rewind($stream);
        $csv = stream_get_contents($stream);
        fclose($stream);

        return response($csv, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="energy-report.csv"',
        ]);
    }

    public function exportPdf(EnergyAnalyticsRequest $request): Response
    {
        [$startDate, $endDate] = $request->dateRange();
        $summary = $this->energyService->getSummary($startDate, $endDate);
        $rows = $this->energyService->getEnergyReport($startDate, $endDate);
        $reportLines = [];

        foreach ($rows as $row) {
            $reportLines[] = sprintf(
                '%-10s %12.2f %12.2f %9.0f %7d',
                $row['date'],
                $row['energy_used'],
                $row['estimated_cost'],
                $row['peak_power'],
                $row['fault_count'],
            );
        }

        $pages = collect($reportLines)
            ->chunk(42)
            ->values()
            ->map(fn (Collection $pageRows, int $index) => [
                'SmartGuard Energy Consumption Report'.($index > 0 ? ' (continued)' : ''),
                "Tariff: {$summary['currency']} {$summary['tariff_rate']} per kWh",
                'Date       Energy (kWh)  Cost        Peak (W)  Faults',
                ...$pageRows->all(),
            ])
            ->all();

        if ($pages === []) {
            $pages = [[
                'SmartGuard Energy Consumption Report',
                "Tariff: {$summary['currency']} {$summary['tariff_rate']} per kWh",
                'No energy records matched the selected filters.',
            ]];
        }

        return response($this->buildPdf($pages), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="energy-report.pdf"',
        ]);
    }

    private function buildPdf(array $pages): string
    {
        $pageCount = count($pages);
        $fontObjectId = 3 + ($pageCount * 2);
        $pageObjectIds = [];
        $objects = [
            1 => '<< /Type /Catalog /Pages 2 0 R >>',
        ];

        foreach ($pages as $index => $lines) {
            $pageObjectId = 3 + ($index * 2);
            $contentObjectId = $pageObjectId + 1;
            $pageObjectIds[] = "{$pageObjectId} 0 R";
            $content = $this->pdfPageContent($lines);
            $objects[$pageObjectId] = "<< /Type /Page /Parent 2 0 R /MediaBox [0 0 612 842] /Resources << /Font << /F1 {$fontObjectId} 0 R >> >> /Contents {$contentObjectId} 0 R >>";
            $objects[$contentObjectId] = '<< /Length '.strlen($content)." >>\nstream\n{$content}\nendstream";
        }

        $objects[2] = '<< /Type /Pages /Kids ['.implode(' ', $pageObjectIds)."] /Count {$pageCount} >>";
        $objects[$fontObjectId] = '<< /Type /Font /Subtype /Type1 /BaseFont /Courier >>';
        ksort($objects);

        $pdf = "%PDF-1.4\n";
        $offsets = [0];
        foreach ($objects as $objectId => $object) {
            $offsets[$objectId] = strlen($pdf);
            $pdf .= "{$objectId} 0 obj\n{$object}\nendobj\n";
        }

        $xref = strlen($pdf);
        $objectCount = count($objects) + 1;
        $pdf .= "xref\n0 {$objectCount}\n0000000000 65535 f \n";
        for ($objectId = 1; $objectId < $objectCount; $objectId++) {
            $pdf .= sprintf("%010d 00000 n \n", $offsets[$objectId]);
        }

        return $pdf."trailer\n<< /Size {$objectCount} /Root 1 0 R >>\nstartxref\n{$xref}\n%%EOF";
    }

    private function pdfPageContent(array $lines): string
    {
        $content = "BT\n/F1 10 Tf\n50 790 Td\n";

        foreach ($lines as $index => $line) {
            if ($index > 0) {
                $content .= "0 -16 Td\n";
            }

            $escaped = str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], $line);
            $content .= "({$escaped}) Tj\n";
        }

        return $content.'ET';
    }
}
