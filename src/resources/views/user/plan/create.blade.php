@extends('user.layouts.app')
@section('panel')
<section class="mt-3">
	<div class="container">
        <div class="row">
        	@foreach($plans as $plan)
        		<div class="col-md-4 col-sm-6">
	                	<div class="pricingTable {{$plan->recommended_status==1?'blue':'green'}}"> <!--  more color red, for basic do blank -->
	                    <div class="pricingTable-header mb-1">
	                        <h6>{{$plan->recommended_status==1?translate('Recommended'):''}}</h6>
	                        <div class="price-value"> 
	                        	{{$general->currency_symbol}}{{shortAmount($plan->amount)}} 
	                        	<span class="month">{{$plan->duration}} {{ translate('Days')}}</span> 
	                        </div>
	                    </div>
	                    <h3 class="heading">{{ucfirst($plan->name)}}</h3>
	                    <div class="pricing-content">
	                        <ul>
	                            <li><b>{{$plan->credit}}</b> {{ translate('SMS Credit') }}</li>
	                            <li><b>{{$plan->email_credit}}</b> {{ translate('Email Credit') }}</li>
	                            <li><b>{{$plan->whatsapp_credit?? '0'}}</b> {{ translate('WhatsApp Credit') }}</li>
	                            <li>{{ translate('1 Credit for '.$general->sms_word_text_count.' plain word')}}</li>
	                            <li>{{ translate('1 Credit for '.$general->sms_word_unicode_count.' unicode word')}}</li>
	                            <li>{{ translate('1 Credit for '.$general->whatsapp_word_count.' word')}}</li>
	                            <li>{{ translate('1 Credit for per Email')}}</li>
	                        </ul>
	                    </div>
	                    <div class="pricingTable-purchase">
	                        <a href="javascript:void(0)" class="btn bordered radius subscription" data-bs-toggle="modal" data-bs-target="#purchase" data-id="{{$plan->id}}">
			                	@if($subscription)
			                		@if($plan->id == $subscription->plan_id)
										@if(Carbon\Carbon::now()->toDateTimeString() > $subscription->expired_date)
			                            {{ translate("Renew") }}
										@else
			                            {{ translate('Current Plan')}}
										@endif
			                		@else
			                          {{ translate('Upgrade Plan')}}
			                		@endif
			                	@else
			                    {{ translate('Purchase Now')}}
			                	@endif
			            	</a>
	                    </div>
	                </div>
	            </div>
        	@endforeach
        </div>
    </div>
</section>

<div class="modal fade" id="purchase" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
        	<div class="modal-header">
		        <h5 class="modal-title">{{ translate('Payment Method')}}</h5>
		        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
		     </div>
        	<form action="{{route('user.plan.store')}}" method="POST">
        		@csrf
        		<input type="hidden" name="id">
        		<input type="hidden" name="payment_gateway">
	            <div class="modal_body">
	            	<div class="container">
	            		<div class="col-lg-12">
	            			<h6 class="payment-gateway-modal-title">{{ translate('Automatic Payment Method')}}</h6>
	            		</div>
	            		<div class="col-lg-12">
	            			<div class="modal_text2 mt-3">
			                    <div class="mb-3">
									<div class="payment-items">
		            					@foreach($paymentMethods as $paymentMethod)
											@if(strpos($paymentMethod->unique_code, 'MANUAL') === false)
								            <div class="payment-item" data-payment_gateway="{{$paymentMethod->id}}">
								            	<div class="payment-item-img">
								                	<img src="{{showImage(filePath()['payment_method']['path'].'/'.$paymentMethod->image,filePath()['payment_method']['size'])}}" alt="{{$paymentMethod->name}}">
								              	</div>
								              	<h4 class="payment-item-title">
								                	{{$paymentMethod->name}}
								              	</h4>
								              	<div class="payment-overlay">
								                	<button type="submit" class="btn">{{ translate('Process')}}</button>
								              	</div>
								            </div>
								        	@endif
					            		@endforeach
	            					</div>
	            				</div>
	            			</div>
	            		</div>
	            		<div class="col-lg-12">
	            			<h6 class="payment-gateway-modal-title">{{ translate('Manual Payment Method')}}</h6>
	            		</div>
	            		<div class="col-lg-12">
	            			<div class="modal_text2 mt-3">
			                    <div class="mb-3">
									<div class="payment-items">
		            					@foreach($paymentMethods as $paymentMethod)
											@if(strpos($paymentMethod->unique_code, 'MANUAL') !== false)
								            <div class="payment-item" data-payment_gateway="{{$paymentMethod->id}}">
								            	<div class="payment-item-img">
								                	<img src="{{showImage(filePath()['payment_method']['path'].'/'.$paymentMethod->image,filePath()['payment_method']['size'])}}" alt="{{$paymentMethod->name}}">
								              	</div>
								              	<h4 class="payment-item-title">
								                	{{$paymentMethod->name}}
								              	</h4>
								              	<div class="payment-overlay">
								                	<button type="submit" class="btn">{{ translate('Process')}}</button>
								              	</div>
								            </div>
								        	@endif
					            		@endforeach
	            					</div>
	            				</div>
	            			</div>
	            		</div>
	                </div>
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
		$(".subscription").on('click', function(){
			var modal = $('#purchase');
			modal.find('input[name=id]').val($(this).data("id"));
			modal.modal('show');
		});

		$(".payment-item").on('click', function(){
			var modal = $('#purchase');
			modal.find('input[name=payment_gateway]').val($(this).data("payment_gateway"));
		});
	})(jQuery);
</script>
@endpush 