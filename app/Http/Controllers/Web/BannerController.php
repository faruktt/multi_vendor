<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class BannerController extends Controller
{
    public function index()
    {
        $banners = Banner::orderBy('sort_order')->get()->map(function ($banner) {
            $banner->image_url = Storage::disk('uploads')->url($banner->image);
            return $banner;
        });

        $heroBanners   = $banners->where('position', 'hero')->values();
        $promo1Banners = $banners->where('position', 'promo1')->values();
        $promo2Banners = $banners->where('position', 'promo2')->values();
        $midBanners    = $banners->where('position', 'mid')->values();

        return view('admin.banners', compact('heroBanners', 'promo1Banners', 'promo2Banners', 'midBanners'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'image'    => 'required|image|max:4096',
            'title'    => 'nullable|string|max:255',
            'link'     => 'nullable|string|max:2048',
            'position' => 'required|in:hero,promo1,promo2,mid',
        ]);

        $path = $request->file('image')->store('banners', 'uploads');
        $nextOrder = (int) Banner::where('position', $request->position)->max('sort_order') + 1;

        Banner::create([
            'image'      => $path,
            'title'      => $request->title,
            'link'       => $request->link,
            'position'   => $request->position,
            'sort_order' => $nextOrder,
            'is_active'  => true,
        ]);

        return back()->with('success', 'Banner uploaded.');
    }

    public function update(Request $request, Banner $banner)
    {
        $request->validate([
            'title' => 'nullable|string|max:255',
            'link'  => 'nullable|string|max:2048',
        ]);

        $banner->update($request->only('title', 'link'));

        return back()->with('success', 'Banner updated.');
    }

    public function toggle(Banner $banner)
    {
        $banner->update(['is_active' => !$banner->is_active]);

        return back()->with('success', 'Banner updated.');
    }

    public function reorder(Request $request)
    {
        $request->validate(['order' => 'required|array']);

        foreach ($request->order as $index => $id) {
            Banner::where('id', $id)->update(['sort_order' => $index]);
        }

        return back()->with('success', 'Banner order updated.');
    }

    public function destroy(Banner $banner)
    {
        Storage::disk('uploads')->delete($banner->image);
        $banner->delete();

        return back()->with('success', 'Banner deleted.');
    }
}
