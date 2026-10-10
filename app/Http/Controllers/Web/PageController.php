<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Page;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PageController extends Controller
{
    public function index(Request $request)
    {
        $query = Page::with('createdBy')->orderBy('sort_order')->orderBy('id', 'desc');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('slug', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $query->where('is_active', true);
            } elseif ($request->status === 'inactive') {
                $query->where('is_active', false);
            }
        }

        $pages = $query->paginate(15)->withQueryString();

        $all = Page::all();
        $stats = [
            'total'    => $all->count(),
            'active'   => $all->where('is_active', true)->count(),
            'inactive' => $all->where('is_active', false)->count(),
        ];

        return view('admin.pages.index', compact('pages', 'stats'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'title'       => 'required|string|max:255',
            'slug'        => 'nullable|string|max:255|unique:pages,slug',
            'description' => 'required|string',
            'sort_order'  => 'nullable|integer|min:0',
            'is_active'   => 'nullable|boolean',
        ]);

        $slug = $request->filled('slug')
            ? Str::slug($request->slug)
            : Str::slug($request->title);

        // Ensure unique slug
        $baseSlug = $slug ?: 'page';
        $uniqueSlug = $baseSlug;
        $counter = 1;
        while (Page::where('slug', $uniqueSlug)->exists()) {
            $uniqueSlug = $baseSlug . '-' . $counter;
            $counter++;
        }

        Page::create([
            'title'       => $request->title,
            'slug'        => $uniqueSlug,
            'description' => $request->description,
            'sort_order'  => (int) ($request->sort_order ?? 0),
            'is_active'   => $request->has('is_active') ? $request->boolean('is_active') : true,
            'created_by'  => auth()->id(),
        ]);

        return redirect()->route('admin.pages.index')->with('success', 'Page created successfully.');
    }

    public function update(Request $request, Page $page)
    {
        $request->validate([
            'title'       => 'required|string|max:255',
            'slug'        => 'nullable|string|max:255|unique:pages,slug,' . $page->id,
            'description' => 'required|string',
            'sort_order'  => 'nullable|integer|min:0',
            'is_active'   => 'nullable|boolean',
        ]);

        $slug = $request->filled('slug')
            ? Str::slug($request->slug)
            : ($page->slug ?: Str::slug($request->title));

        // Ensure unique slug if changed
        if ($slug !== $page->slug) {
            $baseSlug = $slug ?: 'page';
            $uniqueSlug = $baseSlug;
            $counter = 1;
            while (Page::where('slug', $uniqueSlug)->where('id', '!=', $page->id)->exists()) {
                $uniqueSlug = $baseSlug . '-' . $counter;
                $counter++;
            }
            $slug = $uniqueSlug;
        }

        $page->update([
            'title'       => $request->title,
            'slug'        => $slug,
            'description' => $request->description,
            'sort_order'  => (int) ($request->sort_order ?? $page->sort_order),
            'is_active'   => $request->has('is_active') ? $request->boolean('is_active') : $page->is_active,
        ]);

        return redirect()->route('admin.pages.index')->with('success', 'Page updated successfully.');
    }

    public function toggle(Request $request, Page $page)
    {
        $page->update(['is_active' => !$page->is_active]);

        $statusText = $page->is_active ? 'activated' : 'deactivated';

        if ($request->wantsJson()) {
            return response()->json([
                'success'   => true,
                'is_active' => $page->is_active,
                'message'   => "Page {$statusText} successfully.",
            ]);
        }

        return redirect()->back()->with('success', "Page {$statusText} successfully.");
    }

    public function destroy(Page $page)
    {
        $page->delete();

        return redirect()->route('admin.pages.index')->with('success', 'Page deleted successfully.');
    }
}
