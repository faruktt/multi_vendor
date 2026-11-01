@php
    $fbPixelId = $appSettings['fb_pixel_id'] ?? null;
    $gtmId     = $appSettings['gtm_id'] ?? null;
    $trackingCurrency = 'BDT';
@endphp

{{-- ── 1. DataLayer Initialization (Always First) ── --}}
<script>
    window.dataLayer = window.dataLayer || [];
</script>

{{-- ── 2. Google Tag Manager (GTM) Container Script ── --}}
@if(!empty($gtmId))
<!-- Google Tag Manager -->
<script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
})(window,document,'script','dataLayer','{{ $gtmId }}');</script>
<!-- End Google Tag Manager -->
@endif

{{-- ── 3. Meta (Facebook) Pixel Base Code ── --}}
@if(!empty($fbPixelId))
<!-- Meta Pixel Code -->
<script>
!function(f,b,e,v,n,t,s)
{if(f.fbq)return;n=f.fbq=function(){n.callMethod?
n.callMethod.apply(n,arguments):n.queue.push(arguments)};
if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';
n.queue=[];t=b.createElement(e);t.async=!0;
t.src=v;s=b.getElementsByTagName(e)[0];
s.parentNode.insertBefore(t,s)}(window, document,'script',
'https://connect.facebook.net/en_US/fbevents.js');
fbq('init', '{{ $fbPixelId }}');
fbq('track', 'PageView');
</script>
<noscript><img height="1" width="1" style="display:none"
src="https://www.facebook.com/tr?id={{ $fbPixelId }}&ev=PageView&noscript=1"
/></noscript>
<!-- End Meta Pixel Code -->
@endif

{{-- ── 4. Custom Head Scripts (Configured via Admin Settings) ── --}}
@if(!empty($appSettings['custom_head_code']))
{!! $appSettings['custom_head_code'] !!}
@endif

{{-- ── 5. Standard eCommerce DataLayer & Pixel Dispatch Helper ── --}}
<script>
(function() {
    // Helper to push to GTM DataLayer
    window.trackDataLayer = function(eventName, ecommerceData, customParams) {
        window.dataLayer = window.dataLayer || [];
        var payload = Object.assign({
            event: eventName,
            ecommerce: ecommerceData || {}
        }, customParams || {});
        window.dataLayer.push(payload);
    };

    // View Content / View Item
    window.trackViewContent = function(item) {
        if (!item) return;
        var price = parseFloat(item.price || 0);
        var itemObj = {
            item_id: String(item.id || ''),
            item_name: item.name || '',
            price: price,
            item_category: item.category || '',
            quantity: parseInt(item.quantity || 1)
        };

        // GTM DataLayer
        window.trackDataLayer('view_item', {
            currency: '{{ $trackingCurrency }}',
            value: price,
            items: [itemObj]
        });

        // Facebook Pixel
        if (typeof window.fbq === 'function') {
            window.fbq('track', 'ViewContent', {
                content_ids: [String(item.id || '')],
                content_name: item.name || '',
                content_category: item.category || '',
                content_type: 'product',
                value: price,
                currency: '{{ $trackingCurrency }}'
            });
        }
    };

    // Add To Cart
    window.trackAddToCart = function(item) {
        if (!item) return;
        var price = parseFloat(item.price || 0);
        var qty = parseInt(item.quantity || 1);
        var totalVal = price * qty;
        var itemObj = {
            item_id: String(item.id || ''),
            item_name: item.name || '',
            price: price,
            item_category: item.category || '',
            quantity: qty
        };

        // GTM DataLayer
        window.trackDataLayer('add_to_cart', {
            currency: '{{ $trackingCurrency }}',
            value: totalVal,
            items: [itemObj]
        });

        // Facebook Pixel
        if (typeof window.fbq === 'function') {
            window.fbq('track', 'AddToCart', {
                content_ids: [String(item.id || '')],
                content_name: item.name || '',
                content_category: item.category || '',
                content_type: 'product',
                value: totalVal,
                currency: '{{ $trackingCurrency }}'
            });
        }
    };

    // Initiate Checkout / Begin Checkout
    window.trackInitiateCheckout = function(items, totalValue) {
        var val = parseFloat(totalValue || 0);
        var formattedItems = (items || []).map(function(it) {
            return {
                item_id: String(it.id || it.product_id || ''),
                item_name: it.name || it.product_name || '',
                price: parseFloat(it.price || 0),
                item_category: it.category || '',
                quantity: parseInt(it.quantity || it.qty || 1)
            };
        });

        var contentIds = formattedItems.map(function(it) { return it.item_id; }).filter(Boolean);

        // GTM DataLayer
        window.trackDataLayer('begin_checkout', {
            currency: '{{ $trackingCurrency }}',
            value: val,
            items: formattedItems
        });

        // Facebook Pixel
        if (typeof window.fbq === 'function') {
            window.fbq('track', 'InitiateCheckout', {
                content_ids: contentIds,
                contents: formattedItems.map(function(it) {
                    return { id: it.item_id, quantity: it.quantity, item_price: it.price };
                }),
                content_type: 'product',
                value: val,
                currency: '{{ $trackingCurrency }}',
                num_items: formattedItems.length
            });
        }
    };

    // Purchase
    window.trackPurchase = function(order) {
        if (!order) return;
        var val = parseFloat(order.value || order.total || 0);
        var formattedItems = (order.items || []).map(function(it) {
            return {
                item_id: String(it.id || it.product_id || ''),
                item_name: it.name || it.product_name || '',
                price: parseFloat(it.price || 0),
                item_category: it.category || '',
                quantity: parseInt(it.quantity || it.qty || 1)
            };
        });

        var contentIds = formattedItems.map(function(it) { return it.item_id; }).filter(Boolean);

        // GTM DataLayer
        window.trackDataLayer('purchase', {
            transaction_id: String(order.transaction_id || order.invoice_no || ''),
            value: val,
            currency: '{{ $trackingCurrency }}',
            tax: parseFloat(order.tax || 0),
            shipping: parseFloat(order.shipping || order.delivery_charge || 0),
            items: formattedItems
        });

        // Facebook Pixel
        if (typeof window.fbq === 'function') {
            window.fbq('track', 'Purchase', {
                content_ids: contentIds,
                contents: formattedItems.map(function(it) {
                    return { id: it.item_id, quantity: it.quantity, item_price: it.price };
                }),
                content_type: 'product',
                value: val,
                currency: '{{ $trackingCurrency }}',
                num_items: formattedItems.length
            });
        }
    };

    // View Item List
    window.trackViewItemList = function(items, listName) {
        var formattedItems = (items || []).map(function(it, idx) {
            return {
                item_id: String(it.id || it.product_id || ''),
                item_name: it.name || it.product_name || '',
                price: parseFloat(it.price || 0),
                item_category: it.category || '',
                index: idx + 1
            };
        });

        window.trackDataLayer('view_item_list', {
            item_list_name: listName || 'Catalog',
            items: formattedItems
        });
    };
})();
</script>

