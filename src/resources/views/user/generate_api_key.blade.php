@extends('user.layouts.app')
@section('panel')
    <section class="mt-3 rounded_box">
        <div class="container-fluid p-0 mb-3 pb-2">
            <div class="row d-flex align--center rounded">
                <div class="col-xl-12">
                    <div class="card">
                        <div class="card-header bg--lite--violet">
                            <h6 class="card-title text-center text-light">{{$title}}</h6>
                        </div>
                        <div class="card-body">
                            <div class="row pb-3 g-3">
                                <div class="col-md-8 col-sm-12">
                                    <div class="input-group">
                                        <input type="text" class="form-control" id="apikey" value="{{$user->api_key}}" placeholder="Click on the button to generate a new API key ..." aria-label="Recipient's username" aria-describedby="basic-addon2">
                                        <span class="input-group-text bg--info pointer text-white" onclick="myFunction()" id="basic-addon2"><i class="me-1 las la-copy fs-5"></i> {{translate('Copy')}}</span>
                                    </div>
                                </div>

                                <div class="col-md-4 col-sm-12">
                                    <a href="javascript:void(0)" class="btn bg--success w-100 border-0 rounded text-white p-2" id="keygen"><i class="me-1 las la-key fs-5"></i> {{translate('Generate API Key')}}</a>
                                </div>
                            </div>
                        </div>
                    </div> 
                </div>
            </div>
        </div>
    </section>
@endsection


@push('stylepush')
    <style>
        .wrapper {
            padding-top:60px;
        }
        button.form-control {
            background: #f7f7f7 none repeat scroll 0 0;
            border-color: #ccc;
            box-shadow: 0 1px 0 #ccc;
            color: #555;
            vertical-align: top;
            border-radius: 3px;
            border-style: solid;
            border-width: 1px;
            box-sizing: border-box;
            cursor: pointer;
            display: inline-block;
            font-size: 13px;
            height: 28px;
            line-height: 26px;
            margin: 0;
            padding: 0 10px 1px;
            text-decoration: none;
            white-space: nowrap;
        }

        input.form-control {
            background-color: #fff;
            border: 1px solid #ddd;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.07) inset;
            color: #32373c;
            outline: 0 none;
            transition: border-color 50ms ease-in-out 0s;
            margin: 1px;
            padding: 3px 5px;
            border-radius: 0;
            font-size: 14px;
            font-family: inherit;
            font-weight: inherit;
            box-sizing: border-box;
            color: #444;
            font-family: "Open Sans",sans-serif;
            line-height: 1.4em;
            width: 310px;
        }
    </style>
@endpush


@push('scriptpush')
    <script>
        function generateUUID(){
            var d = new Date().getTime();
            if( window.performance && typeof window.performance.now === "function" ){
                d += performance.now();
            }
            var uuid = 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function(c){
                var r = (d + Math.random()*16)%16 | 0;
                d = Math.floor(d/16);
                return (c=='x' ? r : (r&0x3|0x8)).toString(16);
            });
            return uuid;
        }

        $( '#keygen' ).on('click',function(){
            var api_key_value = generateUUID();
            $( '#apikey' ).val(api_key_value);

            $.ajax({
                type:"POST",
                url:"{{route('user.save.generate.api.key')}}",
                data : {_token : "{{ csrf_token() }}", api_key : api_key_value},
                success:function(response){
                    if(response.error){
                        notify('error', response.error)
                    }else{
                        notify('success',response.message);
                    }
                }
            });


        });

        function myFunction() {
            var copyText = document.getElementById("apikey");
            copyText.select();
            copyText.setSelectionRange(0, 99999)
            document.execCommand("copy");
            notify('success', 'Copied the text : ' + copyText.value);
        }
    </script>
@endpush

