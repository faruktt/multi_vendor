<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('landing_pages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vendor_id')->nullable()->constrained('vendors')->nullOnDelete();
            $table->string('title');
            $table->string('slug')->unique();
            $table->string('status', 20)->default('active'); // active, inactive
            $table->string('page_theme', 30)->default('emerald'); // emerald, blue, dark, purple, rose, amber, gold

            // Hero section
            $table->string('hero_badge')->nullable();
            $table->text('hero_title')->nullable();
            $table->text('hero_subtitle')->nullable();
            $table->string('hero_image')->nullable();
            $table->string('hero_video_url')->nullable();
            $table->dateTime('countdown_end')->nullable();
            $table->unsignedInteger('countdown_hours')->default(12);

            // Rich content & sections
            $table->json('features')->nullable();
            $table->longText('specifications')->nullable();
            $table->json('gallery_images')->nullable();
            $table->json('why_choose_us')->nullable();
            $table->json('testimonials')->nullable();
            $table->json('faq')->nullable();

            // Pricing & Delivery
            $table->boolean('free_shipping')->default(false);
            $table->decimal('delivery_charge_inside', 10, 2)->nullable();
            $table->decimal('delivery_charge_sub_dhaka', 10, 2)->nullable();
            $table->decimal('delivery_charge_outside', 10, 2)->nullable();

            // CTA & Form custom labels
            $table->string('cta_button_text')->default('অর্ডার করতে এখানে ক্লিক করুন');
            $table->string('order_form_title')->default('অর্ডার করতে আপনার সঠিক তথ্য দিন');

            // Tracking & Styling
            $table->string('fb_pixel_id')->nullable();
            $table->string('tiktok_pixel_id')->nullable();
            $table->text('custom_css')->nullable();

            // Analytics
            $table->unsignedBigInteger('views_count')->default(0);
            $table->unsignedBigInteger('orders_count')->default(0);

            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('landing_page_products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('landing_page_id')->constrained('landing_pages')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->string('package_title')->nullable();
            $table->string('package_subtitle')->nullable();
            $table->decimal('custom_price', 12, 2)->nullable();
            $table->decimal('custom_old_price', 12, 2)->nullable();
            $table->boolean('is_default')->default(false);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['landing_page_id', 'sort_order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('landing_page_products');
        Schema::dropIfExists('landing_pages');
    }
};

