@extends('admin.layouts.app')
@section('panel')
<section class="mt-3">
    <div class="container-fluid p-0">
	    <div class="row">
	    	<div class="col-lg-12">
	            <div class="card mb-4">
	                <div class="card-body">
	                    <form action="{{route('admin.report.email.credit.search')}}" method="GET">
	                        <div class="row align-items-center">
	                            <div class="col-lg-5">
	                                <label>{{ translate('By TrxID')}}</label>
	                                <input type="text" autocomplete="off" name="search" value="" placeholder="{{ translate('Search by trxid')}}" class="form-control" id="search" value="{{@$search}}">
	                            </div>
	                            <div class="col-lg-5">
	                                <label>{{ translate('By Date')}}</label>
	                                <input type="text" class="form-control datepicker-here" name="date" value="{{@$searchDate}}" data-range="true" data-multiple-dates-separator=" - " data-language="en" data-position="bottom right" autocomplete="off" placeholder="{{ translate('From Date-To Date')}}" id="date">
	                            </div>
	                            <div class="col-lg-2">
	                                <button class="btn btn--primary w-100 h-45 mt-4" type="submit">
	                                    <i class="fas fa-search"></i> {{ translate('Search')}}
	                                </button>
	                            </div>
	                        </div>
	                    </form>
	                </div>
	            </div>
	        </div>

	 		<div class="col-lg-12">
	            <div class="card mb-4">
	                <div class="responsive-table">
		                <table class="w-100 m-0 text-center table--light">
		                    <thead>
		                        <tr>
		                            <th>{{ translate('Date')}}/th>
		                            <th>{{ translate('User')}}</th>
		                            <th>{{ translate('Trx ID')}}</th>
		                            <th>{{ translate('Credit')}}</th>
		                            <th>{{ translate('Post Credit')}}</th>
		                            <th>{{ translate('Details')}}</th>
		                        </tr>
		                    </thead>
		                    @forelse($emailCreditLogs as $creditLog)
			                    <tr class="@if($loop->even) table-light @endif">
			                    	<td data-label="{{ translate('Date')}}">
			                    		<span class="fw-bold">{{diffForHumans($creditLog->created_at)}}</span><br>
				                    	{{getDateTime($creditLog->created_at)}}
				                    </td>

	                            	<td data-label="{{ translate('User')}}">
			                    		<a href="{{route('admin.user.details', $creditLog->user_id)}}" class="fw-bold text-dark">{{$creditLog->user->email}}</a>
				                    </td>

				                    <td data-label="{{ translate('Trx ID')}}">
				                    	{{$creditLog->trx_number}}
				                    </td>

				                    <td data-label="{{ translate('Credit')}}">
				                    	<span class="@if($creditLog->type == "+") text--success @else text--danger @endif">
				                    		{{$creditLog->type == "+" ? '+' : '-'}} {{$creditLog->credit}}</span> {{ translate('credit')}}
				                    </td>

				                    <td data-label="{{ translate('Post Credit')}}">
				                    	{{$creditLog->post_credit}} {{ translate('credit')}}
				                    </td>

				                    <td data-label="{{ translate('Details')}}">
				                    	{{$creditLog->details}}
				                    </td>
			                    </tr>
			                @empty
			                	<tr>
			                		<td class="text-muted text-center" colspan="100%">{{ translate('No Data Found')}}</td>
			                	</tr>
			                @endforelse
		                </table>
		            </div>
	                <div class="m-3">
	                	{{$emailCreditLogs->appends(request()->all())->links()}}
					</div>
	            </div>
	        </div>
	    </div>
	</div>
</section>
@endsection
