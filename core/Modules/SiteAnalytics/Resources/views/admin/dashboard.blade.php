@extends(route_prefix().'admin.admin-master')

@section('title')
    {{ __('Site Analytics Dashboard') }}
@endsection

@section('style')
    <style>
        .card-header{
            background-color: rgba(249,250,251,1);
        }
        .card-header p{
            font-size: .75rem;
        }
        .apexcharts-canvas {
            margin-inline: auto;
        }
        .pagesFav {
            object-fit: contain;
        }
        .recent-orderChart {
            height: 100%;
        }
        a{
            text-decoration: none;
            color: var(--bs-dark);
        }
    </style>
@endsection

@section('content')
    <div class="dashboard-recent-order">
        <div class="row mt-4">
            <div class="col-12">
                <div class="d-flex justify-content-end">
                    @include('siteanalytics::admin.data.filter', ['type' => 'dashboard'])
                </div>
            </div>
        </div>

        <div class="row g-4 mt-1">
            @each('siteanalytics::admin.stats.card', $stats, 'stat')
        </div>

        <div class="dashboard-recent-order">
            <div class="row g-4 mt-1">
                <div class="col-md-6">
                    <div class="p-4 recent-order-wrapper recent-orderChart bg-white">
                        <div class="wrapper d-flex justify-content-between">
                            <div class="header-wrap">
                                <h4 class="header-title mb-2 text-capitalize">{{__("all page views")}}</h4>
                            </div>
                        </div>
                        <div class="page-view chart-wrapper">
                            <div class="my-2" id="chart-total"></div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="p-4 recent-order-wrapper recent-orderChart bg-white">
                        <div class="wrapper d-flex justify-content-between">
                            <div class="header-wrap">
                                <h4 class="header-title mb-2 text-capitalize">{{__("locations and devices")}}</h4>
                            </div>
                        </div>
                        <div class="page-view chart-wrapper">
                            <div id="chart-country"></div>
                            <div id="chart-device"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-1 row g-4 mb-5">
            <div class="col-lg-6">
                @includeWhen(!empty(get_static_option('site_analytics_page_view')) ,'siteanalytics::admin.data.pages-card')
            </div>
            <div class="col-lg-6">
                @includeWhen(!empty(get_static_option('site_analytics_view_source')) ,'siteanalytics::admin.data.sources-card')
            </div>
            <div class="col-lg-6">
                @includeWhen(!empty(get_static_option('site_analytics_users_country')) ,'siteanalytics::admin.data.users-card')
            </div>
            <div class="col-lg-6">
                @includeWhen(!empty(get_static_option('site_analytics_users_device')) ,'siteanalytics::admin.data.devices-card')
            </div>
            @each('siteanalytics::admin.data.utm-card', $utm, 'data')
        </div>
        @endsection

        @section('scripts')
            <script src="{{global_asset('assets/landlord/admin/js/apexcharts.js')}}"></script>

            @php
                $views = json_encode(array_column(current($pages_charts) ,'total_views'));
                $date = json_encode(array_column(current($pages_charts) ,'time'));

                $country = json_encode(array_column(current($users) ,'country'));
                $country_users = json_encode(array_column(current($users) ,'users'));

                $device = json_encode(array_column(current($devices) ,'type'));
                $device_users = json_encode(array_column(current($devices) ,'users'));
            @endphp
            <script>
                $(document).ready(function () {
                    const chartByTotal = () => {
                        return {
                            series: [{
                                name: `{{__('Page Views')}}`,
                                data: {{$views}}
                            }],
                            chart: {
                                height: 500,
                                type: 'bar',
                            },
                            plotOptions: {
                                bar: {
                                    borderRadius: 10,
                                    dataLabels: {
                                        position: 'top',
                                    },
                                }
                            },
                            dataLabels: {
                                enabled: true,
                                offsetY: -23,
                                style: {
                                    fontSize: '14px',
                                    colors: ["#304758"]
                                }
                            },

                            xaxis: {
                                categories: <?php echo $date ?>,
                                position: 'top',
                                axisBorder: {
                                    show: false
                                },
                                axisTicks: {
                                    show: false
                                },
                                crosshairs: {
                                    fill: {
                                        type: 'gradient',
                                        gradient: {
                                            colorFrom: 'rgb(214,233,255)',
                                            colorTo: '#BED1E6',
                                            stops: [0, 100],
                                            opacityFrom: 0.4,
                                            opacityTo: 0.5,
                                        }
                                    }
                                },
                                tooltip: {
                                    enabled: false,
                                }
                            },
                            yaxis: {
                                axisBorder: {
                                    show: false
                                },
                                axisTicks: {
                                    show: false,
                                },
                                labels: {
                                    show: false
                                }

                            },
                            title: {
                                text: '{{ucwords(str_replace('_',' ', $period))}} Page Views',
                                floating: false,
                                offsetY: 0,
                                align: 'center',
                                bottom: 0,
                                style: {
                                    color: '#444'
                                }
                            }
                        };
                    }

                    const chartByCountry = () => {
                        return {
                            series: {{$country_users}},
                            chart: {
                                width: 400,
                                type: 'pie',
                            },
                            labels: <?php echo $country ?>,
                            responsive: [{
                                breakpoint: 480,
                                options: {
                                    chart: {
                                        width: 200
                                    },
                                    legend: {
                                        position: 'bottom'
                                    }
                                }
                            }]
                        };
                    }

                    const chartByDevice = () => {
                        return {
                            series: {{$device_users}},
                            chart: {
                                width: 390,
                                type: 'pie',
                            },
                            labels: <?php echo $device ?>,
                            responsive: [{
                                breakpoint: 480,
                                options: {
                                    chart: {
                                        width: 200
                                    },
                                    legend: {
                                        position: 'bottom'
                                    }
                                }
                            }]
                        };
                    }

                    new ApexCharts(document.querySelector("#chart-total"), chartByTotal()).render();
                    new ApexCharts(document.querySelector("#chart-country"), chartByCountry()).render();
                    new ApexCharts(document.querySelector("#chart-device"), chartByDevice()).render();
                });
            </script>
@endsection
