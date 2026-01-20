<div class="card">
    <div class="card-header px-4 py-5 bg-white">
        <h2 class="card-title m-0">{{__('Sources')}}</h2>
    </div>
    <div class="card-header d-flex justify-content-between align-items-center px-4 py-3">
        <p class="item-title m-0">{{__('Page')}}</p>
        <p class="item-title m-0">{{__('User')}}</p>
    </div>
    <div class="card-inner">
        @foreach ($sources as $source)
            <div class="card-header d-flex justify-content-between align-items-center  px-4 py-3 bg-white">
                <div class="d-flex gap-2">
                    <img class="pagesFav" src="https://www.google.com/s2/favicons?domain={{ urlencode($source->page) }}" alt="" />
                    <a href="http://{{$source->page}}" target="_blank">
                        {{$source->page}}
                    </a>
                </div>

                <p class="item-title m-0">{{ $source->users }}</p>
            </div>
        @endforeach
    </div>
</div>
