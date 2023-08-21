@extends('user.layouts.app')
@section('panel')
    <section class="mt-3">
        <div class="container-fluid p-0">
            <div class="row">
                <div class="col-lg-12 p-1">
                    <div class="rounded_box">
                        <div class="row align--center px-3">
                            <div class="col-12 col-md-4 col-lg-4 col-xl-5">
                                <h6 class="my-3"> {{ translate('Set Mail Default Options')}}</h6>
                            </div>
                            <div class="col-12 col-md-8 col-lg-8 col-xl-7">
                                <div class="row justify-content-end">
                                    <div class="col-12 col-lg-6 col-xl-6 px-2 py-1 ">
                                        <form action="{{route('user.mail.default.method')}}" method="POST" class="form-inline float-sm-right text-end">
                                            @csrf
                                            <div class="input-group mb-3 w-100">
                                                <select class="form-control" name="default_gateway_id" required="">
                                                    @foreach($mails as $mail)
                                                        <option value="{{$mail->id}}" @if($defaultGateway == $mail->id) selected @endif>{{strtoupper($mail->name)}}</option>
                                                    @endforeach
                                                </select>
                                                <button class="btn--primary input-group-text input-group-text" id="basic-addon2" type="submit"> {{ translate('Email Send Method')}}</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="responsive-table">
                            <table class="m-0 text-center table--light">
                                <thead>
                                <tr>
                                    <th> {{ translate('Name')}}</th>
                                    <th> {{ translate('Action')}}</th>
                                </tr>
                                </thead>
                                @forelse($mails as $mail)
                                    <tr class="@if($loop->even) table-light @endif">
                                        <td data-label=" {{ translate('Name')}}">
                                            {{$mail->name}}
                                            @if($defaultGateway == $mail->id)
                                                <span class="text--success fs-5">
					                    		    <i class="las la-check-double"></i>
					                    	    </span>
                                            @endif
                                        </td>

                                        <td data-label= {{ translate('Action')}}>
                                            @if($mail->name == "SendGrid Api" || $mail->name == "SMTP")
                                                <a class="btn--primary text--light" href="{{route('user.mail.edit', $mail->id)}}"><i class="las la-pen"></i></a>
                                            @else
                                                <span> {{ translate('N/A')}}</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td class="text-muted text-center" colspan="100%"> {{ translate('No Data Found')}}</td>
                                    </tr>
                                @endforelse
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
