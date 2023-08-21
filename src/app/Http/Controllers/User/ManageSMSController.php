<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Service\SmsService;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use App\Models\Contact;
use App\Models\SMSlog;
use App\Models\GeneralSetting;
use App\Models\CreditLog;
use App\Models\SmsGateway;
use Carbon\Carbon;
use Shuchkin\SimpleXLSX;
use App\Jobs\ProcessSms;
use App\Service\FileProcessService;

class ManageSMSController extends Controller
{

    public $smsService ;
    public function __construct(SmsService $smsService)
    {
        $this->smsService = $smsService;
    }
    public function create()
    {
    	$title = "Compose SMS";
    	$user = Auth::user();
    	$groups = $user->group()->get();
    	$templates = $user->template()->get();
    	return view('user.sms.create', compact('title', 'groups', 'templates'));
    }

    public function index()
    {
    	$title = "SMS History";
        $user = Auth::user();
        $smslogs = SMSlog::where('user_id', $user->id)->orderBy('id', 'DESC')->with('smsGateway', 'androidGateway')->paginate(paginateNumber());
    	return view('user.sms.index', compact('title', 'smslogs'));
    }


    public function pending()
    {
        $title = "Pending SMS History";
        $user = Auth::user();
        $smslogs = SMSlog::where('user_id', $user->id)->orderBy('id', 'DESC')->where('status', 1)->with('smsGateway','androidGateway')->paginate(paginateNumber());
        return view('user.sms.index', compact('title', 'smslogs'));
    }


    public function delivered()
    {
        $title = "Delivered SMS History";
        $user = Auth::user();
        $smslogs = SMSlog::where('user_id', $user->id)->orderBy('id', 'DESC')->where('status', 4)->with('smsGateway','androidGateway')->paginate(paginateNumber());
        return view('user.sms.index', compact('title', 'smslogs'));
    }

    public function failed()
    {
        $title = "Failed SMS History";
        $user = Auth::user();
        $smslogs = SMSlog::where('user_id', $user->id)->orderBy('id', 'DESC')->where('status', 3)->with('smsGateway','androidGateway')->paginate(paginateNumber());
        return view('user.sms.index', compact('title', 'smslogs'));
    }

    public function scheduled()
    {
    	$title = "Scheduled SMS History";
    	$user = Auth::user();
        $smslogs = SMSlog::where('user_id', $user->id)->orderBy('id', 'DESC')->where('status', 2)->with('smsGateway','androidGateway')->paginate(paginateNumber());
        return view('user.sms.index', compact('title', 'smslogs'));
    }

    public function processing()
    {
        $title = "Processing SMS History";
        $user = Auth::user();
        $smslogs = SMSlog::where('user_id', $user->id)->orderBy('id', 'DESC')->where('status', 5)->with('smsGateway','androidGateway')->paginate(paginateNumber());
        return view('user.sms.index', compact('title', 'smslogs'));
    }


    public function smsStatusUpdate(Request $request)
    {
        $request->validate([
            'status' => 'required|in:1,3,4',
        ]);
        if(is_null($request->input('smslogid'))){
            $notify[] = ['error', 'Something is wrong'];
            return back()->withNotify($notify);
        }

        $general = GeneralSetting::first();
        $smsGateway = SmsGateway::where('id', Arr::get((array)auth()->user()->gateways_credentials, 'sms.default_gateway_id', 1))->first();

        if(!$smsGateway){
            $notify[] = ['error', 'Invalid Sms Gateway'];
            return back()->withNotify($notify);
        }

        if($request->input('smslogid') !== null){
            $smsLogIds = array_filter(explode(",",$request->input('smslogid')));
            if(!empty($smsLogIds)){
                $this->smsService->smsLogStatusUpdateByUid((int) $request->status, (array) $smsLogIds, $general, $smsGateway);
            }
        }
        $notify[] = ['success', 'SMS status has been updated'];
        return back()->withNotify($notify);
    }

    public function store(Request $request)
    {
        session()->put('user_sms_message',$request->message?  $request->message :"");
        $user = Auth::user();
        $request->validate([
            'message' => 'required',
            'smsType' => 'required|in:plain,unicode',
            'schedule' => 'required|in:1,2',
            'shedule_date' => 'required_if:schedule,2',
            'group_id' => 'nullable|array|min:1',
            'group_id.*' => 'nullable|exists:groups,id,user_id,'.$user->id,
        ]);

        if(!$request->number && !$request->group_id && !$request->file){
            $notify[] = ['error', 'Invalid number collect format'];
            return back()->withNotify($notify);
        }
        $numberGroupName  = [];
        $allContactNumber  = [];
        if($request->number){
            $contactNumber = preg_replace('/[ ,]+/', ',', trim($request->number));
            $recipientNumber  = explode(",",$contactNumber);
            array_push($allContactNumber, $recipientNumber);
        }

        if($request->group_id){
            $groupNumber = Contact::where('user_id', $user->id)->whereIn('group_id', $request->group_id)->pluck('contact_no')->toArray();
            $numberGroupName = Contact::where('user_id', $user->id)->whereIn('group_id', $request->group_id)->pluck('name','contact_no')->toArray();
            array_push($allContactNumber, $groupNumber);
        }
        if($request->file){
            $extension = strtolower($request->file->getClientOriginalExtension());
            $service = new FileProcessService();
            if(!in_array($extension, ['csv','xlsx'])){
                $notify[] = ['error', 'Invalid file extension'];
                return back()->withNotify($notify);
            }
          
            if($extension == "csv"){
                $response =  $service->processCsv($request->file);
                array_push($allContactNumber,array_keys($response[0]));
                $numberGroupName = $numberGroupName +  $response[0];
            }

            if($extension == "xlsx"){
                $response =  $service->processExel($request->file);
                array_push($allContactNumber,array_keys($response[0]));
                $numberGroupName = $numberGroupName +  $response[0];
            }
        }
        $general = GeneralSetting::first();
        $wordLenght = $request->smsType == "plain" ? $general->sms_word_text_count : $general->sms_word_unicode_count;

        $contactNewArray = [];
        foreach($allContactNumber as $childArray){
            foreach($childArray as $value){
                $contactNewArray[] = $value;
            }
        }
        $contactNewArray = array_unique($contactNewArray);
        $messages = str_split($request->message,$wordLenght);
        $totalMessage = count($messages);

        $totalNumber = count($contactNewArray);
        $totalCredit = $totalNumber * $totalMessage;

        if($totalCredit > $user->credit){
            $notify[] = ['error', 'You do not have a sufficient credit for send message'];
            return back()->withNotify($notify);
        }

        $user->credit -=  $totalCredit;
        $user->save();

        $creditInfo = new CreditLog();
        $creditInfo->user_id = $user->id;
        $creditInfo->credit_type = "-";
        $creditInfo->credit = $totalCredit;
        $creditInfo->trx_number = trxNumber();
        $creditInfo->post_credit =  $user->credit;
        $creditInfo->details = $totalCredit." credits were cut for " .$totalNumber . " number send message";
        $creditInfo->save();

        $smsGatewayId = Arr::get((array)$user->gateways_credentials, 'sms.default_gateway_id', 1);
        $smsGateway = SmsGateway::where('id', $smsGatewayId)->first();

        if(!$smsGateway){
            $notify[] = ['error', 'Invalid Sms Gateway'];
            return back()->withNotify($notify);
        }
        $setTimeInDelay = 0;
        if($request->schedule == 2){
            $setTimeInDelay = $request->shedule_date;
        }else{
            $setTimeInDelay = Carbon::now();
        }
        foreach ($contactNewArray as $key => $value) {
            
            if(filter_var($value, FILTER_SANITIZE_NUMBER_INT)){

                $contact =  preg_replace('/[^0-9]/', '', trim(str_replace('+', '', $value)));
                $log = new SMSlog();
                if(auth()->user()->sms_gateway == 1){
                    $log->api_gateway_id = $smsGateway->id;
                }
                $log->sms_type = $request->smsType == "plain" ? 1 : 2;
                $log->user_id = $user->id;
                $log->word_length = $wordLenght;
                $log->to = $contact;
                $log->initiated_time = $request->schedule == 1 ? Carbon::now() : $request->shedule_date;
                if(array_key_exists($value,$numberGroupName)){
                    $finalContent = str_replace('{{name}}', $numberGroupName ? $numberGroupName[$value]:$value, offensiveMsgBlock($request->message));
                }
                else{
                    $finalContent = str_replace('{{name}}',$value, offensiveMsgBlock($request->message));
                }
                $log->message = $finalContent;
                $log->status = $request->schedule == 2 ? 2 : 1;
                $log->schedule_status = $request->schedule;
                $log->save();
    
                if(auth()->user()->sms_gateway == 1 && $log->status == 1){
    
                    $credential = config('setting.gateway_credentials.sms.'.$smsGateway->gateway_code);
    
                    if(!is_null($user->gateways_credentials)){
                        $credential = Arr::get($user->gateways_credentials, 'sms.' . $smsGateway->gateway_code, $credential);
                    }
    
                    if(count($contactNewArray) == 1 && $request->schedule==1){
                        ProcessSms::dispatchNow($value, $request->smsType, $finalContent,(object)$credential, $smsGateway->gateway_code, $log->id);
                    }else{
                        $smsType = $log->sms_type == 1 ? 'plain' : 'unicode';
                        ProcessSms::dispatch($value,$smsType,$finalContent,$credential,(object)$smsGateway->gateway_code, $log->id)->delay(Carbon::parse($setTimeInDelay));
                    }
                }
            }
  
        }
        $notify[] = ['success', 'New SMS request sent, please see in the SMS history for final status'];
        session()->forget('user_sms_message');
        return back()->withNotify($notify);
    }


    public function search(Request $request, $scope)
    {
        $title = "SMS History";
        $search = $request->search;
        $searchDate = $request->date;
        $user = Auth::user();


        if ($search!="") {
            $smslogs = SMSlog::where('user_id', $user->id)->where('to','like',"%$search%");
        }
        if ($searchDate!="") {
            $searchDate_array = explode('-',$request->date);
            $firstDate = $searchDate_array[0];
            $lastDate = null;
            if (count($searchDate_array)>1) {
                $lastDate = $searchDate_array[1];
            }
            $matchDate = "/\d{2}\/\d{2}\/\d{4}/";
            if ($firstDate && !preg_match($matchDate,$firstDate)) {
                $notify[] = ['error','Invalid order search date format'];
                return back()->withNotify($notify);
            }
            if ($lastDate && !preg_match($matchDate,$lastDate)) {
                $notify[] = ['error','Invalid order search date format'];
                return back()->withNotify($notify);
            }
            if ($firstDate) {
                $smslogs = SMSlog::where('user_id', $user->id)->whereDate('created_at',Carbon::parse($firstDate));
            }
            if ($lastDate){
                $smslogs = SMSlog::where('user_id', $user->id)->whereDate('created_at','>=',Carbon::parse($firstDate))->whereDate('created_at','<=',Carbon::parse($lastDate));
            }

        }
        if ($search=="" && $searchDate==""){
                $notify[] = ['error','Please give any search filter data'];
                return back()->withNotify($notify);
        }
        if($scope == 'pending') {
            $smslogs = $smslogs->where('status',SMSlog::PENDING);
        }elseif($scope == 'delivered'){
            $smslogs = $smslogs->where('status',SMSlog::SUCCESS);
        }elseif($scope == 'schedule'){
            $smslogs = $smslogs->where('status',SMSlog::SCHEDULE);
        }elseif($scope == 'failed'){
            $smslogs = $smslogs->where('status',SMSlog::FAILED);
        }

        $smslogs = $smslogs->orderBy('id','desc')->with('user', 'androidGateway', 'smsGateway')->paginate(paginateNumber());
        return view('user.sms.index', compact('title', 'smslogs', 'search','searchDate'));
    }

}
