<div class="col-lg-6">
    <div class="card radius-5 py-3">
        <div class="card-body d-flex flex-wrap gap-3 align-items-center justify-content-between">
            <h2 class="card-title card-title-custom m-0">{{ $stat['key'] }}</h2>
            <h2 class="card_title card-title-value m-0">{{ $stat['value'] }}</h2>
            @if (isset($stat['percentage']) && $stat['percentage'] > 0)
                <h2 class="card-title-value m-0">
                    @include(sprintf('siteanalytics::admin.stats.%s-icon', $stat['increase'] ? 'increase' : 'decrease'))
                </h2>
            @endif
        </div>
    </div>
</div>
