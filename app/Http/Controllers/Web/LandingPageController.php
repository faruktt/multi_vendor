<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\LandingPage;
use App\Models\LandingPageProduct;
use App\Models\Product;
use App\Models\Vendor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class LandingPageController extends Controller
{
    public function index(Request $request)
    {
        $query = LandingPage::with(['landingPageProducts.product', 'creator'])
            ->withCount('products');

        if ($request->filled('q')) {
            $search = trim($request->q);
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('slug', 'like', "%{$search}%")
                  ->orWhere('hero_title', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $landingPages = $query->latest()->paginate(15)->withQueryString();

        // High level analytics
        $totalCount    = LandingPage::count();
        $activeCount   = LandingPage::where('status', 'active')->count();
        $totalViews    = LandingPage::sum('views_count');
        $totalOrders   = LandingPage::sum('orders_count');

        return view('admin.landing-pages.index', compact(
            'landingPages',
            'totalCount',
            'activeCount',
            'totalViews',
            'totalOrders'
        ));
    }

    public function create()
    {
        $onlineStore = Vendor::onlineStore();
        
        // Fetch active products with images & variants for selection
        $products = Product::withoutGlobalScopes()
            ->where('status', 'active')
            ->select(['id', 'name', 'sku', 'price', 'old_price', 'image', 'stock_qty'])
            ->with(['variants:id,product_id,color_label,size_label,price,stock_qty'])
            ->orderBy('name')
            ->get();

        return view('admin.landing-pages.create', compact('products', 'onlineStore'));
    }

    public function store(Request $request)
    {
        @ini_set('memory_limit', '512M');
        @ini_set('max_execution_time', '300');

        $request->validate([
            'title'                      => 'required|string|max:255',
            'slug'                       => 'nullable|string|max:255|unique:landing_pages,slug',
            'status'                     => 'required|in:active,inactive',
            'page_theme'                 => 'required|string|in:emerald,blue,dark,purple,rose,amber',
            'hero_badge'                 => 'nullable|string|max:255',
            'hero_title'                 => 'nullable|string|max:500',
            'hero_subtitle'              => 'nullable|string|max:1000',
            'hero_video_url'             => 'nullable|string|max:500',
            'hero_image'                 => 'nullable|image|max:15360',
            'gallery_images.*'           => 'nullable|image|max:15360',
            'countdown_hours'            => 'nullable|integer|min:1|max:720',
            'products'                   => 'required|array|min:1',
            'products.*.product_id'      => 'required|exists:products,id',
            'products.*.package_title'   => 'nullable|string|max:255',
            'products.*.package_subtitle'=> 'nullable|string|max:255',
            'products.*.custom_price'    => 'nullable|numeric|min:0',
            'products.*.custom_old_price'=> 'nullable|numeric|min:0',
            'delivery_charge_inside'     => 'nullable|numeric|min:0',
            'delivery_charge_sub_dhaka'  => 'nullable|numeric|min:0',
            'delivery_charge_outside'    => 'nullable|numeric|min:0',
            'cta_button_text'            => 'nullable|string|max:255',
            'order_form_title'           => 'nullable|string|max:255',
            'fb_pixel_id'                => 'nullable|string|max:100',
            'tiktok_pixel_id'            => 'nullable|string|max:100',
            'custom_css'                 => 'nullable|string',
        ]);

        $onlineStore = Vendor::onlineStore();

        // Process rich specifications & convert any inline base64 images to stored files
        $specifications = $this->processSpecifications($request->specifications);

        // Generate slug
        $slug = $request->filled('slug') ? Str::slug($request->slug) : Str::slug($request->title);
        $originalSlug = $slug;
        $count = 1;
        while (LandingPage::where('slug', $slug)->exists()) {
            $slug = "{$originalSlug}-" . (++$count);
        }

        DB::transaction(function () use ($request, $slug, $onlineStore, $specifications) {
            // Upload Hero Image
            $heroImagePath = null;
            if ($request->hasFile('hero_image')) {
                $heroImagePath = $request->file('hero_image')->store('landing', 'uploads');
            }

            // Upload Gallery Images
            $galleryPaths = [];
            if ($request->hasFile('gallery_images')) {
                foreach ($request->file('gallery_images') as $file) {
                    $galleryPaths[] = $file->store('landing/gallery', 'uploads');
                }
            }

            // Clean Features
            $features = [];
            if ($request->filled('features') && is_array($request->features)) {
                foreach ($request->features as $item) {
                    if (!empty($item['title'])) {
                        $features[] = [
                            'icon'  => $item['icon'] ?? 'fa-check',
                            'title' => trim($item['title']),
                            'desc'  => trim($item['desc'] ?? ''),
                        ];
                    }
                }
            }

            // Clean Why Choose Us
            $whyChooseUs = [];
            if ($request->filled('why_choose_us') && is_array($request->why_choose_us)) {
                foreach ($request->why_choose_us as $item) {
                    if (!empty($item['title'])) {
                        $whyChooseUs[] = [
                            'icon'  => $item['icon'] ?? 'fa-shield-halved',
                            'title' => trim($item['title']),
                            'desc'  => trim($item['desc'] ?? ''),
                        ];
                    }
                }
            }

            // Clean Testimonials
            $testimonials = [];
            if ($request->filled('testimonials') && is_array($request->testimonials)) {
                foreach ($request->testimonials as $item) {
                    if (!empty($item['name']) && !empty($item['comment'])) {
                        $testimonials[] = [
                            'name'     => trim($item['name']),
                            'city'     => trim($item['city'] ?? 'ঢাকা'),
                            'comment'  => trim($item['comment']),
                            'rating'   => min(5, max(1, (int) ($item['rating'] ?? 5))),
                        ];
                    }
                }
            }

            // Clean FAQ
            $faq = [];
            if ($request->filled('faq') && is_array($request->faq)) {
                foreach ($request->faq as $item) {
                    if (!empty($item['question']) && !empty($item['answer'])) {
                        $faq[] = [
                            'question' => trim($item['question']),
                            'answer'   => trim($item['answer']),
                        ];
                    }
                }
            }

            $landingPage = LandingPage::create([
                'vendor_id'                 => $onlineStore?->id,
                'title'                     => $request->title,
                'slug'                      => $slug,
                'status'                    => $request->status,
                'page_theme'                => $request->page_theme ?: 'emerald',
                'hero_badge'                => $request->hero_badge,
                'hero_title'                => $request->hero_title ?: $request->title,
                'hero_subtitle'             => $request->hero_subtitle,
                'hero_image'                => $heroImagePath,
                'hero_video_url'            => $request->hero_video_url,
                'countdown_hours'           => $request->countdown_hours ?: 12,
                'features'                  => $features,
                'specifications'            => $specifications,
                'gallery_images'            => $galleryPaths,
                'why_choose_us'             => $whyChooseUs,
                'testimonials'              => $testimonials,
                'faq'                       => $faq,
                'free_shipping'             => $request->boolean('free_shipping'),
                'delivery_charge_inside'    => $request->filled('delivery_charge_inside') ? (float) $request->delivery_charge_inside : null,
                'delivery_charge_sub_dhaka' => $request->filled('delivery_charge_sub_dhaka') ? (float) $request->delivery_charge_sub_dhaka : null,
                'delivery_charge_outside'   => $request->filled('delivery_charge_outside') ? (float) $request->delivery_charge_outside : null,
                'cta_button_text'           => $request->cta_button_text ?: 'অর্ডার করতে এখানে ক্লিক করুন',
                'order_form_title'          => $request->order_form_title ?: 'অর্ডার করতে আপনার সঠিক তথ্য দিন',
                'fb_pixel_id'               => $request->fb_pixel_id,
                'tiktok_pixel_id'           => $request->tiktok_pixel_id,
                'custom_css'                => $request->custom_css,
                'created_by'                => auth()->id(),
            ]);

            // Attach multiple products
            $defaultIndex = (int) ($request->default_product_index ?? 0);
            foreach ($request->products as $idx => $pData) {
                if (empty($pData['product_id'])) continue;

                LandingPageProduct::create([
                    'landing_page_id'  => $landingPage->id,
                    'product_id'       => $pData['product_id'],
                    'package_title'    => $pData['package_title'] ?? null,
                    'package_subtitle' => $pData['package_subtitle'] ?? null,
                    'custom_price'     => isset($pData['custom_price']) && $pData['custom_price'] !== '' ? (float) $pData['custom_price'] : null,
                    'custom_old_price' => isset($pData['custom_old_price']) && $pData['custom_old_price'] !== '' ? (float) $pData['custom_old_price'] : null,
                    'is_default'       => ((int) $idx === $defaultIndex),
                    'sort_order'       => (int) $idx,
                ]);
            }
        });

        return redirect()->route('admin.landing-pages.index')
            ->with('success', 'ল্যান্ডিং পেজ সফলভাবে তৈরি হয়েছে! URL: ' . route('landing.show', $slug));
    }

    public function edit(LandingPage $landingPage)
    {
        $landingPage->load(['landingPageProducts.product.variants']);
        $onlineStore = Vendor::onlineStore();

        $products = Product::withoutGlobalScopes()
            ->where('status', 'active')
            ->select(['id', 'name', 'sku', 'price', 'old_price', 'image', 'stock_qty'])
            ->with(['variants:id,product_id,color_label,size_label,price,stock_qty'])
            ->orderBy('name')
            ->get();

        return view('admin.landing-pages.edit', compact('landingPage', 'products', 'onlineStore'));
    }

    public function update(Request $request, LandingPage $landingPage)
    {
        @ini_set('memory_limit', '512M');
        @ini_set('max_execution_time', '300');

        $request->validate([
            'title'                      => 'required|string|max:255',
            'slug'                       => 'required|string|max:255|unique:landing_pages,slug,' . $landingPage->id,
            'status'                     => 'required|in:active,inactive',
            'page_theme'                 => 'required|string|in:emerald,blue,dark,purple,rose,amber',
            'hero_badge'                 => 'nullable|string|max:255',
            'hero_title'                 => 'nullable|string|max:500',
            'hero_subtitle'              => 'nullable|string|max:1000',
            'hero_video_url'             => 'nullable|string|max:500',
            'hero_image'                 => 'nullable|image|max:15360',
            'gallery_images.*'           => 'nullable|image|max:15360',
            'countdown_hours'            => 'nullable|integer|min:1|max:720',
            'products'                   => 'required|array|min:1',
            'products.*.product_id'      => 'required|exists:products,id',
            'products.*.package_title'   => 'nullable|string|max:255',
            'products.*.package_subtitle'=> 'nullable|string|max:255',
            'products.*.custom_price'    => 'nullable|numeric|min:0',
            'products.*.custom_old_price'=> 'nullable|numeric|min:0',
            'delivery_charge_inside'     => 'nullable|numeric|min:0',
            'delivery_charge_sub_dhaka'  => 'nullable|numeric|min:0',
            'delivery_charge_outside'    => 'nullable|numeric|min:0',
            'cta_button_text'            => 'nullable|string|max:255',
            'order_form_title'           => 'nullable|string|max:255',
            'fb_pixel_id'                => 'nullable|string|max:100',
            'tiktok_pixel_id'            => 'nullable|string|max:100',
            'custom_css'                 => 'nullable|string',
        ]);

        // Process rich specifications & convert any inline base64 images to stored files
        $specifications = $this->processSpecifications($request->specifications);

        DB::transaction(function () use ($request, $landingPage, $specifications) {
            // Upload Hero Image if provided
            $heroImagePath = $landingPage->hero_image;
            if ($request->hasFile('hero_image')) {
                if ($heroImagePath && Storage::disk('uploads')->exists($heroImagePath)) {
                    Storage::disk('uploads')->delete($heroImagePath);
                }
                $heroImagePath = $request->file('hero_image')->store('landing', 'uploads');
            }

            // Existing Gallery Images
            $existingGallery = $request->input('existing_gallery', []);
            if (!is_array($existingGallery)) {
                $existingGallery = [];
            }

            // New Gallery Images
            if ($request->hasFile('gallery_images')) {
                foreach ($request->file('gallery_images') as $file) {
                    $existingGallery[] = $file->store('landing/gallery', 'uploads');
                }
            }

            // Clean Features
            $features = [];
            if ($request->filled('features') && is_array($request->features)) {
                foreach ($request->features as $item) {
                    if (!empty($item['title'])) {
                        $features[] = [
                            'icon'  => $item['icon'] ?? 'fa-check',
                            'title' => trim($item['title']),
                            'desc'  => trim($item['desc'] ?? ''),
                        ];
                    }
                }
            }

            // Clean Why Choose Us
            $whyChooseUs = [];
            if ($request->filled('why_choose_us') && is_array($request->why_choose_us)) {
                foreach ($request->why_choose_us as $item) {
                    if (!empty($item['title'])) {
                        $whyChooseUs[] = [
                            'icon'  => $item['icon'] ?? 'fa-shield-halved',
                            'title' => trim($item['title']),
                            'desc'  => trim($item['desc'] ?? ''),
                        ];
                    }
                }
            }

            // Clean Testimonials
            $testimonials = [];
            if ($request->filled('testimonials') && is_array($request->testimonials)) {
                foreach ($request->testimonials as $item) {
                    if (!empty($item['name']) && !empty($item['comment'])) {
                        $testimonials[] = [
                            'name'     => trim($item['name']),
                            'city'     => trim($item['city'] ?? 'ঢাকা'),
                            'comment'  => trim($item['comment']),
                            'rating'   => min(5, max(1, (int) ($item['rating'] ?? 5))),
                        ];
                    }
                }
            }

            // Clean FAQ
            $faq = [];
            if ($request->filled('faq') && is_array($request->faq)) {
                foreach ($request->faq as $item) {
                    if (!empty($item['question']) && !empty($item['answer'])) {
                        $faq[] = [
                            'question' => trim($item['question']),
                            'answer'   => trim($item['answer']),
                        ];
                    }
                }
            }

            $landingPage->update([
                'title'                     => $request->title,
                'slug'                      => Str::slug($request->slug),
                'status'                    => $request->status,
                'page_theme'                => $request->page_theme ?: 'emerald',
                'hero_badge'                => $request->hero_badge,
                'hero_title'                => $request->hero_title ?: $request->title,
                'hero_subtitle'             => $request->hero_subtitle,
                'hero_image'                => $heroImagePath,
                'hero_video_url'            => $request->hero_video_url,
                'countdown_hours'           => $request->countdown_hours ?: 12,
                'features'                  => $features,
                'specifications'            => $specifications,
                'gallery_images'            => array_values($existingGallery),
                'why_choose_us'             => $whyChooseUs,
                'testimonials'              => $testimonials,
                'faq'                       => $faq,
                'free_shipping'             => $request->boolean('free_shipping'),
                'delivery_charge_inside'    => $request->filled('delivery_charge_inside') ? (float) $request->delivery_charge_inside : null,
                'delivery_charge_sub_dhaka' => $request->filled('delivery_charge_sub_dhaka') ? (float) $request->delivery_charge_sub_dhaka : null,
                'delivery_charge_outside'   => $request->filled('delivery_charge_outside') ? (float) $request->delivery_charge_outside : null,
                'cta_button_text'           => $request->cta_button_text ?: 'অর্ডার করতে এখানে ক্লিক করুন',
                'order_form_title'          => $request->order_form_title ?: 'অর্ডার করতে আপনার সঠিক তথ্য দিন',
                'fb_pixel_id'               => $request->fb_pixel_id,
                'tiktok_pixel_id'           => $request->tiktok_pixel_id,
                'custom_css'                => $request->custom_css,
            ]);

            // Re-sync multiple products
            $landingPage->landingPageProducts()->delete();
            $defaultIndex = (int) ($request->default_product_index ?? 0);
            foreach ($request->products as $idx => $pData) {
                if (empty($pData['product_id'])) continue;

                LandingPageProduct::create([
                    'landing_page_id'  => $landingPage->id,
                    'product_id'       => $pData['product_id'],
                    'package_title'    => $pData['package_title'] ?? null,
                    'package_subtitle' => $pData['package_subtitle'] ?? null,
                    'custom_price'     => isset($pData['custom_price']) && $pData['custom_price'] !== '' ? (float) $pData['custom_price'] : null,
                    'custom_old_price' => isset($pData['custom_old_price']) && $pData['custom_old_price'] !== '' ? (float) $pData['custom_old_price'] : null,
                    'is_default'       => ((int) $idx === $defaultIndex),
                    'sort_order'       => (int) $idx,
                ]);
            }
        });

        return redirect()->route('admin.landing-pages.index')
            ->with('success', 'ল্যান্ডিং পেজ সফলভাবে আপডেট করা হয়েছে!');
    }

    /**
     * Upload an image from Summernote rich text editor via AJAX
     */
    public function uploadEditorImage(Request $request)
    {
        @ini_set('memory_limit', '256M');

        $request->validate([
            'image' => 'required|image|max:15360',
        ]);

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('landing/editor', 'uploads');
            $url = Storage::disk('uploads')->url($path);
            return response()->json([
                'success' => true,
                'url'     => $url,
            ]);
        }

        return response()->json(['success' => false, 'message' => 'ছবি পাওয়া যায়নি।'], 422);
    }

    /**
     * Process rich specifications: extract base64 data URIs and store them as image files
     */
    protected function processSpecifications(?string $html): ?string
    {
        if (empty($html) || !str_contains($html, 'data:image/')) {
            return $html;
        }

        @ini_set('memory_limit', '512M');
        @ini_set('max_execution_time', '300');

        $pattern = '/<img([^>]+)src=["\']data:image\/([a-zA-Z0-9\+\-]+);base64,([^"\']+)["\']([^>]*)>/i';

        return preg_replace_callback($pattern, function ($matches) {
            $prefix     = $matches[1];
            $ext        = strtolower($matches[2]);
            $base64Data = $matches[3];
            $suffix     = $matches[4];

            if ($ext === 'jpeg') {
                $ext = 'jpg';
            } elseif ($ext === 'svg+xml') {
                $ext = 'svg';
            } elseif (!in_array($ext, ['jpg', 'png', 'webp', 'gif'])) {
                $ext = 'png';
            }

            $decoded = base64_decode(str_replace(' ', '+', $base64Data));
            if ($decoded === false) {
                return $matches[0];
            }

            $filename = 'editor_' . Str::random(24) . '.' . $ext;
            $diskPath = 'landing/editor/' . $filename;

            Storage::disk('uploads')->put($diskPath, $decoded);
            $url = Storage::disk('uploads')->url($diskPath);

            return "<img{$prefix}src=\"{$url}\"{$suffix}>";
        }, $html);
    }

    public function destroy(LandingPage $landingPage)
    {
        if ($landingPage->hero_image && Storage::disk('uploads')->exists($landingPage->hero_image)) {
            Storage::disk('uploads')->delete($landingPage->hero_image);
        }

        if (is_array($landingPage->gallery_images)) {
            foreach ($landingPage->gallery_images as $img) {
                if ($img && Storage::disk('uploads')->exists($img)) {
                    Storage::disk('uploads')->delete($img);
                }
            }
        }

        $landingPage->delete();

        return redirect()->route('admin.landing-pages.index')
            ->with('success', 'ল্যান্ডিং পেজ মুছে ফেলা হয়েছে।');
    }

    public function toggle(LandingPage $landingPage)
    {
        $newStatus = $landingPage->status === 'active' ? 'inactive' : 'active';
        $landingPage->update(['status' => $newStatus]);

        return back()->with('success', 'ল্যান্ডিং পেজ স্ট্যাটাস ' . ($newStatus === 'active' ? 'একটিভ' : 'নিষ্ক্রিয়') . ' করা হয়েছে।');
    }
}

