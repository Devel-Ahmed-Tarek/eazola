@php
    $product_array = $pages->toArray();
    $order_array = $orders->toArray();

    $views = json_encode(array_column($product_array, 'total_views'));
    $sale = json_encode(array_column($order_array, 'total_sale'));


    $tempProductArr = $pages->pluck("total_views", "time");
    $tempOrderArr = $orders->pluck("total_sale", "time");
    $finalArray = [];

    $index = 0;
    foreach($tempProductArr as $key => $value){
        // set default value
        $finalArray[$index] = 0;

        // check is order exist or not if exist then calculate value otherwise set 0
        if($tempOrderArr[$key] ?? false) {
            // calculate here
            $calculated = (1 - $tempOrderArr[$key] / $value) * 100;
            $finalArray[$index++] = (float) number_format($calculated, 2);
        }
    }

    $bounce_rates = json_encode($finalArray);

    $months = count($product_array) > count($order_array) ? array_column($product_array, 'time') : array_column($order_array, 'time');
    $months = $months ? \Modules\SiteAnalytics\Http\Services\SiteAnalyticsService::sortElements($months) : [];
    $months = json_encode($months);


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
                    name: `{{__('Views')}}`,
                    data: {{$views}}
                }, {
                    name: `{{__('Sale')}}`,
                    data: {{$sale}}
                }, {
                    name: `{{__('Bounce Rate (%)')}}`,
                    data: {{$bounce_rates}}
                }],
                chart: {
                    height: 450,
                    type: 'area'
                },
                dataLabels: {
                    enabled: false
                },
                stroke: {
                    curve: 'smooth'
                },
                xaxis: {
                    type: 'string',
                    categories: <?php echo $months ?>
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
