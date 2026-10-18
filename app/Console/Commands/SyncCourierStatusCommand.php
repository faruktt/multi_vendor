<?php

namespace App\Console\Commands;

use App\Models\CourierSetting;
use App\Models\Sale;
use App\Services\Courier\CourierManager;
use App\Services\Courier\CourierStatusHandler;
use Illuminate\Console\Command;

class SyncCourierStatusCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'courier:sync-status {--limit=100 : Number of active orders to check}';

    /**
     * The console command description.
     */
    protected $description = 'Fetch live parcel statuses from couriers and auto-cancel orders if cancelled/returned by courier';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $limit = (int) $this->option('limit') ?: 100;

        $this->info("Scanning pending courier parcels (limit: {$limit})...");

        // Find orders sent to courier that are not completed or cancelled
        $sales = Sale::withoutGlobalScopes()
            ->whereNotNull('courier_tracking_code')
            ->whereNotNull('courier_name')
            ->whereNotIn('order_status', ['completed', 'cancelled'])
            ->orderBy('id', 'desc')
            ->limit($limit)
            ->get();

        if ($sales->isEmpty()) {
            $this->info("No active courier parcels to sync.");
            return Command::SUCCESS;
        }

        $courierSettings = CourierSetting::all()->keyBy('code');
        $checked = 0;
        $cancelledCount = 0;

        foreach ($sales as $sale) {
            $courier = $courierSettings->get($sale->courier_name);
            if (!$courier) {
                continue;
            }

            try {
                $service = CourierManager::resolve($courier);
                $res = $service->checkStatus($sale->courier_tracking_code);

                if ($res['success'] && !empty($res['status'])) {
                    $checked++;
                    $handled = CourierStatusHandler::applyStatus($sale, $res['status'], $courier->name, $res['raw'] ?? null);

                    if ($handled['is_cancelled']) {
                        $cancelledCount++;
                        $this->warn("Sale #{$sale->id} ({$sale->invoice_no}) -> Courier status: '{$res['status']}' => AUTO CANCELLED!");
                    } else {
                        $this->line("Sale #{$sale->id} ({$sale->invoice_no}) -> Courier status: '{$res['status']}'");
                    }
                }
            } catch (\Throwable $e) {
                $this->error("Failed to check sale #{$sale->id}: " . $e->getMessage());
            }
        }

        $this->info("Finished! Checked: {$checked}, Auto-cancelled: {$cancelledCount}");

        return Command::SUCCESS;
    }
}
