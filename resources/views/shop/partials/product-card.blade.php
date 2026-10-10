{{-- Reuses the new boutique product card design for full consistency --}}
@include('shop.partials.shelf-product-card', [
    'product' => $product,
    'branch'  => $branch ?? null,
    'widthClass' => $widthClass ?? 'w-full'
])
