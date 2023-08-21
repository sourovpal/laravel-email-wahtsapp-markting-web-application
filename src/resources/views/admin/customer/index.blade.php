@extends('admin.layouts.app')
@section('panel')
<section class="mt-3">
    <div class="container-fluid p-0">
	    <div class="row">
	    	<div class="col-lg-12">
	            <div class="card mb-4">
	                <div class="card-body">
	                    <form action="{{route('admin.user.search', $scope ?? str_replace('admin.user.','',request()->route()->getName()))}}" method="GET">
	                        <div class="row align-items-center">
	                            <div class="col-lg-4">
	                                <label>{{ translate('By User Or Email') }}</label>
	                                <input type="text" autocomplete="off" name="search" value="" placeholder="@lang('Search with User, Email or To Recipient number')" class="form-control" id="search" value="{{@$search}}">
	                            </div>
	                            <div class="col-lg-4">
	                                <label>{{ translate('By Date')}}</label>
	                                <input type="text" class="form-control datepicker-here" name="date" value="{{@$searchDate}}" data-range="true" data-multiple-dates-separator=" - " data-language="en" data-position="bottom right" autocomplete="off" placeholder="@lang('From Date-To Date')" id="date">
	                            </div>
	                            <div class="col-lg-2">
	                                <button class="btn btn--primary w-100 h-45 mt-4" type="submit">
	                                    <i class="fas fa-search"></i> {{ translate('Search')}}
	                                </button>
	                            </div>

                                <div class="col-lg-2">
                                    <button class="btn btn--info w-100 h-45 mt-4" type="button" data-bs-toggle="modal" data-bs-target="#addUser">
                                        <i class="fas fa-users"></i> {{ translate('Add New')}}
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
		                <table class="m-0 text-center table--light">
		                    <thead>
		                        <tr>
		                            <th>{{ translate('Customer')}}</th>
		                            <th>{{ translate('Email - Phone')}}</th>
		                            <th>{{ translate('Status')}}</th>
		                            <th>{{ translate('Add / Returned Credit')}}</th>
		                            <th>{{ translate('Joined At')}}</th>
		                            <th>{{ translate('Action')}}</th>
		                        </tr>
		                    </thead>
		                    @forelse($customers as $customer)
			                    <tr class="@if($loop->even) table-light @endif">
				                    <td data-label="{{ translate('Customer')}}">
				                    	{{$customer->name ?? 'N/A'}}
				                    </td>
				                    <td data-label="{{ translate('Email')}}">
				                    	{{$customer->email}}<br>
				                    	{{$customer->phone}}
				                    </td>

				                    <td data-label="{{ translate('Status')}}">
				                    	@if($customer->status == 1)
				                    		<span class="badge badge--success">{{ translate('Active')}}</span>
				                    	@else
				                    		<span class="badge badge--danger">{{ translate('Banned')}}</span>
				                    	@endif
				                    </td>

                                    <td data-label="{{ translate('Add / Returned Credit')}}">
                                        <button type="button"
                                            class="badge btn bg--success text-white createdupdate"
                                            data-bs-toggle="modal" data-id="{{$customer->id}}"
                                            data-bs-target="#creditaddreturn">{{translate('Add / Returned')}}
                                        </button>
                                    </td>

				                    <td data-label="{{ translate('Joined At')}}">
				                    	{{diffForHumans($customer->created_at)}}<br>
				                    	{{getDateTime($customer->created_at)}}
				                    </td>

				                    <td data-label={{ translate('Action')}}>
			                    		<a href="{{route('admin.user.details', $customer->id)}}" class="btn--primary text--light brand" data-bs-toggle="tooltip" data-bs-placement="top" title="Details"><i class="las la-desktop"></i></a>
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
						{{$customers->appends(request()->all())->links()}}
					</div>
	            </div>
	        </div>
	    </div>
	</div>
</section>


<div class="modal fade" id="addUser" tabindex="-1" aria-labelledby="addUserLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{route('admin.user.store')}}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="card">
                        <div class="card-header bg--lite--violet">
                            <div class="card-title text-center text--light"> {{ translate('Add New User')}}</div>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label for="name" class="form-label"> {{ translate('Name')}} <sup class="text--danger">*</sup></label>
                                <input type="text" class="form-control" name="name" id="name" placeholder="{{translate('Enter Name')}}" required>
                            </div>

                            <div class="mb-3">
                                <label for="email" class="form-label"> {{ translate('Email-Address')}} <sup class="text--danger">*</sup></label>
                                <input type="email" class="form-control" name="email" id="email" placeholder="{{translate('Enter Email-Address')}}" required>
                            </div>

                            <div class="mb-3">
                                <label for="password" class="form-label"> {{ translate('Password')}} <sup class="text--danger">*</sup></label>
                                <input type="password" class="form-control" name="password" id="password" placeholder="{{translate('Enter Password')}}" required>
                            </div>

                            <div class="mb-3">
                                <label for="password" class="form-label"> {{ translate('Confirm Password')}} <sup class="text--danger">*</sup></label>
                                <input type="password" class="form-control" name="password_confirmation" id="email" placeholder="{{translate('Enter Confirm Password')}}" required>
                            </div>

                        </div>
                    </div>
                </div>

                <div class="modal_button2">
                    <button type="button" class="" data-bs-dismiss="modal"> {{ translate('Cancel')}}</button>
                    <button type="submit" class="bg--success"> {{ translate('Submit')}}</button>
                </div>
            </form>
        </div>
    </div>
</div>


<div class="modal fade" id="creditaddreturn" tabindex="-1" aria-labelledby="addUserCreditLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{route('admin.user.add.return')}}" method="POST">
                @csrf
                <input type="hidden" name="id" value="">
                <div class="modal-body">
                    <div class="card">
                        <div class="card-header bg--lite--violet">
                            <div class="card-title text-center text--light"> {{ translate('Add / Returnted Credit')}}</div>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label for="type" class="form-label"> {{ translate('Type')}} <sup class="text--danger">*</sup></label>
                                <select class="form-control" name="type" id="type" required>
                                    <option value="1">{{translate('Add Credit')}}</option>
                                    <option value="2">{{translate('Returnted Credit')}}</option>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label for="sms_credit" class="form-label"> {{ translate('SMS Credit')}} </label>
                                <input type="text" class="form-control" name="sms_credit" id="sms_credit" placeholder="{{translate('Enter SMS Credit')}}">
                            </div>

                            <div class="mb-3">
                                <label for="email_credit" class="form-label"> {{ translate('Email Credit')}}</label>
                                <input type="text" class="form-control" name="email_credit" id="email_credit" placeholder="{{translate('Enter Email Credit')}}">
                            </div>

                            <div class="mb-3">
                                <label for="whatsapp_credit" class="form-label"> {{ translate('WhatsApp Credit')}}</label>
                                <input type="text" class="form-control" name="whatsapp_credit" id="whatsapp_credit" placeholder="{{translate('Enter WhatsApp Credit')}}">
                            </div>

                        </div>
                    </div>
                </div>

                <div class="modal_button2">
                    <button type="button" class="" data-bs-dismiss="modal"> {{ translate('Cancel')}}</button>
                    <button type="submit" class="bg--success"> {{ translate('Submit')}}</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection


@push('scriptpush')
    <script>
        (function($){
            "use strict";
            $('.createdupdate').on('click', function(){
                var modal = $('#creditaddreturn');
                modal.find('input[name=id]').val($(this).data('id'));
                modal.modal('show');
            });
        })(jQuery);
    </script>
@endpush
