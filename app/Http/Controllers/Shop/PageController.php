<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\Page;
use App\Models\Vendor;
use Illuminate\Http\Request;

class PageController extends Controller
{
    public function show($slug)
    {
        $branch = Vendor::onlineStore();
        $page = Page::where('slug', $slug)
            ->where('is_active', true)
            ->firstOrFail();

        return view('shop.pages.show', compact('branch', 'page'));
    }
}
