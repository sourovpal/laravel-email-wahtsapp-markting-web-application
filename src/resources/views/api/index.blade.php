@extends($layout)
@section('panel')
@push('stylepush')
<link rel="stylesheet" href="{{asset('assets/dashboard/css/prism.css')}}" />
<link rel="stylesheet" href="{{asset('assets/dashboard/css/code-box-copy.min.css')}}"/>
@endpush

<section class="mt-3">
    <div class="container-fluid p-0 py-4">
	    <div class="row">
	 		<div class="col-lg-12">
	            <div class="card mb-4">
	            	<div class="card-body">
	            		<h6 class="mb-1">{{ translate('Before you get started') }}</h6>
						<div class="lead mb-5">
							A brief overview of the API and its purpose <br>
							<strong>Endpoints:</strong> A list of all the endpoints available in the API, including their URLs and the HTTP methods they support.
							<br>
							<strong>Request and Response:</strong> The expected request format and the format of the response, including examples of how to use the API and the data that it returns.
						</div>
 

						<h6 class="mb-1">{{ translate('Send Email') }}</h6>
						<div class="lead mb-5">
							<div class="accordion" id="emailSend">
							  	<div class="accordion-item mb-3">
								    <h2 class="accordion-header" id="headingOne">
								      	<button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseEmail" aria-expanded="true" aria-controls="collapseEmail">
								        	<span class="badge bg-primary">
								        		POST
								        	</span> &nbsp;
								        	<span>{{route('incoming.email.send')}}</span>
								      </button>
								    </h2>
							    	<div id="collapseEmail" class="accordion-collapse collapse" aria-labelledby="headingOne" data-bs-parent="#emailSend">
							      		<div class="accordion-body">
							        		<strong>This is the first item's accordion body.</strong> It's also worth noting that just about any HTML can go within the <code>.accordion-body</code>, though the transition does limit overflow.
							        		<div class="code-box-copy">
    											<button class="code-box-copy__btn" data-clipboard-target="#code-block-email-send" title="Copy"></button>
							        				<pre>
														<code id="code-block-email-send" class="language-php">
$curl = curl_init();
$postdata = array(
    "contact" => array(
        array(
            "subject" => "demo list info",
            "email" => "receiver1@email.com",
            "message" => "In publishing and graphic design, Lorem ipsum text",
            "sender_name" => "name",
            "reply_to_email" => "demo@gmail.com"
        ),
        array(
            "subject" => "demo list info",
            "email" => "receiver2@email.com",
            "message" => "1",
            "sender_name" => "name",
            "reply_to_email" => "demo@gmail.com"
        )
    )
);

curl_setopt_array($curl, array(
    CURLOPT_URL => '{{route('incoming.email.send')}}',
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => '',
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 0,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => 'POST',
    CURLOPT_POSTFIELDS =>json_encode($postdata),
    CURLOPT_HTTPHEADER => array(
        'Api-key: ###########################,
        'Content-Type: application/json'
    ),
));
$response = curl_exec($curl);
curl_close($curl);
                                                        </code>
														</pre>
													</div>
												</div>
							      			</div>
							    		</div>
							  		</div>
							  	<div class="accordion-item mb-3">
							    	<h2 class="accordion-header" id="headingEmailStatus">
							      		<button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseEmailStatus" aria-expanded="false" aria-controls="collapseEmailStatus">
							        		<span class="badge bg-success">
							        			GET
							        		</span>&nbsp;
							        		<span>{{url('api/get/email/{uid}')}}</span>
							      		</button>
							    	</h2>
							    	<div id="collapseEmailStatus" class="accordion-collapse collapse" aria-labelledby="headingEmailStatus" data-bs-parent="#emailSend">
							      		<div class="accordion-body">
                                            <div class="code-box-copy">
                                                <button class="code-box-copy__btn" data-clipboard-target="#code-block-email-send" title="Copy"></button>
                                                <pre>
														<code id="code-block-email-send" class="language-php">
$curl = curl_init();

curl_setopt_array($curl, array(
    CURLOPT_URL => '{{url('api/get/email/{uid}')}}',
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => '',
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 0,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => 'GET',
    CURLOPT_HTTPHEADER => array(
        'Api-key: ###########################,
    ),
));

$response = curl_exec($curl);
curl_close($curl);
                                                        </code>
														</pre>
                                            </div>
							      		</div>
							    	</div>
							  	</div>
							</div>

                        <h6 class="mb-1">{{ translate('Send SMS') }}</h6>
                        <div class="lead mb-5">
                            <div class="accordion" id="smsSend">
                                <div class="accordion-item mb-3">
                                    <h2 class="accordion-header" id="headingOne">
                                        <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseSms" aria-expanded="false" aria-controls="collapseEmail">
								        	<span class="badge bg-primary">
								        		POST
								        	</span> &nbsp;
                                            <span>{{route('incoming.sms.send')}}</span>
                                        </button>
                                    </h2>
                                    <div id="collapseSms" class="accordion-collapse collapse" aria-labelledby="headingOne" data-bs-parent="#smsSend">
                                        <div class="accordion-body">
                                            <strong>This is the first item's accordion body.</strong> It's also worth noting that just about any HTML can go within the <code>.accordion-body</code>, though the transition does limit overflow.
                                            <div class="code-box-copy">
                                                <button class="code-box-copy__btn" data-clipboard-target="#code-block-email-send" title="Copy"></button>
                                                <pre>
														<code id="code-block-email-send" class="language-php">
$curl = curl_init();
$postdata = array(
    "contact" => array(
        array(
            "number" => "11254352345",
            "body" => "In publishing and graphic design, Lorem ipsum is a",
            "sms_type" => "plain"
        ),
        array(
            "number" => "32234213423",
            "body" => "In publishing and graphic design, Lorem ipsum is a",
            "sms_type" => "unicode"
        )
    )
);

curl_setopt_array($curl, array(
    CURLOPT_URL => '{{route('incoming.sms.send')}}',
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => '',
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 0,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => 'POST',
    CURLOPT_POSTFIELDS =>json_encode($postdata),
    CURLOPT_HTTPHEADER => array(
        'Api-key: ###########################,
        'Content-Type: application/json',
    ),
));

$response = curl_exec($curl);
curl_close($curl);

                                                        </code>
														</pre>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="accordion-item mb-3">
                                <h2 class="accordion-header" id="headingEmailStatus">
                                    <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseSmsStatus" aria-expanded="false" aria-controls="collapseEmailStatus">
							        		<span class="badge bg-success">
							        			GET
							        		</span>&nbsp;
                                        <span>{{url('api/get/sms/{uid}')}}</span>
                                    </button>
                                </h2>
                                <div id="collapseSmsStatus" class="accordion-collapse collapse" aria-labelledby="headingEmailStatus" data-bs-parent="#emailSend">
                                    <div class="accordion-body">
                                        <strong>This is the third item's accordion body.</strong> It is hidden by default, until the collapse plugin adds the appropriate classes that we use to style each element.
                                        <div class="code-box-copy">
                                            <button class="code-box-copy__btn" data-clipboard-target="#code-block-email-send" title="Copy"></button>
                                            <pre>
														<code id="code-block-email-send" class="language-php">
$curl = curl_init();

curl_setopt_array($curl, array(
    CURLOPT_URL => '{{url('api/get/sms/{uid}')}}',
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => '',
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 0,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => 'GET',
    CURLOPT_HTTPHEADER => array(
        'Api-key: ###########################,
    ),
));

$response = curl_exec($curl);
curl_close($curl);

                                                        </code>
														</pre>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>


                        <h6 class="mb-1">{{ translate('Send Whatsapp') }}</h6>
                        <div class="lead mb-5">
                            <div class="accordion" id="whatsappSend">
                                <div class="accordion-item mb-3">
                                    <h2 class="accordion-header" id="headingOne">
                                        <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseWhatsapp" aria-expanded="false" aria-controls="collapseEmail">
                                                <span class="badge bg-primary">
                                                    POST
                                                </span> &nbsp;
                                            <span>{{route('incoming.whatsapp.send')}}</span>
                                        </button>
                                    </h2>
                                    <div id="collapseWhatsapp" class="accordion-collapse collapse" aria-labelledby="headingOne" data-bs-parent="#whatsappSend">
                                        <div class="accordion-body">
                                            <strong>This is the first item's accordion body.</strong> It's also worth noting that just about any HTML can go within the <code>.accordion-body</code>, though the transition does limit overflow.
                                            <div class="code-box-copy">
                                                <button class="code-box-copy__btn" data-clipboard-target="#code-block-email-send" title="Copy"></button>
                                                <pre>
                                                            <code id="code-block-email-send" class="language-php">
$curl = curl_init();
$postdata = array(
    "contact" => array(
        array(
            "number" => "880123456789",
            "message" => "In publishing and graphic design, Lorem ipsum"
        ),
        array(
            "number" => "880123456789",
            "message" => "In publishing and graphic design, Lorem ipsum",
            "media" => "image",
            "url" => "https://example.com/image.jpeg"
        ),
        array(
            "number" => "880123456799",
            "message" => "In publishing an audio file, Lorem ipsum",
            "media" => "audio",
            "url" => "https://example.com/audio.mp3"
        ),
        array(
            "number" => "880123456799",
            "message" => "In publishing a video file, Lorem ipsum",
            "media" => "video",
            "url" => "https://example.com/video.mp4"
        ),
        array(
            "number" => "880123456799",
            "message" => "In publishing a document file, Lorem ipsum",
            "media" => "document",
            "url" => "https://example.com/document.pdf"
        )
    )
);

curl_setopt_array($curl, array(
    CURLOPT_URL => '{{route('incoming.whatsapp.send')}}',
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => '',
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 0,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => 'POST',
    CURLOPT_POSTFIELDS =>json_encode($postdata),
    CURLOPT_HTTPHEADER => array(
        'Api-key: ###########################,
        'Content-Type: application/json',
    ),
));

$response = curl_exec($curl);
curl_close($curl);
                                                            </code>
                                                            </pre>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="accordion-item mb-3">
                                <h2 class="accordion-header" id="headingEmailStatus">
                                    <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseWhatsappStatus" aria-expanded="false" aria-controls="collapseEmailStatus">
                                                <span class="badge bg-success">
                                                    GET
                                                </span>&nbsp;
                                        <span>{{url('api/get/whatsapp/{uid}')}}</span>
                                    </button>
                                </h2>
                                <div id="collapseWhatsappStatus" class="accordion-collapse collapse" aria-labelledby="headingEmailStatus" data-bs-parent="#emailSend">
                                    <div class="accordion-body">
                                        <div class="code-box-copy">
                                            <button class="code-box-copy__btn" data-clipboard-target="#code-block-email-send" title="Copy"></button>
                                            <pre>
														<code id="code-block-email-send" class="language-php">
$curl = curl_init();

curl_setopt_array($curl, array(
    CURLOPT_URL => '{{url('api/get/whatsapp/{uid}')}}',
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => '',
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 0,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => 'GET',
    CURLOPT_HTTPHEADER => array(
        'Api-key: ###########################,
    ),
));

$response = curl_exec($curl);
curl_close($curl);

                                                        </code>
														</pre>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@push('scriptpush')
<script src="{{asset('assets/dashboard/js/prism.js')}}"></script>
<script src="{{asset('assets/dashboard/js/clipboard.min.js')}}"></script>
<script src="{{asset('assets/dashboard/js/code-box-copy.min.js')}}"></script>
@endpush

@push('scriptpush')
<script>
"use strict";
(function($) {
    $('.code-box-copy').codeBoxCopy();
})(jQuery);
</script>
@endpush

@push('scriptpush')
<script>
"use strict";
(function($) {
    $('.code-box-copy').codeBoxCopy({
        tooltipText: 'Copied',
        tooltipShowTime: 1000,
        tooltipFadeInTime: 300,
        tooltipFadeOutTime: 300
    });
})(jQuery);
</script>
@endpush

