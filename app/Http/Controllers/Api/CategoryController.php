<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CategoryController extends Controller
{
    public function index()
    {
        return response()->json(
            Category::withCount('products')->latest()->get()->map(fn($c) => $this->transform($c))
        );
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'  => 'required|string|max:255',
            'image' => 'nullable|image|max:2048',
        ]);

        $imagePath = $request->hasFile('image')
            ? $request->file('image')->store('categories', 'uploads')
            : null;

        $category = Category::create([
            'vendor_id' => $request->user()->vendor_id,
            'name'      => $validated['name'],
            'image'     => $imagePath,
        ]);

        return response()->json($this->transform($category->loadCount('products')), 201);
    }

    public function update(Request $request, Category $category)
    {
        $validated = $request->validate([
            'name'  => 'required|string|max:255',
            'image' => 'nullable|image|max:2048',
        ]);

        $imagePath = $category->image;
        if ($request->hasFile('image')) {
            if ($category->image) Storage::disk('uploads')->delete($category->image);
            $imagePath = $request->file('image')->store('categories', 'uploads');
        }

        $category->update(['name' => $validated['name'], 'image' => $imagePath]);

        return response()->json($this->transform($category->loadCount('products')));
    }

    public function destroy(Category $category)
    {
        if ($category->image) Storage::disk('uploads')->delete($category->image);
        $category->delete();
        return response()->json(['message' => 'Deleted']);
    }

    private function transform($category)
    {
        $data = $category->toArray();
        if (!empty($data['image'])) {
            $data['image'] = Storage::disk('uploads')->url($data['image']);
        }
        return $data;
    }
}
