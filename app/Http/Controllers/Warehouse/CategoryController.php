<?php

namespace App\Http\Controllers\Warehouse;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\SubCategory;
use App\Models\Vendor;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    /**
     * Main Categories Management (Global, no branch duplication)
     */
    public function index(Request $request, ?Vendor $branch = null)
    {
        $branch = $branch ?? Vendor::warehouse();

        $query = Category::withCount(['products', 'subCategories']);

        if ($request->filled('search')) {
            $s = trim($request->search);
            $query->where('name', 'like', "%{$s}%");
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $categories = $query->orderBy('sort_order')->orderBy('name')->get();
        $parentCategories = $categories;

        return view('warehouse.categories.index', compact('branch', 'categories', 'parentCategories'));
    }

    public function store(Request $request, ?Vendor $branch = null)
    {
        $request->validate([
            'name'       => 'required|string|max:255|unique:categories,name',
            'image'      => 'nullable|image|max:10240',
            'status'     => 'nullable|string|in:active,inactive',
            'sort_order' => 'nullable|integer',
        ]);

        $imagePath = $request->hasFile('image')
            ? $request->file('image')->store('categories', 'uploads')
            : null;

        $status = $request->input('status', 'active');
        $maxOrder = Category::max('sort_order') ?? 0;
        $sortOrder = $request->filled('sort_order') ? (int) $request->input('sort_order') : ($maxOrder + 1);

        Category::create([
            'name'                => trim($request->name),
            'image'               => $imagePath,
            'status'              => $status,
            'sort_order'          => $sortOrder,
            'homepage_sort_order' => $sortOrder,
            'show_on_homepage'    => true,
        ]);

        return back()->with('success', 'Category added successfully.');
    }

    public function update(Request $request, $branch, $category = null)
    {
        $category = $this->resolveCategory($branch, $category);

        $request->validate([
            'name'       => 'required|string|max:255|unique:categories,name,' . $category->id,
            'image'      => 'nullable|image|max:10240',
            'status'     => 'nullable|string|in:active,inactive',
            'sort_order' => 'nullable|integer',
        ]);

        $imagePath = $category->image;
        if ($request->hasFile('image')) {
            if ($imagePath) Storage::disk('uploads')->delete($imagePath);
            $imagePath = $request->file('image')->store('categories', 'uploads');
        }

        $status = $request->input('status', $category->status ?? 'active');
        $sortOrder = $request->filled('sort_order') ? (int) $request->input('sort_order') : ($category->sort_order ?? 0);

        $category->update([
            'name'                => trim($request->name),
            'image'               => $imagePath,
            'status'              => $status,
            'sort_order'          => $sortOrder,
            'homepage_sort_order' => $sortOrder,
        ]);

        return back()->with('success', 'Category updated successfully.');
    }

    public function destroy($branch, $category = null)
    {
        $category = $this->resolveCategory($branch, $category);

        if ($category->image) {
            Storage::disk('uploads')->delete($category->image);
        }

        // Subcategories delete cascade automatically via foreign key or model
        foreach ($category->subCategories as $sub) {
            if ($sub->image) Storage::disk('uploads')->delete($sub->image);
            $sub->delete();
        }

        $category->delete();

        return back()->with('success', 'Category and its subcategories removed successfully.');
    }

    public function toggleStatus($branch, $category = null)
    {
        $category = $this->resolveCategory($branch, $category);

        $newStatus = ($category->status === 'inactive') ? 'active' : 'inactive';
        $category->update(['status' => $newStatus]);

        return back()->with('success', "Category '{$category->name}' status changed to " . ucfirst($newStatus) . ".");
    }

    public function reorder(Request $request, $branch, $category = null)
    {
        $category = $this->resolveCategory($branch, $category);
        $direction = $request->input('direction') === 'up' ? 'up' : 'down';

        $categories = Category::orderBy('sort_order')->orderBy('name')->get()->values();

        // Sequential re-indexing
        $categories->each(fn($c, $i) => $c->sort_order === $i ?: $c->update(['sort_order' => $i, 'homepage_sort_order' => $i]));

        $index = $categories->search(fn($c) => $c->id === $category->id);
        $swapIndex = ($direction === 'up') ? $index - 1 : $index + 1;

        if ($swapIndex >= 0 && $swapIndex < $categories->count()) {
            $swapWith = $categories->get($swapIndex);
            $category->update(['sort_order' => $swapIndex, 'homepage_sort_order' => $swapIndex]);
            $swapWith->update(['sort_order' => $index, 'homepage_sort_order' => $index]);
        }

        return back()->with('success', 'Category order updated.');
    }

    public function updateOrderDirect(Request $request, $branch, $category = null)
    {
        $category = $this->resolveCategory($branch, $category);

        $request->validate(['sort_order' => 'required|integer|min:0']);
        $newOrder = (int) $request->input('sort_order');

        $category->update([
            'sort_order'          => $newOrder,
            'homepage_sort_order' => $newOrder,
        ]);

        return back()->with('success', "Sort order for '{$category->name}' set to {$newOrder}.");
    }

    public function toggleHomepage($branch, $category = null)
    {
        $category = $this->resolveCategory($branch, $category);
        $category->update(['show_on_homepage' => !$category->show_on_homepage]);

        return back()->with('success', 'Homepage visibility updated.');
    }

    public function reorderHomepage(Request $request, $branch, $category = null)
    {
        return $this->reorder($request, $branch, $category);
    }

    // Central action wrappers (when called directly without {branch} in route)
    public function storeCentral(Request $request) { return $this->store($request, Vendor::warehouse()); }
    public function updateCentral(Request $request, $category) { return $this->update($request, Vendor::warehouse(), $category); }
    public function destroyCentral($category) { return $this->destroy(Vendor::warehouse(), $category); }
    public function toggleStatusCentral($category) { return $this->toggleStatus(Vendor::warehouse(), $category); }
    public function reorderCentral(Request $request, $category) { return $this->reorder($request, Vendor::warehouse(), $category); }
    public function updateOrderDirectCentral(Request $request, $category) { return $this->updateOrderDirect($request, Vendor::warehouse(), $category); }

    private function resolveCategory($branch, $category): Category
    {
        if ($branch instanceof Category) {
            return $branch;
        }
        if ($category instanceof Category) {
            return $category;
        }
        return Category::findOrFail($category ?: $branch);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // ══ SUBCATEGORIES MANAGEMENT (sub_categories table) ══
    // ─────────────────────────────────────────────────────────────────────────

    public function subcategoriesIndex(Request $request, ?Vendor $branch = null)
    {
        $branch = $branch ?? Vendor::warehouse();

        $parentCategories = Category::orderBy('name')->get();

        $query = SubCategory::with('category')->withCount('products');

        if ($request->filled('search')) {
            $s = trim($request->search);
            $query->where(function ($q) use ($s) {
                $q->where('name', 'like', "%{$s}%")
                  ->orWhereHas('category', fn($cq) => $cq->where('name', 'like', "%{$s}%"));
            });
        }

        if ($request->filled('category') || $request->filled('category_id') || $request->filled('parent_id')) {
            $catFilter = trim($request->category ?: ($request->category_id ?: $request->parent_id));
            $query->where(function ($q) use ($catFilter) {
                if (is_numeric($catFilter)) {
                    $q->where('category_id', $catFilter);
                } else {
                    $q->whereHas('category', fn($cq) => $cq->where('name', $catFilter));
                }
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $paginatedSubcategories = $query->orderBy('sort_order')->orderBy('name')->paginate(15)->withQueryString();

        return view('warehouse.subcategories.index', compact('branch', 'paginatedSubcategories', 'parentCategories'));
    }

    public function subcategoriesStore(Request $request, ?Vendor $branch = null)
    {
        $catId = $request->input('category_id') ?: $request->input('parent_id');

        $request->validate([
            'name'        => 'required|string|max:255',
            'image'       => 'nullable|image|max:10240',
            'status'      => 'nullable|string|in:active,inactive',
            'sort_order'  => 'nullable|integer',
        ]);

        $category = Category::findOrFail($catId);

        $exists = SubCategory::where('category_id', $category->id)
            ->where('name', trim($request->name))
            ->exists();

        if ($exists) {
            return back()->withInput()->with('error', 'A subcategory with this name already exists under "' . $category->name . '".');
        }

        $imagePath = $request->hasFile('image')
            ? $request->file('image')->store('categories', 'uploads')
            : null;

        $maxOrder = SubCategory::where('category_id', $category->id)->max('sort_order') ?? 0;
        $sortOrder = $request->filled('sort_order') ? (int) $request->input('sort_order') : ($maxOrder + 1);

        SubCategory::create([
            'category_id' => $category->id,
            'name'        => trim($request->name),
            'image'       => $imagePath,
            'status'      => $request->input('status', 'active'),
            'sort_order'  => $sortOrder,
        ]);

        return back()->with('success', 'Subcategory "' . $request->name . '" created successfully under "' . $category->name . '".');
    }

    public function subcategoriesUpdate(Request $request, $branch, $subCategory = null)
    {
        $sub = $this->resolveSubCategory($branch, $subCategory);
        $catId = $request->input('category_id') ?: $request->input('parent_id') ?: $sub->category_id;

        $request->validate([
            'name'       => 'required|string|max:255',
            'image'      => 'nullable|image|max:10240',
            'status'     => 'nullable|string|in:active,inactive',
            'sort_order' => 'nullable|integer',
        ]);

        $imagePath = $sub->image;
        if ($request->hasFile('image')) {
            if ($imagePath) Storage::disk('uploads')->delete($imagePath);
            $imagePath = $request->file('image')->store('categories', 'uploads');
        }

        $sub->update([
            'category_id' => $catId,
            'name'        => trim($request->name),
            'image'       => $imagePath,
            'status'      => $request->input('status', $sub->status ?? 'active'),
            'sort_order'  => $request->filled('sort_order') ? (int) $request->input('sort_order') : ($sub->sort_order ?? 0),
        ]);

        return back()->with('success', 'Subcategory "' . $sub->name . '" updated successfully.');
    }

    public function subcategoriesToggleStatus($branch, $subCategory = null)
    {
        $sub = $this->resolveSubCategory($branch, $subCategory);
        $newStatus = ($sub->status === 'inactive') ? 'active' : 'inactive';
        $sub->update(['status' => $newStatus]);

        return back()->with('success', 'Subcategory status changed to ' . ucfirst($newStatus) . '.');
    }

    public function subcategoriesDestroy($branch, $subCategory = null)
    {
        $sub = $this->resolveSubCategory($branch, $subCategory);
        if ($sub->image) {
            Storage::disk('uploads')->delete($sub->image);
        }
        $sub->delete();

        return back()->with('success', 'Subcategory deleted successfully.');
    }

    private function resolveSubCategory($branch, $subCategory): SubCategory
    {
        if ($branch instanceof SubCategory) {
            return $branch;
        }
        if ($subCategory instanceof SubCategory) {
            return $subCategory;
        }
        return SubCategory::findOrFail($subCategory ?: $branch);
    }
}
