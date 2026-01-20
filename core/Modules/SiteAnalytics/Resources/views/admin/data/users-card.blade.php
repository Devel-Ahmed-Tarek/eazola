<div class="card">
    <div class="card-header px-4 py-5 bg-white">
        <h2 class="card-title m-0">{{__('Geo Locations')}}</h2>
    </div>
    <div class="card-header d-flex justify-content-between align-items-center px-4 py-3">
        <p class="item-title m-0">{{__('Country')}}</p>
        <p class="item-title m-0">{{__('Users')}}</p>
    </div>
    <div class="card-inner">
        @foreach ($users as $user)
            <div class="card-header d-flex justify-content-between align-items-center  px-4 py-3 bg-white">
                <div class="d-flex gap-2">
                    <a href="#0">
                        {{$user->country}}
                    </a>
                </div>

                <p class="item-title m-0">{{ $user->users }}</p>
            </div>
        @endforeach
    </div>
</div>
