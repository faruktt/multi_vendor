<?php

namespace App\Console\Commands;

use App\Models\Product;
use App\Services\ProductImageService;
use Illuminate\Console\Command;

class ResizeProductImages extends Command
{
    protected $signature = 'products:resize-images {--force : Re-process all images even if already 800x1000}';
    protected $description = 'Standardize and resize all product images to 800x1000 (4:5) canvas without cropping';

    public function handle(): int
    {
        $this->info('Starting product image standardization...');

        $products = Product::withoutGlobalScopes()->get();
        $processed = 0;
        $skipped = 0;
        $failed = 0;

        foreach ($products as $product) {
            $images = $product->images_list ?? [];
            if (empty($images) && $product->image) {
                $images = [$product->image];
            }

            foreach ($images as $img) {
                if (!$img) continue;

                $path = str_contains($img, '/')
                    ? public_path('uploads/' . $img)
                    : public_path('uploads/products/' . $img);

                if (!file_exists($path)) {
                    $skipped++;
                    continue;
                }

                $size = @getimagesize($path);
                if ($size && $size[0] === ProductImageService::TARGET_WIDTH && $size[1] === ProductImageService::TARGET_HEIGHT && !$this->option('force')) {
                    $skipped++;
                    continue;
                }

                $ok = ProductImageService::reprocessInPlace($path);
                if ($ok) {
                    $processed++;
                    $this->line("  ✓ Processed: {$img} for [{$product->name}]");
                } else {
                    $failed++;
                    $this->warn("  ✗ Failed to process: {$img}");
                }
            }
        }

        $this->info("Completed! Processed: {$processed}, Skipped/Already OK: {$skipped}, Failed: {$failed}");

        return Command::SUCCESS;
    }
}
