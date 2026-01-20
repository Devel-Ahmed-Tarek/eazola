(function ($) {
    "use strict";



    $(document).ready(function () {

        /*
        ========================================
            Navbar Toggler
        ========================================
        */
        $(document).on('click', '.navbar-toggler, .click-nav-right-icon, .click-content-show', function (r) {
            r.preventDefault();
            if ($(this).hasClass("navbar-toggler")) {
                $(this).toggleClass("active");
            } else if ($(this).hasClass("click-nav-right-icon")) {
                $(".show-nav-content").toggleClass("show");
            }
        });

        /*
        ========================================
            Select Modal Menu Text js
        ========================================
        */
        let menuReplaceText = $('.restaurantMenuReplaceText');

        $(document).on('click', '.restaurantMenuSelectText', function () {
            let menuSelectText = $(this);
            menuSelectText.closest("li").addClass("selected").siblings().removeClass("selected");

            let menuSelectTextValue = menuSelectText.text();

            menuReplaceText.text(menuSelectTextValue);

        });

        /*
        ==================================================
            Sidebar menu open & close on click js
        ==================================================
        */
        $(document).on('click', '.clickSidebarBtn', function () {
            $('.restaurantMenu__index').toggleClass('open');
        });
        $(document).on('click', '.restaurantMenu__bars', function () {
            console.log('adnan');
            $('.restaurantMenu__index').removeClass('open');
        });
        $(document).on('mouseup', function (e) {
            if ($(e.target).closest('.restaurantMenu__index').length === 0) {
                $('.restaurantMenu__index').removeClass('open');
            }
        });

        // responsive sidebar open close
        $(document).on('click', '.responsiveSidebarBtn', function () {
            console.log('adnan2');
            $('.restaurantMenu__detailsPage').toggleClass('open');
            // $('.restaurantMenu__index').toggleClass('open');

        });
        $(document).on('click', '.restaurantMenu__bars', function () {
            $('.restaurantMenu__detailsPage').removeClass('open');
        });
        $(document).on('mouseup', function (e) {
            if ($(e.target).closest('.restaurantMenu__detailsPage').length === 0) {
                $('.restaurantMenu__detailsPage').removeClass('open');
            }
        });

        /*
        ========================================
            Checkout Item Close js
        ========================================
        */
        $(document).on('click', '.checkOut__product__close', function () {
            $(this).closest('.checkOut__product').hide(100);
        })

        /*
        ========================================
            Checkout Size Open close js
        ========================================
        */
        $(document).on('click', '.checkOutSize__click', function (e) {
            let checkOutInner = $(this).closest('.checkOutSize__wrap').find('.checkOutSize__inner');
            checkOutInner.toggleClass('active');
        });

        $(document).on('mouseup', function (e) {
            if ($(e.target).closest('.checkOutSize__wrap').length === 0) {
                $('.checkOutSize__inner').removeClass('active');
            }
        });

        $(document).on('click', '.checkOutSize__item', function () {
            let checkOutSizeItemText = $(this).text();
            let checkOutSizeTitle = $(this).closest('.checkOutSize__wrap').find('.checkOutSize__click');

            checkOutSizeTitle.text(checkOutSizeItemText);

            $(this).closest('.checkOutSize__inner').removeClass('active');
        })

        /*
        ========================================
            Checkout sidebar open close js
        ========================================
        */
        $(document).on('click', '.checkOutBtn', function () {
            $('.checkOut__wrapper, .checkOut__wrapper__overlay').toggleClass('active');
        })
        $(document).on('click', '.checkOutBarClose, .checkOut__wrapper__overlay', function () {
            $('.checkOut__wrapper, .checkOut__wrapper__overlay').removeClass('active');
        })

        /*
        ========================================
            Product Quantity js
        ========================================
        */
        $(document).on('click', '.plus', function () {
            let selectedInput = $(this).prev('.quantity-input');
            selectedInput[0].stepUp(1);
        });
        $(document).on('click', '.substract', function () {
            let selectedInput = $(this).next('.quantity-input');
            if (selectedInput.val() > 1) {
                selectedInput[0].stepDown(1);
            }
        });


        /*
        ======================================================
           Active page List Click details page
        ======================================================
        */
        $(document).on("click", ".detailsListItem", function () {
            let activeList = $(this);

            activeAndPrevActive(activeList);
        });

        let tempObj = { first: null, second: null };

        function addClassToPrevItem() {
            $("." + tempObj.first).siblings().removeClass("prevActive");
            $("." + tempObj.first).addClass("prevActive");
        }

        function activeAndPrevActive(activeList, enablePrev = true) {
            // get class name from this
            let classNameList = activeList.attr("data-details");

            // to check first is null or not if null then store current element
            if (tempObj.first == null) {
                tempObj.first = classNameList;
            } else {
                // cehck second element is not empty
                if (tempObj.second !== null && tempObj.second !== classNameList) {
                    tempObj.first = tempObj.second;
                }
                tempObj.second = classNameList;
            }

            if (enablePrev) {
                addClassToPrevItem();
            }

            activeList.siblings().removeClass("active");
            activeList.addClass("active");

            $("." + classNameList).siblings().removeClass("active");
            $("." + classNameList).addClass("active");
        }

        activeAndPrevActive($('.detailsListItem.active'), false);

        const urlParams = new URLSearchParams(location.search);
        // now check selectedClass key are exist on this param or not if exist then select this one
        if (urlParams.has('selectedClass')) {
            activeAndPrevActive($('.detailsListItem[data-details=' + urlParams.get('selectedClass') + ']'), false);
        }

        /*
        ========================================
            Tab
        ========================================
        */
        $(document).on('click', 'ul.tabs li', function () {
            var tab_id = $(this).attr('data-tab');

            $('ul.tabs li').removeClass('active');
            $('.tab_content_item').removeClass('active');

            $(this).addClass('active');
            $("#" + tab_id).addClass('active');
        });

        // Step Form with tab

        var totalTab = $('#restaurantMenu__tab li').length;
        var tabNavList = $('#restaurantMenu__tab li');
        let currentTabIndex = 1;

        $(document).on('click', '#restaurantMenu__tab li', function () {

            if ((totalTab - 1) === tabNavList.index($(this))) {
                $('#nextBtn').addClass('d-none');
            } else {
                $('#nextBtn').removeClass('d-none');
            }

            if ($('#restaurantMenu__tab li:first-child').hasClass('active')) {
                $('#prevBtn').addClass('d-none');
            } else {
                $('#prevBtn').removeClass('d-none');
            }

        });

        // next and previous js start
        $(document).on('click', '#nextBtn', function (e) {
            let currentState = $('#restaurantMenu__tab li.active');
            let currentContent = $('#restaurantMenu__tabContent .tab_content_item.active');
            currentTabIndex = currentState.index() + 1;

            if (currentTabIndex === totalTab) {
                return false;
            }
            if (currentTabIndex === totalTab - 1) {
                $(this).addClass('d-none');
            }

            currentState.removeClass('active').next().addClass('active');
            currentState.next();

            currentContent.siblings().removeClass('active')
            currentContent.removeClass('active').next().addClass('active');
            currentContent.next();

            currentTabIndex++;
        });

        $(document).on('click', '#prevBtn', function (e) {
            let currentState = $('#restaurantMenu__tab li.active');
            let currentContent = $('#restaurantMenu__tabContent .tab_content_item.active');

            currentTabIndex = currentState.index() + 1;
            if (currentTabIndex === 1) return;

            if (currentTabIndex === totalTab) {
                $('#nextBtn').removeClass('d-none');
            }

            currentState.removeClass('active').prev().addClass('active');
            currentState.prev();

            currentContent.siblings().removeClass('active')
            currentContent.removeClass('active').prev().addClass('active');
            currentContent.prev();

            currentTabIndex--;
        });

    });

    /*
    ========================================
        click and slide scroll js
    ========================================
    */
    window.addEventListener('load', function () {
        let categoryWrapList = document.getElementById('restaurantMenu__wrapper');
        if (categoryWrapList) {
            CategorySlideFunction();
        }
    });

    function CategorySlideFunction(r) {
        const categoryWrap = '#restaurantMenu__wrapper';
        const categoryWrap2 = document.querySelector(categoryWrap);
        const categoryWrapWidth = categoryWrap2.clientWidth;

        const categorySlide = '#restaurantMenu__tab';
        const categorySlide2 = document.querySelector(categorySlide);

        const arrowsRight = document.querySelector('#nextBtn');
        const arrowsLeft = document.querySelector('#prevBtn');

        let slideChildren = categorySlide2.children;
        let totalWidth = 0;

        for (let i = 0; i < slideChildren.length; i++) {
            totalWidth += parseInt(slideChildren[i].offsetWidth);
        }

        if (totalWidth < categoryWrapWidth) {
            arrowsRight.classList.add('hidden_item');
            arrowsLeft.classList.add('hidden_item');
        } else {
            arrowsRight.classList.remove('hidden_item');
            arrowsLeft.classList.remove('hidden_item');
        }

        const categoryComponents = document.querySelectorAll(categoryWrap);

        for (let i = 0; i < categoryComponents.length; i++) {
            const ItemComponent = categoryComponents[i];
            const contentSlide = ItemComponent.querySelector(categorySlide);
            let x = 0;
            let mx = 0;
            const maxScrollWidth = contentSlide.scrollWidth - contentSlide.clientWidth / 4 - contentSlide.clientWidth / 4;
            const arrowsRight = ItemComponent.querySelector('#nextBtn');
            const arrowsLeft = ItemComponent.querySelector('#prevBtn');

            // arrowsLeft.classList.add('hidden_item');

            if (maxScrollWidth !== 0) {
                ItemComponent.classList.add('has-arrows');
            }

            if (arrowsRight) {
                arrowsRight.addEventListener('click', function (event) {
                    event.preventDefault();
                    x = contentSlide.clientWidth / 4 + contentSlide.scrollLeft + 0;
                    contentSlide.scroll({
                        left: x,
                        behavior: 'smooth',
                    });
                });
            }

            if (arrowsLeft) {
                arrowsLeft.addEventListener('click', function (event) {
                    event.preventDefault();
                    x = contentSlide.clientWidth / 4 - contentSlide.scrollLeft + 0;
                    contentSlide.scroll({
                        left: -x,
                        behavior: 'smooth',
                    });
                });
            }

            /**
             * Mouse move handler.
             */
            const mousemoveHandler = (e) => {
                const mx2 = e.pageX - contentSlide.offsetLeft;
                if (mx) {
                    contentSlide.scrollLeft = contentSlide.sx + mx - mx2;
                }
            };

            /**
             * Mouse down handler.
             */
            const mousedownHandler = (e) => {
                contentSlide.sx = contentSlide.scrollLeft;
                mx = e.pageX - contentSlide.offsetLeft;
                contentSlide.classList.add('dragging');
            };

            /**
             * Scroll handler.
             */
            const scrollHandler = () => {
                toggleArrows();
            };

            /**
             * Toggle arrow handler.
             */
            const toggleArrows = () => {
                if (contentSlide.scrollLeft > maxScrollWidth - 10) {
                    arrowsRight.classList.add('hidden_item');
                } else if (contentSlide.scrollLeft < 10) {
                    arrowsLeft.classList.add('hidden_item');
                } else {
                    arrowsRight.classList.remove('hidden_item');
                    arrowsLeft.classList.remove('hidden_item');
                }
            };

            if (contentSlide.scrollLeft > maxScrollWidth - 10) {
                arrowsRight.classList.add('hidden_item');
            } else if (contentSlide.scrollLeft < 10) {
                arrowsLeft.classList.add('hidden_item');
            } else {
                arrowsRight.classList.remove('hidden_item');
                arrowsLeft.classList.remove('hidden_item');
            }

            /**
             * Mouse up handler.
             */
            const mouseupHandler = () => {
                mx = 0;
                contentSlide.classList.remove('dragging');
            };

            contentSlide.addEventListener('mousemove', mousemoveHandler);
            contentSlide.addEventListener('mousedown', mousedownHandler);
            if (ItemComponent !== undefined) {
                contentSlide.addEventListener('scroll', scrollHandler);
            }
            contentSlide.addEventListener('mouseup', mouseupHandler);
            contentSlide.addEventListener('mouseleave', mouseupHandler);
        }
    }

    if ($('.detailsPage__area').length) {
        let navbar = $(".navbar__area");
        let detailsPage = $(".detailsPage__area");
        if (detailsPage.length) {
            detailsPage.scroll(function() {
                let detailsPageRect = detailsPage[0].getBoundingClientRect();
                if (detailsPageRect.top >= 0) {
                    navbar.addClass("sticky__nav");
                } else {
                    navbar.removeClass("sticky__nav");
                }
                // Check if scroll position is at the top (0)
                if ($(this).scrollTop() === 0) {
                    navbar.removeClass("sticky__nav");
                }
            });
        }
    }


})(jQuery);
