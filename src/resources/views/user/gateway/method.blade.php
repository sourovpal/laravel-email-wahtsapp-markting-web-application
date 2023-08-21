@extends('user.layouts.app')
@section('panel')
    <section class="mt-3">
        <div class="container-fluid">
            <div class="row align--center rounded">
                <div class="col-md-4 rounded_box">
                    <div class="card">
                        <div class="card-header bg--lite--violet">
                            <h6 class="card-title text-center text-light">{{translate('Select Sending Method')}}</h6>
                        </div> 
                    </div> 
                    <div class="card-body">
                        <form method="post" action="{{route('user.default.sms.gateway')}}"  >
                            @csrf
                            <div class="row">
                                <div class="mb-3">
                                  <label for="sms_gateway" class="form-label">{{translate('Send SMS By')}} <sup class="text--danger">*</sup></label>
                                    <select class="form-control" id="sms_gateway" name="sms_gateway" required>
                                        <option value="1" @if(auth()->user()->sms_gateway == 1) selected @endif>{{translate('API Gateway')}}</option>
                                        <option value="2" @if(auth()->user()->sms_gateway == 2) selected @endif>{{translate('Android Gateway')}}</option>
                                    </select>
                                </div>  
                                <div>
                                    <button type="submit" class="btn btn--primary w-100 text-light">{{ translate('Submit')}}</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection 

