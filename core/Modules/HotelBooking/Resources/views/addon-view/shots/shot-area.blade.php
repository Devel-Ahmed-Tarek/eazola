<section class="attraction-area pat-50 pab-50">
    <div class="container">
        <div class="section-title center-text">
            <h2 class="title"> {{$data['top_title']}} </h2>
            <div class="section-title-line"> </div>
        </div>
        <div class="row g-4 mt-4">
            @foreach($data['repeater_data']['repeater_title_'.get_user_lang()] ?? [] as $key => $title)
                @php
                    $repeater_image = $data['repeater_data']['repeater_image_'.get_user_lang()][$key] ?? '';
                    $repeater_description = $data['repeater_data']['repeater_description_'.get_user_lang()][$key] ?? '';
                @endphp
            <div class="col-xl-3 col-lg-4 col-sm-6">
                <div class="single-attraction-two radius-20">
                    <div class="single-attraction-two-thumb">
                        <a href="{!! render_image_url_by_attachment_id($repeater_image) !!}" class="gallery-popup-two">  {!! render_image_markup_by_attachment_id($repeater_image) !!} </a>
                    </div>
                    <div class="single-attraction-two-contents">
                        <h4 class="single-attraction-two-contents-title">   {{$title}} </h4>
                        <p class="single-attraction-two-contents-para">{{ \Illuminate\Support\Str::limit($repeater_description, 70, $end='.......') }} </p>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>


