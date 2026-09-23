<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\OrderStatus;
use App\Models\Sale;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class OrderStatusController extends Controller
{
    public const COLORS = [
        'slate', 'blue', 'indigo', 'purple', 'teal',
        'emerald', 'amber', 'red', 'rose', 'sky', 'violet', 'orange',
    ];

    public function index()
    {
        $orderStatuses = OrderStatus::orderBy('sort_order')->get();

        return view('admin.order-statuses', ['orderStatuses' => $orderStatuses, 'colors' => self::COLORS]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'label' => 'required|string|max:100|unique:order_statuses,label',
            'color' => 'required|string|in:' . implode(',', self::COLORS),
        ]);

        $key = Str::slug($request->label, '_');
        if (OrderStatus::where('key', $key)->exists()) {
            return back()->with('error', 'A status with a matching internal key already exists.');
        }

        OrderStatus::create([
            'key'        => $key,
            'label'      => $request->label,
            'color'      => $request->color,
            'sort_order' => (int) OrderStatus::max('sort_order') + 1,
        ]);

        return back()->with('success', 'Order status added.');
    }

    public function update(Request $request, OrderStatus $orderStatus)
    {
        $request->validate([
            'label' => 'required|string|max:100|unique:order_statuses,label,' . $orderStatus->id,
            'color' => 'required|string|in:' . implode(',', self::COLORS),
        ]);

        $orderStatus->update($request->only('label', 'color'));

        return back()->with('success', 'Order status updated.');
    }

    public function reorder(Request $request)
    {
        $request->validate(['order' => 'required|array']);

        foreach ($request->order as $index => $id) {
            OrderStatus::where('id', $id)->update(['sort_order' => $index]);
        }

        return back()->with('success', 'Order updated.');
    }

    public function destroy(OrderStatus $orderStatus)
    {
        if ($orderStatus->is_protected) {
            return back()->with('error', 'This status is required by the system and cannot be deleted.');
        }

        $inUse = Sale::withoutGlobalScopes()->where('order_status', $orderStatus->key)->count();
        if ($inUse > 0) {
            return back()->with('error', "Cannot delete — {$inUse} order(s) currently use this status.");
        }

        $orderStatus->delete();

        return back()->with('success', 'Order status deleted.');
    }
}
