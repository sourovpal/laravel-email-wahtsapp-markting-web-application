<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta http-equiv="Content-Security-Policy" content="upgrade-insecure-requests" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{@$general->site_name}} - {{@$title}}</title>
    <meta name="csrf-token" content="{{csrf_token()}}" />
    @php
      $fav_icon = $general->favicon ?  $general->favicon : "site_favicon.png"
    @endphp
    <link rel="shortcut icon" href="{{showImage(filePath()['site_logo']['path'].'/'.$fav_icon)}}" type="image/x-icon">    
    <link rel="stylesheet" href="{{asset('assets/global/css/bootstrap.min.css')}}">
    <link rel="stylesheet" href="{{asset('assets/global/css/all.min.css')}}">
    <link rel="stylesheet" href="{{asset('assets/global/css/line-awesome.min.css')}}">
    <link rel="stylesheet" href="{{asset('assets/dashboard/css/select2.min.css')}}">
    <link rel="stylesheet" href="{{asset('assets/global/css/toastr.css')}}">
    <link rel="stylesheet" href="{{asset('assets/dashboard/css/apexcharts.css')}}">
    <link rel="stylesheet" href="{{asset('assets/dashboard/css/datepicker/datepicker.min.css')}}">
    <link rel="stylesheet" href="{{asset('assets/dashboard/css/style.css')}}">
    <link rel="stylesheet" href="{{asset('assets/dashboard/css/responsive.css')}}">
    <link rel="stylesheet" href="{{asset('assets/dashboard/css/summernote-lite.min.css')}}">
    <link rel="stylesheet" href="{{asset('assets/dashboard/flag-icons/flag-icons.css')}}">
    @stack('style-include')
    @stack('stylepush')
</head>
<body>
    @yield('content')
    <script src="{{asset('assets/global/js/jquery-3.6.0.min.js')}}"></script>
    <script src="{{asset('assets/global/js/bootstrap.bundle.min.js')}}"></script>
    <script src="{{asset('assets/global/js/all.min.js')}}"></script>
    <script src="{{asset('assets/dashboard/js/select2.min.js')}}"></script>
    <script src="{{asset('assets/global/js/toastr.js')}}"></script>
    <script src="{{asset('assets/dashboard/js/chart.min.js')}}"></script>
    <script src="{{asset('assets/dashboard/js/apexcharts.js')}}"></script>
    <script src="{{asset('assets/dashboard/js/ckd.js')}}"></script>
    <script src="{{asset('assets/dashboard/js/datepicker/datepicker.min.js')}}"></script>
    <script src="{{asset('assets/dashboard/js/datepicker/datepicker.en.js')}}"></script>
    <script src="{{asset('assets/dashboard/js/script.js')}}"></script>
    <script src="{{asset('assets/dashboard/js/summernote-lite.min.js')}}"></script>
    @include('partials.notify')
    @stack('script-include')
    @stack('scriptpush')

    <script type="text/javascript">
        'use strict';
        function changeLang(val){
            window.location.href = "{{route('login')}}/language/change/"+val;
        }
        $(".active").focus();

        var checkboxes = document.querySelectorAll(".form-check-input");


        if (checkboxes) {  
          var statusUpdateBtn = document.querySelector(".statusUpdateBtn");

            for (var i = 0; i < checkboxes.length; i++) {
                console.log(checkboxes.length)
                checkboxes[i].addEventListener("click", function () {
                    var checked = false;
                      for (var j = 0; j < checkboxes.length; j++) {
                        if (checkboxes[j].checked) {
                          checked = true;
                          break;
                        }
                      }
                    if (checked) {
                        statusUpdateBtn.classList.remove('d-none'); 
                    } else { 
                        statusUpdateBtn.classList.add('d-none');
                    }
                });
            }
        }
    </script>
</body>
</html>
