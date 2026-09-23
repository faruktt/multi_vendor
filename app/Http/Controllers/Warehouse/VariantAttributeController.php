<?php

namespace App\Http\Controllers\Warehouse;

use App\Http\Controllers\Controller;
use App\Models\Color;
use App\Models\Size;
use App\Models\Vendor;
use Illuminate\Http\Request;

class VariantAttributeController extends Controller
{
    public function index(Vendor $branch)
    {
        $colors = Color::orderBy('sort_order')->orderBy('name')->get();
        $sizes  = Size::orderBy('sort_order')->orderBy('name')->get();

        return view('warehouse.variant-attributes.index', compact('branch', 'colors', 'sizes'));
    }

    // ── Colors ──────────────────────────────────────────────────────────

    public function storeColor(Request $request, Vendor $branch)
    {
        $request->validate([
            'name'     => 'required|string|max:100|unique:colors,name',
            'hex_code' => 'nullable|string|max:7|regex:/^#[0-9A-Fa-f]{3,6}$/',
        ]);

        Color::create([
            'name'       => $request->name,
            'hex_code'   => $request->hex_code,
            'sort_order' => Color::max('sort_order') + 1,
        ]);

        return back()->with('success', 'Color "' . $request->name . '" added.');
    }

    public function updateColor(Request $request, Vendor $branch, Color $color)
    {
        $request->validate([
            'name'     => 'required|string|max:100|unique:colors,name,' . $color->id,
            'hex_code' => 'nullable|string|max:7|regex:/^#[0-9A-Fa-f]{3,6}$/',
        ]);

        $color->update([
            'name'     => $request->name,
            'hex_code' => $request->hex_code,
        ]);

        return back()->with('success', 'Color updated.');
    }

    public function destroyColor(Vendor $branch, Color $color)
    {
        // Check if used in variants
        if ($color->variants()->exists()) {
            return back()->with('error', 'Cannot delete — this color is used by product variants.');
        }

        $color->delete();
        return back()->with('success', 'Color deleted.');
    }

    // ── Sizes ────────────────────────────────────────────────────────────

    public function storeSize(Request $request, Vendor $branch)
    {
        $request->validate([
            'name' => 'required|string|max:100|unique:sizes,name',
        ]);

        Size::create([
            'name'       => $request->name,
            'sort_order' => Size::max('sort_order') + 1,
        ]);

        return back()->with('success', 'Size "' . $request->name . '" added.');
    }

    public function updateSize(Request $request, Vendor $branch, Size $size)
    {
        $request->validate([
            'name' => 'required|string|max:100|unique:sizes,name,' . $size->id,
        ]);

        $size->update(['name' => $request->name]);

        return back()->with('success', 'Size updated.');
    }

    public function destroySize(Vendor $branch, Size $size)
    {
        if ($size->variants()->exists()) {
            return back()->with('error', 'Cannot delete — this size is used by product variants.');
        }

        $size->delete();
        return back()->with('success', 'Size deleted.');
    }
}
