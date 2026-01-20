<div class="card">
        <div class="card-header px-4 py-5 bg-white">
            <h2 class="card-title m-0">{{__('Pages')}}</h2>
        </div>
        <div class="card-header d-flex justify-content-between align-items-center px-4 py-3">
            <p class="item-title m-0">{{__('Page')}}</p>
            <p class="item-title m-0">{{__('User')}}</p>
        </div>
        <div class="card-inner">
            @foreach ($pages as $page)
                @php
                    $newString = preg_replace_callback('/^\/|^\//', function ($matches) {
                        return $matches[0] === '/' ? '' : 'home';
                       }, $page->page);

                    if ($newString === '')
                        {
                            $newString = 'home';
                        }
                @endphp

                <div class="card-header d-flex justify-content-between align-items-center  px-4 py-3 bg-white">
                    <a href="{{$page->page}}" target="_blank">
                        {{$newString}}
                    </a>
                    <p class="item-title m-0">{{ $page->users }}</p>
                </div>
            @endforeach
        </div>
    </div>

