@php
    $plans = \App\Models\PricePlan::where('status', 1)->select(['id', 'title'])->pluck('title', 'id');

    $pages_array = $pages->toArray();
    $plan_pages = array_map(function ($item) {
        $item['page'] = str_replace(['/plan-order/', '/view-plan/','/trial'],'',$item['page']);
        return $item;
    }, $pages_array);

    $plan_with_names = [];
    foreach ($plan_pages ?? [] as $key => $item)
    {
        $plan_with_names[$key]['id'] = $item['page'] ?? '';
        $plan_with_names[$key]['users'] = $item['users'];
        $plan_with_names[$key]['name'] = current($plans)[$item['page']] ?? '';
    }
@endphp

<div class="card">
        <div class="card-header px-4 py-5 bg-white">
            <h2 class="card-title m-0">{{__('Pages')}}</h2>
        </div>
        <div class="card-header d-flex justify-content-between align-items-center px-4 py-3">
            <p class="item-title m-0">{{__('Page')}}</p>
            <p class="item-title m-0">{{__('User')}}</p>
        </div>
        <div class="card-inner">
            @foreach ($plan_with_names ?? [] as $item)
                <div class="card-header d-flex justify-content-between align-items-center  px-4 py-3 bg-white">
                    <a href="{{$item['id'] ? route('landlord.admin.price.plan.edit', $item['id']) : '#0'}}">
                        {{$item['name'] ?? ''}}
                    </a>
                    <p class="item-title m-0">{{ $item['users'] }}</p>
                </div>
            @endforeach
        </div>
    </div>

