<?php

namespace App\Console\Commands;

use App\Models\Device;
use App\Services\EnergySummaryService;
use Illuminate\Console\Command;

class RebuildEnergySummaries extends Command
{
    protected $signature = 'energy:rebuild-summaries {--device= : Device code to rebuild}';

    protected $description = 'Rebuild daily and monthly energy summaries from stored telemetry';

    public function handle(EnergySummaryService $energySummaryService): int
    {
        $devices = Device::query()
            ->when(
                $this->option('device'),
                fn ($query, $deviceCode) => $query->where('device_code', $deviceCode),
            )
            ->get();

        if ($devices->isEmpty()) {
            $this->warn('No matching devices were found.');

            return self::SUCCESS;
        }

        foreach ($devices as $device) {
            $summaryCount = $energySummaryService->rebuildDevice($device);
            $this->info("{$device->device_code}: rebuilt {$summaryCount} daily summaries.");
        }

        return self::SUCCESS;
    }
}
