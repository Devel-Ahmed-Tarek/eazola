<!-- footer area start -->
<footer class="footer-area footer-area-one footer-border-round footer-bg-1">
    <div class="container">
        <div class="row">
            <div class="col-lg-12">
                <div class="footer-wrapper pat-100 pab-100">
                    <div class="footer-contents center-text">
                        {!! render_frontend_sidebar('hotel_booking_footer',['column'=> true]) !!}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="copyright-area footer-padding copyright-bg-1">
        <div class="container">
            <div class="copyright-contents">
                <div class="copyright-contents-flex">
                    <div class="copyright-contents-items">
                        {!! render_frontend_sidebar('hotel_booking_bottom_footer',['column'=> true]) !!}
                    </div>
                    <span class="copyright-contents-main"> {!! get_footer_copyright_text_tenant(get_user_lang()) !!} </span>
                </div>
            </div>
        </div>
    </div>
</footer>
<!-- footer area end -->

<!-- back to top area start -->
<div class="progressParent">
    <svg class="backCircle svg-inner" width="100%" height="100%" viewBox="-1 -1 102 102">
        <path d="M50,1 a49,49 0 0,1 0,98 a49,49 0 0,1 0,-98" style="transition: stroke-dashoffset 10ms linear 0s; stroke-dasharray: 307.919, 307.919; stroke-dashoffset: 307.919;"></path>
    </svg>
</div>
