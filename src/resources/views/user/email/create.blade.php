@extends('user.layouts.app')
@section('panel')
<style type="text/css">
	.ck-editor__editable_inline {
    min-height: 100px;
}
</style>
<section class="mt-3 rounded_box">
	<div class="container-fluid p-0 mb-3 pb-2">
		<div class="row d-flex align--center rounded">
			<div class="col-xl-12">
				<div class="col-xl">
					<form action="{{route('user.manage.email.store')}}" method="POST" enctype="multipart/form-data">
						@csrf
					    <div class="card mb-2">
						    <h6 class="card-header">{{ translate('Recipient Set In Different Ways')}}</h6>
						    <div class="card-body">
					    		<div class="row">
					          		<div class="col-md-4 mb-2">
					            		<label class="form-label">
					            			{{ translate('Single Input') }}
					            		</label>
					            		<div class="input-group input-group-merge">
					              			<select class="form-control emailcollect" name="email[]" id="email" multiple>
											
											</select>
					            		</div>
					            		<div class="form-text">
					            			{{ translate('Put single or search from save contact')}}
										</div>
					          		</div>
					          		<div class="col-md-4 mb-2">
					            		<label class="form-label">
					            			{{ translate('From Group')}}
					            		</label>
					            		<div class="input-group input-group-merge">
								            <select class="form-control keywords" name="email_group_id[]" id="group" multiple="multiple">
												<option value="" disabled="">{{ translate('Select One')}}</option>
												@foreach($emailGroups as $group)
													<option @if (old("email_group_id")){{ (in_array($group->id, old("email_group_id")) ? "selected":"") }}@endif value="{{$group->id}}">{{$group->name}}</option>
												@endforeach
											</select>
					            		</div>
					            		<div class="form-text">
					            			{{ translate('Can be select single or multiple group')}}
										</div>
					          		</div>
					          		<div class="col-md-4 mb-2">
					            		<label class="form-label">
					            			{{ translate('Import File')}}
					            		</label>
					            		<div class="input-group input-group-merge">
					              			<input class="form-control" type="file" name="file" id="file">
					            		</div>
					            		<div class="form-text">
					            			{{ translate('Download Sample: ')}} 
											<a href="{{route('demo.email.file.downlode', 'csv')}}"><i class="fa fa-download" aria-hidden="true"></i> {{ translate('csv')}}, </a>
											<a href="{{route('demo.email.file.downlode', 'xlsx')}}"><i class="fa fa-download" aria-hidden="true"></i> {{ translate('xlsx')}}</a>
										</div>
					          		</div>
					    		</div>
					      	</div>
					    </div>

					    <div class="card mb-2">
						    <h6 class="card-header">{{ translate('Email Header Information')}}</h6>
						    <div class="card-body" id="optional_info">
						    	<div class="row">
						    		<div class="col-md-4 mb-2">
					            		<label class="form-label">
					            			{{ translate('Subject')}} <sup class="text-danger">*</sup>
					            		</label>
					            		<div class="input-group input-group-merge">
					              			<input type="text"  value="{{old("subject")}}" name="subject" id="subject" class="form-control" placeholder="{{ translate('Write email subject here')}}">
					            		</div>
					          		</div>
						    		<div class="col-md-4 mb-2">
										<label class="form-label">
											{{ translate('Send From')}}
										</label>
										<div class="input-group input-group-merge">
												<input class="form-control" value="{{old("from_name")}}" placeholder="{{ translate('Sender Name (Optional)')}}" type="text" name="from_name" id="from_name">
										</div>
									</div>
									<div class="col-md-4 mb-2">
										<label class="form-label">
											{{ translate('Reply To Email')}}
										</label>
										<div class="input-group input-group-merge">
												<input class="form-control" value="{{old("reply_to_email")}}" type="email" placeholder="{{ translate('Reply To Email (Optional)')}}" name="reply_to_email" id="reply_to_email">
										</div>
									</div> 
						    	</div>
						    </div>
						</div>

					    <div class="card mb-2">
						    <h6 class="card-header">{{ translate('Email Body')}}</h6>
						    <div class="card-body">
				          		<div class="row">
					          		<div class="md-12">
					            		<label class="form-label">
					            			{{ translate('Message Body')}} <sup class="text-danger">*</sup>
					            		</label>
					            		<div class="input-group">
					            			<textarea class="form-control" name="message" id="message" rows="2">{{old("message")}}</textarea>
					            		</div>
					          		</div>
				          		</div>
					      	</div>
					    </div>

					    <div class="card mb-2">
						    <h6 class="card-header">{{ translate('Sending Options')}}</h6>
						    <div class="card-body">
				          		<div class="row">
				          			<div class="col-md-6 mb-4">
					            		<label for="schedule" class="form-label">{{ translate('Send Email')}} <sup class="text-danger">*</sup></label>
										<div>
											<div class="form-check form-check-inline">
												<input {{old("schedule") ==  '1'? "checked" :""}} class="form-check-input" type="radio" name="schedule" id="schedule" value="1" checked="">
												<label class="form-check-label" for="schedule">{{ translate('Now')}}</label>
											</div>

											<div class="form-check form-check-inline">
												<input  {{old("schedule") ==  '2'? "checked" :""}}  class="form-check-input" type="radio" name="schedule" id="schedule2" value="2">
												<label class="form-check-label" for="schedule2">{{ translate('Later')}}</label>
											</div>
										</div>
					          		</div>
					          		<div class="col-md-6 scheduledate"></div>
				          		</div>
				          	</div>
				          </div>
					    <button type="submit" class="btn btn-primary me-sm-3 me-1">Submit</button>
				    </form>
				</div>
			</div>
		</div>
	</div>
</section>
@endsection


@push('scriptpush')
<script>
	(function($){
	
		"use strict";
		$('.keywords').select2({
			tags: true,
			tokenSeparators: [',']
		});

		selectSearch("{{route('email.select2')}}")
		function selectSearch(route){
			$(`.emailcollect`).select2({
            allowClear: false,
            tags: true,
            tokenSeparators: [' '],
            placeholder: '',
            ajax: {
                url: route,
                dataType: 'json',
                delay: 250,
                data: function(params) {
                    return {
                        term: params.term || '',
                        page: params.page || 1
                    }
                },
                cache: true
            }
          }); 
		}

 

		$('input[type=radio][name=schedule]').on('change', function(){
	        if(this.value == 2){
	        	var html = `
	        		<label for="shedule_date" class="form-label">{{ translate('Schedule Date & Time')}}<sup class="text-danger">*</sup></label>
					<input type="datetime-local" name="shedule_date" id="shedule_date" class="form-control" required="">`;
	        	$('.scheduledate').append(html);
	        }else{
	        	$('.scheduledate').empty();
	        }
	    });
	    $(document).ready(function() { 
		    CKEDITOR.ClassicEditor.create(document.getElementById("message"), {
		        placeholder: document.getElementById("message").getAttribute("placeholder"),
		        toolbar: {
		          items: [
		            'heading',
		            'fontSize', 'fontFamily', 'fontColor', 'fontBackgroundColor', 'highlight', '|',
		            'alignment', '|',
		            'bold', 'italic', 'strikethrough', 'underline', 'subscript', 'superscript', 'removeFormat', 'findAndReplace', '-',
		            'bulletedList', 'numberedList', '|',
		            'outdent', 'indent', '|',
		            'undo', 'redo',
		            'link', 'insertImage', 'blockQuote', 'insertTable', 'mediaEmbed', '|',
		            'horizontalLine', 'pageBreak', '|',
		            'sourceEditing'
		          ],
		          shouldNotGroupWhenFull: true
		        },
		        list: {
		          properties: {
		            styles: true,
		            startIndex: true,
		            reversed: true
		          }
		        },
		        heading: {
		          options: [
		            { model: 'paragraph', title: 'Paragraph', class: 'ck-heading_paragraph' },
		            { model: 'heading1', view: 'h1', title: 'Heading 1', class: 'ck-heading_heading1' },
		            { model: 'heading2', view: 'h2', title: 'Heading 2', class: 'ck-heading_heading2' },
		            { model: 'heading3', view: 'h3', title: 'Heading 3', class: 'ck-heading_heading3' },
		            { model: 'heading4', view: 'h4', title: 'Heading 4', class: 'ck-heading_heading4' },
		            { model: 'heading5', view: 'h5', title: 'Heading 5', class: 'ck-heading_heading5' },
		            { model: 'heading6', view: 'h6', title: 'Heading 6', class: 'ck-heading_heading6' }
		          ]
		        },
		        fontFamily: {
		          options: [
		            'default',
		            'Arial, Helvetica, sans-serif',
		            'Courier New, Courier, monospace',
		            'Georgia, serif',
		            'Lucida Sans Unicode, Lucida Grande, sans-serif',
		            'Tahoma, Geneva, sans-serif',
		            'Times New Roman, Times, serif',
		            'Trebuchet MS, Helvetica, sans-serif',
		            'Verdana, Geneva, sans-serif'
		          ],
		          supportAllValues: true
		        },
		        fontSize: {
		          options: [10, 12, 14, 'default', 18, 20, 22],
		          supportAllValues: true
		        },
		        htmlSupport: {
		          allow: [
		            {
		              name: /.*/,
		              attributes: true,
		              classes: true,
		              styles: true
		            }
		          ]
		        },
		        htmlEmbed: {
		          showPreviews: true
		        },
		        link: {
		          decorators: {
		            addTargetToExternalLinks: true,
		            defaultProtocol: 'https://',
		            toggleDownloadable: {
		              mode: 'manual',
		              label: 'Downloadable',
		              attributes: {
		                download: 'file'
		              }
		            }
		          }
		        },
		        mention: {
		          feeds: [
		            {
		              marker: '@',
		              feed: [
		                '@apple', '@bears', '@brownie', '@cake', '@cake', '@candy', '@canes', '@chocolate', '@cookie', '@cotton', '@cream',
		                '@cupcake', '@danish', '@donut', '@dragée', '@fruitcake', '@gingerbread', '@gummi', '@ice', '@jelly-o',
		                '@liquorice', '@macaroon', '@marzipan', '@oat', '@pie', '@plum', '@pudding', '@sesame', '@snaps', '@soufflé',
		                '@sugar', '@sweet', '@topping', '@wafer'
		              ],
		              minimumCharacters: 1
		            }
		          ]
		        },
		        removePlugins: [
		          'CKBox',
		          'CKFinder',
		          'EasyImage',
		          'RealTimeCollaborativeComments',
		          'RealTimeCollaborativeTrackChanges',
		          'RealTimeCollaborativeRevisionHistory',
		          'PresenceList',
		          'Comments',
		          'TrackChanges',
		          'TrackChangesData',
		          'RevisionHistory',
		          'Pagination',
		          'WProofreader',
		          'MathType'
		        ]
		      });
	    });
	})(jQuery);
</script>
@endpush

