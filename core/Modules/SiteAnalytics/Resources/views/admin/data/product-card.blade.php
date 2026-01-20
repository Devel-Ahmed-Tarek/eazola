@php
    $plans = \Modules\Product\Entities\Product::select(['id','slug', 'name']);
    $slug_name = $plans->pluck('name', 'slug');
    $id_slug = $plans->pluck('id','slug');

    $pages_array = $products_views->toArray();
    $plan_pages = array_map(function ($item) {
        $item['page'] = str_replace(['/shop/product/'],'',$item['page']);
        return $item;
    }, $pages_array);

    $product_with_names = [];
    foreach ($plan_pages ?? [] as $key => $item)
    {
        if (!empty(current($id_slug)[$item['page']]))
        {
            $product_with_names[$key]['id'] = current($id_slug)[$item['page']] ?? '';
            $product_with_names[$key]['users'] = $item['users'] ?? '';
            $product_with_names[$key]['name'] = current($slug_name)[$item['page']] ?? '';
        }
    }
@endphp

<div class="card">
        <div class="card-header px-4 py-5 bg-white">
            <h2 class="card-title m-0">{{__('Products')}}</h2>
        </div>
        <div class="card-header d-flex justify-content-between align-items-center px-4 py-3">
            <p class="item-title m-0">{{__('Page')}}</p>
            <p class="item-title m-0">{{__('User')}}</p>
        </div>
        <div class="card-inner">
            @foreach ($product_with_names ?? [] as $item)
                <div class="card-header d-flex justify-content-between align-items-center  px-4 py-3 bg-white">
                    <a href="{{$item['id'] ? route('tenant.admin.product.edit', $item['id']) : '#0'}}">
                        {{$item['name'] ?? ''}}
                    </a>
                    <p class="item-title m-0">{{ $item['users'] }}</p>
                </div>
            @endforeach
        </div>
    </div>

