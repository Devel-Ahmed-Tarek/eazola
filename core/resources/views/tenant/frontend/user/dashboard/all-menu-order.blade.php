@extends('tenant.frontend.user.dashboard.user-master')
@section('title')
{{__('All Reservation')}}
@endsection
@section('style')
<link rel="stylesheet" href="{{global_asset('assets/tenant/frontend/css/custom-dashboard.css')}}">

<style>
    button.low,
    button.status-open{
        display: inline-block;
        background-color: #6bb17b;
        padding: 3px 10px;
        border-radius: 4px;
        color: #fff;
        text-transform: capitalize;
        border: none;
        font-weight: 600;
    }
    button.high,
    button.status-close{
        display: inline-block;
        background-color: #c66060;
        padding: 3px 10px;
        border-radius: 4px;
        color: #fff;
        text-transform: capitalize;
        border: none;
        font-weight: 600;
    }
    button.medium {
        display: inline-block;
        background-color: #70b9ae;
        padding: 3px 10px;
        border-radius: 4px;
        color: #fff;
        text-transform: capitalize;
        border: none;
        font-weight: 600;
    }
    button.urgent {
        display: inline-block;
        background-color: #bfb55a;
        padding: 3px 10px;
        border-radius: 4px;
        color: #fff;
        text-transform: capitalize;
        border: none;
        font-weight: 600;
    }
</style>
@endsection
@section('section')
<div class="mb-4">
    <x-restaurant::frontent.user-home-navbar />
</div>

@if(count($all_menu_orders) > 0)
<div class="table-responsive">
    <table class="table table-striped">
        <thead>
        <tr>
            <th>{{__('Order ID')}}</th>
            <th>{{__('Created')}}</th>
            <th>{{__('Amount')}}</th>
            <th>{{__('Status')}}</th>
            <th>{{__('Action')}}</th>
        </tr>
        </thead>
        <tbody>
        @foreach($all_menu_orders ?? [] as $data)
        <tr>
            <td>{{$data->id}}</td>
            <td>
                <p> <small>{{$data->created_at->format('D, d M Y')}}</small></p>
            </td>
            <td>{{amount_with_currency_symbol($data->total_amount)}}</td>
            <td>
                @if($data->status == 0)
                <span class="badge bg-warning">
                    pending
                </span>
                @elseif($data->status == 1)
                <span class="badge bg-success">
                    approved
                </span>
                @elseif($data->status == 2)
                <span class="badge bg-info">
                    in-progress
                </span>
                @elseif($data->status == 3)
                <span class="badge bg-danger">
                    cancled
                </span>
                @elseif($data->status == 4)
                <span class="badge bg-danger">
                    cancel requested
                </span>
                @endif
            </td>
            <td>
                <a href="{{route('tenant.user.dashboard.view.order',$data->id)}}"  class="btn btn-info btn-sm mb-3" target="_blank"><i class="fas fa-eye"></i> view</a>
                @if($data->status != 4 && $data->status != 3)
                <x-table.btn.swal.delete :route="route('tenant.user.dashboard.order.cancel.request',$data->id)" type="'cancel'" />
                @endif

            </td>
        </tr>
        @endforeach
        </tbody>
    </table>
</div>
<div class="blog-pagination">
    {{ $all_menu_orders->links() }}
</div>
@else
<div class="alert alert-warning">{{__('Nothing Found')}}</div>
@endif

@endsection

@section('scripts')
<x-table.btn.swal.js :message="'Do you want to cancel this orders?'" :type="'Cancel'"/>
<script src="{{global_asset('assets/common/js/bootstrap.bundle.min.js')}}"></script>

<script>
    (function (){
        "use strict";

        $(document).on('click','.change_priority',function (e){
            e.preventDefault();
            //get value
            var priority = $(this).data('val');
            var id = $(this).data('id');
            var currentPriority =  $(this).parent().prev('button').text();
            currentPriority = currentPriority.trim();
            $(this).parent().prev('button').removeClass(currentPriority).addClass(priority).text(priority);
            //ajax call
            $.ajax({
                'type': 'post',
                'url' : "{{route('tenant.user.dashboard.support.ticket.priority.change')}}",
                'data' : {
                    _token : "{{csrf_token()}}",
                    priority : priority,
                    id : id,
                },
                success: function (data){
                    $(this).parent().find('button.'+currentPriority).removeClass(currentPriority).addClass(priority).text(priority);
                }
            })
        });
        $(document).on('click','.status_change',function (e){
            e.preventDefault();
            //get value
            var status = $(this).data('val');
            var id = $(this).data('id');
            var currentStatus =  $(this).parent().prev('button').text();
            currentStatus = currentStatus.trim();
            $(this).parent().prev('button').removeClass('status-'+currentStatus).addClass('status-'+status).text(status);
            //ajax call
            $.ajax({
                'type': 'post',
                'url' : "{{route('tenant.user.dashboard.support.ticket.status.change')}}",
                'data' : {
                    _token : "{{csrf_token()}}",
                    status : status,
                    id : id,
                },
                success: function (data){
                    $(this).parent().prev('button').removeClass(currentStatus).addClass(status).text(status);
                }
            })
        });


    })(jQuery);
</script>
@endsection
