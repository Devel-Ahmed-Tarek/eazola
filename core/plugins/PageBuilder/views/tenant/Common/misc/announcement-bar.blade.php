@if(!empty($data['items']))
    @once
        <style>
            .pb-announcement-bar {
                width: 100%;
                overflow: hidden;
                font-size: 14px;
                line-height: 1.4;
                font-weight: 500;
                letter-spacing: 0.01em;
            }

            .pb-announcement-bar__inner {
                display: flex;
                align-items: center;
                min-height: 40px;
                padding: 10px 16px;
            }

            .pb-announcement-bar__inner--center {
                justify-content: center;
            }

            .pb-announcement-bar__inner--start {
                justify-content: flex-start;
            }

            .pb-announcement-bar__inner--end {
                justify-content: flex-end;
            }

            .pb-announcement-bar__track {
                display: inline-flex;
                align-items: center;
                gap: 16px;
                white-space: nowrap;
            }

            .pb-announcement-bar__item,
            .pb-announcement-bar__link {
                display: inline-flex;
                align-items: center;
                gap: 6px;
            }

            .pb-announcement-bar__link {
                text-decoration: none;
                transition: opacity 0.2s ease;
            }

            .pb-announcement-bar__link:hover {
                opacity: 0.75;
            }

            .pb-announcement-bar__separator {
                opacity: 0.55;
            }

            .pb-announcement-bar--marquee .pb-announcement-bar__marquee {
                display: flex;
                width: max-content;
                animation: pbAnnouncementMarquee 28s linear infinite;
            }

            .pb-announcement-bar--marquee .pb-announcement-bar__track + .pb-announcement-bar__track {
                margin-inline-start: 48px;
            }

            @keyframes pbAnnouncementMarquee {
                0% {
                    transform: translateX(0);
                }
                100% {
                    transform: translateX(-50%);
                }
            }

            [dir="rtl"] .pb-announcement-bar--marquee .pb-announcement-bar__marquee {
                animation-name: pbAnnouncementMarqueeRtl;
            }

            @keyframes pbAnnouncementMarqueeRtl {
                0% {
                    transform: translateX(0);
                }
                100% {
                    transform: translateX(50%);
                }
            }

            @media (prefers-reduced-motion: reduce) {
                .pb-announcement-bar--marquee .pb-announcement-bar__marquee {
                    animation: none;
                }
            }
        </style>
    @endonce

    @php
        $barClass = 'pb-announcement-bar';
        if (!empty($data['enable_marquee'])) {
            $barClass .= ' pb-announcement-bar--marquee';
        }
        $innerAlignClass = 'pb-announcement-bar__inner--' . ($data['text_align'] ?? 'center');
    @endphp

    <section
        class="{{ $barClass }}"
        data-padding-top="{{ $data['padding_top'] }}"
        data-padding-bottom="{{ $data['padding_bottom'] }}"
        style="background-color: {{ $data['bg_color'] }}; color: {{ $data['text_color'] }};"
        aria-label="{{ __('Announcement') }}"
    >
        <div class="pb-announcement-bar__inner {{ $innerAlignClass }}">
            @if(!empty($data['enable_marquee']))
                <div class="pb-announcement-bar__marquee">
                    @foreach([1, 2] as $loopIndex)
                        <div class="pb-announcement-bar__track">
                            @foreach($data['items'] as $index => $item)
                                @if($index > 0)
                                    <span class="pb-announcement-bar__separator">{{ $data['separator'] }}</span>
                                @endif

                                @if(!empty($item['url']))
                                    <a href="{{ $item['url'] }}" class="pb-announcement-bar__link">
                                        <span>{{ $item['text'] }}</span>
                                    </a>
                                @else
                                    <span class="pb-announcement-bar__item">{{ $item['text'] }}</span>
                                @endif
                            @endforeach
                        </div>
                    @endforeach
                </div>
            @else
                <div class="pb-announcement-bar__track">
                    @foreach($data['items'] as $index => $item)
                        @if($index > 0)
                            <span class="pb-announcement-bar__separator">{{ $data['separator'] }}</span>
                        @endif

                        @if(!empty($item['url']))
                            <a href="{{ $item['url'] }}" class="pb-announcement-bar__link">
                                <span>{{ $item['text'] }}</span>
                            </a>
                        @else
                            <span class="pb-announcement-bar__item">{{ $item['text'] }}</span>
                        @endif
                    @endforeach
                </div>
            @endif
        </div>
    </section>
@endif
