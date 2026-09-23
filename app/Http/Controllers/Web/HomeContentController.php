<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\HomeContent;
use Illuminate\Http\Request;

class HomeContentController extends Controller
{
    public function index(Request $request)
    {
        $query = HomeContent::with('createdBy')->orderBy('sort_order')->orderBy('id');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('content', 'like', "%{$search}%");
            });
        }

        if ($request->filled('type') && in_array($request->type, ['reseller', 'supplier'])) {
            $query->where('type', $request->type);
        }

        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $query->where('is_active', true);
            } elseif ($request->status === 'inactive') {
                $query->where('is_active', false);
            }
        }

        $contents = $query->paginate(15)->withQueryString();

        $all = HomeContent::all();
        $stats = [
            'total'    => $all->count(),
            'reseller' => $all->where('type', 'reseller')->count(),
            'supplier' => $all->where('type', 'supplier')->count(),
            'active'   => $all->where('is_active', true)->count(),
            'inactive' => $all->where('is_active', false)->count(),
        ];

        return view('admin.home-contents.index', compact('contents', 'stats'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'title'      => 'nullable|string|max:255',
            'type'       => 'required|in:reseller,supplier',
            'content'    => 'required|string',
            'sort_order' => 'nullable|integer|min:0',
            'is_active'  => 'nullable|boolean',
        ]);

        HomeContent::create([
            'title'      => $request->title,
            'type'       => $request->type ?? 'reseller',
            'content'    => $request->content,
            'sort_order' => (int) ($request->sort_order ?? 0),
            'is_active'  => $request->has('is_active') ? $request->boolean('is_active') : true,
            'created_by' => auth()->id(),
        ]);

        return redirect()->route('admin.home-contents.index')->with('success', 'Homepage content added successfully.');
    }

    public function update(Request $request, HomeContent $homeContent)
    {
        $request->validate([
            'title'      => 'nullable|string|max:255',
            'type'       => 'required|in:reseller,supplier',
            'content'    => 'required|string',
            'sort_order' => 'nullable|integer|min:0',
            'is_active'  => 'nullable|boolean',
        ]);

        $homeContent->update([
            'title'      => $request->title,
            'type'       => $request->type ?? $homeContent->type,
            'content'    => $request->content,
            'sort_order' => (int) ($request->sort_order ?? $homeContent->sort_order),
            'is_active'  => $request->has('is_active') ? $request->boolean('is_active') : $homeContent->is_active,
        ]);

        return redirect()->route('admin.home-contents.index')->with('success', 'Homepage content updated successfully.');
    }

    public function toggle(Request $request, HomeContent $homeContent)
    {
        $homeContent->update(['is_active' => !$homeContent->is_active]);

        $statusText = $homeContent->is_active ? 'activated' : 'deactivated';

        if ($request->wantsJson()) {
            return response()->json([
                'success'   => true,
                'is_active' => $homeContent->is_active,
                'message'   => "Content has been {$statusText}.",
            ]);
        }

        return back()->with('success', "Content has been {$statusText}.");
    }

    public function destroy(HomeContent $homeContent)
    {
        $homeContent->delete();

        return redirect()->route('admin.home-contents.index')->with('success', 'Homepage content deleted successfully.');
    }
}
