@extends('user.layouts.app')
@section('panel')
    <section class="mt-3 rounded_box">
        <div class="container-fluid p-0 mb-3 pb-2">
            <div class="row d-flex align--center rounded">
                <div class="col-xl-12">
                    <div class="table_heading d-flex align--center justify--between">
                        <nav  aria-label="breadcrumb">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="{{route('user.mail.configuration')}}"> {{ translate('Mail Configuration')}}</a></li>
                                <li class="breadcrumb-item" aria-current="page">{{$mail->name}}</li>
                            </ol>
                        </nav>
                    </div>

                    <div class="card">
                        <div class="card-header bg--lite--violet text-center">
                            <h6 class="text-light">{{$mail->name}}  {{ translate('Mail Configuration Update Form')}}</h6>
                        </div>
                        <div class="card-body">
                            <form action="{{route('user.mail.update', $mail->id)}}" method="POST">
                                @csrf

                                <div class="row">
                                    @foreach($credentials as $key => $parameter)
                                        <div class="mb-3 col-md-6">
                                            <label for="{{$key}}" class="form-label">{{ucwords(str_replace('_', ' ', $key))}} <sup class="text--danger">*</sup></label>
                                            <input type="text" name="{{$key}}" id="{{$key}}" value="{{$parameter}}"  placeholder="Enter {{ucwords(str_replace('_', ' ', $key))}}" class="form-control" required>
                                        </div>
                                    @endforeach
                                </div>

                                <button type="submit" class="btn btn--primary w-100 text-light"> {{ translate('Submit')}}</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
