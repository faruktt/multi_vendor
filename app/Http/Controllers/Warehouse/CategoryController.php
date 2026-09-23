<?php

namespace App\Http\Controllers\Warehouse;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Vendor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CategoryController extends Controller
{
    /**
     * Categories have no branch system anymore — every vendor still has its own physical row
     * (products still need a same-vendor category_id), but they're all named-matched and kept
     * in sync from here, the single place they're managed. This groups every row by name so
     * one edit/delete here applies everywhere it's used, and nothing that predates this page
     * (categories entered back when each branch managed its own) gets left out.
     */
    public function index(Vendor $branch)
    {
        $onlineStoreId = Vendor::where('is_online_store', true)->value('id');

        $rows = Category::withoutGlobalScopes()
            ->withCount('products')
            ->with('vendor')
            ->get();

        $categories = $rows->groupBy('name')->map(function ($group) use ($branch, $onlineStoreId) {
            $representative = $group->firstWhere('vendor_id', $branch->id) ?? $group->first();
            $websiteRow     = $onlineStoreId ? $group->firstWhere('vendor_id', $onlineStoreId) : null;

            return (object) [
                'id'                 => $representative->id,
                'name'               => $representative->name,
                'parent_id'          => $representative->parent_id,
                'image_url'          => $representative->image ? Storage::disk('uploads')->url($representative->image) : null,
                'products_count'     => $group->sum('products_count'),
                'branch_names'       => $group->pluck('vendor.name')->filter()->unique()->values(),
                'website_category_id'=> $websiteRow?->id,
                'show_on_homepage'   => $websiteRow?->show_on_homepage,
            ];
        })->values()->sortBy('name')->values();

        return view('warehouse.categories.index', compact('branch', 'categories'));
    }

    public function store(Request $request, Vendor $branch)
    {
        $request->validate([
            'name'  => 'required|string|max:255',
            'image' => 'nullable|image|max:2048',
        ]);

        $exists = Category::withoutGlobalScopes()->where('name', $request->name)->exists();
        abort_if($exists, 422, 'A category with this name already exists.');

        $imagePath = $request->hasFile('image')
            ? $request->file('image')->store('categories', 'uploads')
            : null;

        $branch->categories()->create(['name' => $request->name, 'image' => $imagePath]);

        $onlineStore = Vendor::where('is_online_store', true)->first();
        if ($onlineStore && $onlineStore->id !== $branch->id) {
            $onlineStore->categories()->firstOrCreate(['name' => $request->name], ['image' => $imagePath]);
        }

        return back()->with('success', 'Category added successfully.');
    }

    /** Renames/re-images every vendor's row sharing the old name — keeps the whole system in sync. */
    public function update(Request $request, Vendor $branch, Category $category)
    {
        $request->validate([
            'name'  => 'required|string|max:255',
            'image' => 'nullable|image|max:2048',
        ]);

        $siblings = Category::withoutGlobalScopes()->where('name', $category->name)->get();

        $imagePath = $category->image;
        if ($request->hasFile('image')) {
            foreach ($siblings as $sibling) {
                if ($sibling->image) Storage::disk('uploads')->delete($sibling->image);
            }
            $imagePath = $request->file('image')->store('categories', 'uploads');
        }

        foreach ($siblings as $sibling) {
            $sibling->update(['name' => $request->name, 'image' => $imagePath]);
        }

        return back()->with('success', 'Category updated everywhere it\'s used.');
    }

    /** Deletes every vendor's row sharing this name — the category disappears system-wide. */
    public function destroy(Vendor $branch, Category $category)
    {
        $siblings = Category::withoutGlobalScopes()->where('name', $category->name)->get();

        foreach ($siblings as $sibling) {
            if ($sibling->image) Storage::disk('uploads')->delete($sibling->image);
            $sibling->delete();
        }

        return back()->with('success', 'Category removed everywhere it was used.');
    }

    /** Only meaningful on the Website's own row — that's the one the storefront actually reads. */
    public function toggleHomepage(Vendor $branch, Category $category)
    {
        $website = Vendor::where('is_online_store', true)->firstOrFail();
        abort_unless($category->vendor_id === $website->id, 404);

        $category->update(['show_on_homepage' => !$category->show_on_homepage]);

        return back()->with('success', 'Homepage visibility updated.');
    }

    public function reorderHomepage(Request $request, Vendor $branch, Category $category)
    {
        $website = Vendor::where('is_online_store', true)->firstOrFail();
        abort_unless($category->vendor_id === $website->id, 404);

        $direction = $request->input('direction') === 'up' ? 'up' : 'down';

        $siblings = Category::withoutGlobalScopes()
            ->where('vendor_id', $website->id)
            ->whereNull('parent_id')
            ->orderBy('homepage_sort_order')
            ->orderBy('name')
            ->get()
            ->values();

        $siblings->each(fn($c, $i) => $c->homepage_sort_order === $i ?: $c->update(['homepage_sort_order' => $i]));

        $index = $siblings->search(fn($c) => $c->id === $category->id);
        $swapIndex = $direction === 'up' ? $index - 1 : $index + 1;

        if ($swapIndex >= 0 && $swapIndex < $siblings->count()) {
            $swapWith = $siblings->get($swapIndex);
            $category->update(['homepage_sort_order' => $swapIndex]);
            $swapWith->update(['homepage_sort_order' => $index]);
        }

        return back();
    }
}
