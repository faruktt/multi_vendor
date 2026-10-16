<?php

namespace App\Http\Controllers\Warehouse;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Vendor;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Storage;

class CategoryController extends Controller
{
    /**
     * Main Categories Management (Categories where parent_id is NULL)
     * Every vendor still has its own physical row for category_id relationships,
     * but they are kept in sync system-wide from here.
     */
    public function index(Request $request, Vendor $branch)
    {
        $onlineStoreId = Vendor::where('is_online_store', true)->value('id');

        $rows = Category::withoutGlobalScopes()
            ->withCount('products')
            ->with(['vendor', 'parent'])
            ->get();

        // Main Categories (parent_id is null)
        $parentRows = $rows->whereNull('parent_id');

        $categories = $parentRows->groupBy('name')->map(function ($group) use ($branch, $onlineStoreId, $rows) {
            $representative = $group->firstWhere('vendor_id', $branch->id) ?? $group->first();
            $websiteRow     = $onlineStoreId ? $group->firstWhere('vendor_id', $onlineStoreId) : null;

            // Count subcategories under this main category
            $subCount = $rows->filter(function ($r) use ($representative) {
                return $r->parent_id && ($r->parent_id === $representative->id || ($r->parent && $r->parent->name === $representative->name));
            })->groupBy('name')->count();

            return (object) [
                'id'                  => $representative->id,
                'name'                => $representative->name,
                'parent_id'           => null,
                'parent_name'         => null,
                'is_subcategory'      => false,
                'subcategories_count' => $subCount,
                'image_url'           => $representative->image ? Storage::disk('uploads')->url($representative->image) : null,
                'products_count'      => $group->sum('products_count'),
                'branch_names'        => $group->pluck('vendor.name')->filter()->unique()->values(),
                'website_category_id' => $websiteRow?->id,
                'show_on_homepage'    => $websiteRow?->show_on_homepage,
                'status'              => $representative->status ?? 'active',
            ];
        })->values();

        if ($request->filled('search')) {
            $s = mb_strtolower(trim($request->search));
            $categories = $categories->filter(fn($c) => str_contains(mb_strtolower($c->name), $s))->values();
        }

        $categories = $categories->sortBy('name')->values();

        // Parent categories for dropdown (just in case)
        $parentCategories = $categories;

        return view('warehouse.categories.index', compact('branch', 'categories', 'parentCategories'));
    }

    public function store(Request $request, Vendor $branch)
    {
        $request->validate([
            'name'      => 'required|string|max:255',
            'parent_id' => 'nullable|integer',
            'image'     => 'nullable|image|max:10240',
            'status'    => 'nullable|string|in:active,inactive',
        ]);

        $parentId = $request->filled('parent_id') ? (int)$request->parent_id : null;
        $parentCat = $parentId ? Category::withoutGlobalScopes()->find($parentId) : null;

        // Check duplicate name within same parent level
        $existsQuery = Category::withoutGlobalScopes()->where('name', $request->name);
        if ($parentCat) {
            $parentNames = [$parentCat->name];
            $parentIds = Category::withoutGlobalScopes()->whereIn('name', $parentNames)->pluck('id');
            $exists = $existsQuery->whereIn('parent_id', $parentIds)->exists();
        } else {
            $exists = $existsQuery->whereNull('parent_id')->exists();
        }
        abort_if($exists, 422, 'A category or subcategory with this name already exists.');

        $imagePath = $request->hasFile('image')
            ? $request->file('image')->store('categories', 'uploads')
            : null;

        $status = $request->input('status', 'active');

        // Resolve parent_id for branch
        $branchParentId = null;
        if ($parentCat) {
            $branchParent = Category::withoutGlobalScopes()
                ->where('vendor_id', $branch->id)
                ->where('name', $parentCat->name)
                ->first();
            $branchParentId = $branchParent?->id ?? $parentCat->id;
        }

        $branch->categories()->create([
            'name'      => $request->name,
            'parent_id' => $branchParentId,
            'image'     => $imagePath,
            'status'    => $status,
        ]);

        $otherVendors = Vendor::where('id', '!=', $branch->id)->where('status', 'active')->get();
        foreach ($otherVendors as $otherVendor) {
            $otherParentId = null;
            if ($parentCat) {
                $otherParent = Category::withoutGlobalScopes()
                    ->where('vendor_id', $otherVendor->id)
                    ->where('name', $parentCat->name)
                    ->first();
                $otherParentId = $otherParent?->id ?? $branchParentId;
            }

            $otherVendor->categories()->firstOrCreate(
                ['name' => $request->name, 'parent_id' => $otherParentId],
                ['image' => $imagePath, 'status' => $status]
            );
        }

        $msg = $parentCat ? 'Subcategory added successfully under ' . $parentCat->name . '.' : 'Category added successfully.';
        return back()->with('success', $msg);
    }

    /** Renames/re-images every vendor's row sharing the old name — keeps the whole system in sync. */
    public function update(Request $request, Vendor $branch, Category $category)
    {
        $request->validate([
            'name'      => 'required|string|max:255',
            'parent_id' => 'nullable|integer',
            'image'     => 'nullable|image|max:10240',
            'status'    => 'nullable|string|in:active,inactive',
        ]);

        $siblings = Category::withoutGlobalScopes()->where('name', $category->name)->get();

        $imagePath = $category->image;
        if ($request->hasFile('image')) {
            foreach ($siblings as $sibling) {
                if ($sibling->image) Storage::disk('uploads')->delete($sibling->image);
            }
            $imagePath = $request->file('image')->store('categories', 'uploads');
        }

        $parentId = $request->filled('parent_id') ? (int)$request->parent_id : null;
        $parentCat = $parentId ? Category::withoutGlobalScopes()->find($parentId) : null;
        $status = $request->input('status', $category->status ?? 'active');

        foreach ($siblings as $sibling) {
            $siblingParentId = null;
            if ($parentCat) {
                $sParent = Category::withoutGlobalScopes()
                    ->where('vendor_id', $sibling->vendor_id)
                    ->where('name', $parentCat->name)
                    ->first();
                $siblingParentId = $sParent?->id ?? $parentId;
            }

            $sibling->update([
                'name'      => $request->name,
                'parent_id' => $siblingParentId,
                'image'     => $imagePath,
                'status'    => $status,
            ]);
        }

        return back()->with('success', 'Category updated everywhere it\'s used.');
    }

    /** Deletes every vendor's row sharing this name and cascades to its subcategories. */
    public function destroy(Vendor $branch, Category $category)
    {
        $siblings = Category::withoutGlobalScopes()->where('name', $category->name)->get();

        // Also delete subcategories if this was a parent category
        $childCategories = Category::withoutGlobalScopes()->whereIn('parent_id', $siblings->pluck('id'))->get();
        foreach ($childCategories as $child) {
            if ($child->image) Storage::disk('uploads')->delete($child->image);
            $child->delete();
        }

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

    // ─────────────────────────────────────────────────────────────────────────
    // ══ SUBCATEGORIES MANAGEMENT ══
    // ─────────────────────────────────────────────────────────────────────────

    public function subcategoriesIndex(Request $request, Vendor $branch)
    {
        $onlineStoreId = Vendor::where('is_online_store', true)->value('id');

        // All distinct parent categories for modal dropdown & filter
        $parentCategories = Category::withoutGlobalScopes()
            ->whereNull('parent_id')
            ->get()
            ->groupBy('name')
            ->map(function ($group) use ($branch) {
                return $group->firstWhere('vendor_id', $branch->id) ?? $group->first();
            })
            ->sortBy('name')
            ->values();

        // Query only subcategories (where parent_id is not null)
        $rows = Category::withoutGlobalScopes()
            ->whereNotNull('parent_id')
            ->with(['vendor', 'parent'])
            ->withCount('products')
            ->get();

        // Group by parent name and subcategory name across branches
        $subcategories = $rows->groupBy(function ($r) {
            $pName = $r->parent ? $r->parent->name : 'Unknown';
            return $pName . '___' . $r->name;
        })->map(function ($group) use ($branch, $onlineStoreId) {
            $representative = $group->firstWhere('vendor_id', $branch->id) ?? $group->first();
            $parent = $representative->parent;
            if (!$parent && $representative->parent_id) {
                $parent = Category::withoutGlobalScopes()->find($representative->parent_id);
            }

            return (object) [
                'id'                  => $representative->id,
                'name'                => $representative->name,
                'parent_id'           => $representative->parent_id,
                'parent_name'         => $parent?->name ?? '—',
                'parent_category_id'  => $parent?->id,
                'status'              => $representative->status ?? 'active',
                'is_active'           => ($representative->status ?? 'active') === 'active',
                'image_url'           => $representative->image ? Storage::disk('uploads')->url($representative->image) : null,
                'products_count'      => $group->sum('products_count'),
                'created_at'          => $representative->created_at,
            ];
        })->values();

        // Filter by search
        if ($request->filled('search')) {
            $s = mb_strtolower(trim($request->search));
            $subcategories = $subcategories->filter(function ($item) use ($s) {
                return str_contains(mb_strtolower($item->name), $s) || str_contains(mb_strtolower($item->parent_name), $s);
            })->values();
        }

        // Filter by parent category
        if ($request->filled('category')) {
            $catFilter = trim($request->category);
            $subcategories = $subcategories->filter(function ($item) use ($catFilter) {
                return (string)$item->parent_id === $catFilter 
                    || (string)$item->parent_category_id === $catFilter 
                    || strcasecmp($item->parent_name, $catFilter) === 0;
            })->values();
        }

        // Sort by parent name then subcategory name
        $subcategories = $subcategories->sortBy(function ($item) {
            return $item->parent_name . ' - ' . $item->name;
        })->values();

        // Paginate
        $perPage = 15;
        $currentPage = Paginator::resolveCurrentPage('page') ?: 1;
        $pagedData = $subcategories->slice(($currentPage - 1) * $perPage, $perPage)->values();
        $paginatedSubcategories = new LengthAwarePaginator(
            $pagedData,
            $subcategories->count(),
            $perPage,
            $currentPage,
            ['path' => Paginator::resolveCurrentPath(), 'query' => $request->query()]
        );

        return view('warehouse.subcategories.index', compact('branch', 'paginatedSubcategories', 'parentCategories'));
    }

    public function subcategoriesStore(Request $request, Vendor $branch)
    {
        $request->validate([
            'parent_id' => 'required|integer',
            'name'      => 'required|string|max:255',
            'image'     => 'nullable|image|max:10240',
            'status'    => 'nullable|string|in:active,inactive',
        ]);

        $parentCat = Category::withoutGlobalScopes()->findOrFail($request->parent_id);

        // Check duplicate name within this parent category
        $parentNames = [$parentCat->name];
        $parentIds = Category::withoutGlobalScopes()->whereIn('name', $parentNames)->pluck('id');
        $exists = Category::withoutGlobalScopes()
            ->where('name', $request->name)
            ->whereIn('parent_id', $parentIds)
            ->exists();

        if ($exists) {
            return back()->withInput()->with('error', 'A subcategory with this name already exists under "' . $parentCat->name . '".');
        }

        $imagePath = $request->hasFile('image')
            ? $request->file('image')->store('categories', 'uploads')
            : null;

        $status = $request->input('status', 'active');

        // Resolve parent_id for branch
        $branchParent = Category::withoutGlobalScopes()
            ->where('vendor_id', $branch->id)
            ->where('name', $parentCat->name)
            ->first();
        $branchParentId = $branchParent?->id ?? $parentCat->id;

        $branch->categories()->create([
            'name'      => $request->name,
            'parent_id' => $branchParentId,
            'image'     => $imagePath,
            'status'    => $status,
        ]);

        $otherVendors = Vendor::where('id', '!=', $branch->id)->where('status', 'active')->get();
        foreach ($otherVendors as $otherVendor) {
            $otherParent = Category::withoutGlobalScopes()
                ->where('vendor_id', $otherVendor->id)
                ->where('name', $parentCat->name)
                ->first();
            $otherParentId = $otherParent?->id ?? $branchParentId;

            $otherVendor->categories()->firstOrCreate(
                ['name' => $request->name, 'parent_id' => $otherParentId],
                ['image' => $imagePath, 'status' => $status]
            );
        }

        return back()->with('success', 'Subcategory "' . $request->name . '" created successfully under "' . $parentCat->name . '".');
    }

    public function subcategoriesUpdate(Request $request, Vendor $branch, Category $category)
    {
        $request->validate([
            'parent_id' => 'required|integer',
            'name'      => 'required|string|max:255',
            'image'     => 'nullable|image|max:10240',
            'status'    => 'nullable|string|in:active,inactive',
        ]);

        $parentCat = Category::withoutGlobalScopes()->findOrFail($request->parent_id);

        $siblings = Category::withoutGlobalScopes()->where('name', $category->name)->get();

        $imagePath = $category->image;
        if ($request->hasFile('image')) {
            foreach ($siblings as $sibling) {
                if ($sibling->image) Storage::disk('uploads')->delete($sibling->image);
            }
            $imagePath = $request->file('image')->store('categories', 'uploads');
        }

        $status = $request->input('status', $category->status ?? 'active');

        foreach ($siblings as $sibling) {
            $sParent = Category::withoutGlobalScopes()
                ->where('vendor_id', $sibling->vendor_id)
                ->where('name', $parentCat->name)
                ->first();
            $siblingParentId = $sParent?->id ?? $parentCat->id;

            $sibling->update([
                'name'      => $request->name,
                'parent_id' => $siblingParentId,
                'image'     => $imagePath,
                'status'    => $status,
            ]);
        }

        return back()->with('success', 'Subcategory "' . $request->name . '" updated successfully.');
    }

    public function subcategoriesToggleStatus(Vendor $branch, Category $category)
    {
        $newStatus = ($category->status ?? 'active') === 'active' ? 'inactive' : 'active';
        $siblings = Category::withoutGlobalScopes()->where('name', $category->name)->get();
        foreach ($siblings as $sibling) {
            $sibling->update(['status' => $newStatus]);
        }

        return back()->with('success', 'Subcategory status changed to ' . ucfirst($newStatus) . '.');
    }

    public function subcategoriesDestroy(Vendor $branch, Category $category)
    {
        $siblings = Category::withoutGlobalScopes()->where('name', $category->name)->get();
        foreach ($siblings as $sibling) {
            if ($sibling->image) Storage::disk('uploads')->delete($sibling->image);
            $sibling->delete();
        }

        return back()->with('success', 'Subcategory deleted successfully.');
    }
}
