<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Service\SmsService;
use Illuminate\Http\Request;
use App\Models\SMSlog;
use App\Models\User;
use App\Models\CreditLog;
use App\Models\Group;
use App\Models\GeneralSetting;
use App\Models\SmsGateway;
use App\Models\Template;
use App\Models\Contact;
use App\Jobs\ProcessSms;
use App\Service\FileProcessService;
use Carbon\Carbon;
use Shuchkin\SimpleXLSX;
use Illuminate\Support\Facades\Http;


class SmsController extends Controller
{

    public $smsService ;
    public function __construct(SmsService $smsService)
    {
        $this->smsService = $smsService;
    }

    public function index()
    {
    	$title = "All SMS History";
    	$smslogs = SMSlog::orderBy('id', 'DESC')->with('user', 'androidGateway', 'smsGateway')->paginate(paginateNumber());
    	return view('admin.sms.index', compact('title', 'smslogs'));
    }

    public function pending()
    {
    	$title = "Pending SMS History";
    	$smslogs = SMSlog::where('status',SMSlog::PENDING)->orderBy('id', 'DESC')->with('user', 'androidGateway', 'smsGateway')->paginate(paginateNumber());
    	return view('admin.sms.index', compact('title', 'smslogs'));
    }

    public function success()
    {
    	$title = "Delivered SMS History";
    	$smslogs = SMSlog::where('status',SMSlog::SUCCESS)->orderBy('id', 'DESC')->with('user', 'androidGateway', 'smsGateway')->paginate(paginateNumber());
    	return view('admin.sms.index', compact('title', 'smslogs'));
    }

    public function schedule()
    {
    	$title = "Schedule SMS History";
    	$smslogs = SMSlog::where('status',SMSlog::SCHEDULE)->orderBy('id', 'DESC')->with('user', 'androidGateway', 'smsGateway')->paginate(paginateNumber());
    	return view('admin.sms.index', compact('title', 'smslogs'));
    }

    public function failed()
    {
    	$title = "Failed SMS History";
    	$smslogs = SMSlog::where('status',SMSlog::FAILED)->orderBy('id', 'DESC')->with('user', 'androidGateway', 'smsGateway')->paginate(paginateNumber());
    	return view('admin.sms.index', compact('title', 'smslogs'));
    }

    public function processing()
    {
        $title = "Processing SMS History";
        $smslogs = SMSlog::where('status',SMSlog::PROCESSING)->orderBy('id', 'DESC')->with('user', 'androidGateway', 'smsGateway')->paginate(paginateNumber());
        return view('admin.sms.index', compact('title', 'smslogs'));
    }

    public function search(Request $request, $scope)
    {
        $title = "SMS History Search";
        $search = $request->search;
        $searchDate = $request->date;

        if ($search!="") {
            $smslogs = SMSlog::where(function ($q) use ($search) {
                $q->where('to','like', "%$search%")->orWhereHas('user', function ($user) use ($search) {
                    $user->where('email', 'like', "%$search%");
                });
            });
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
                $smslogs = SMSlog::whereDate('created_at',Carbon::parse($firstDate));
            }
            if ($lastDate){
                $smslogs = SMSlog::whereDate('created_at','>=',Carbon::parse($firstDate))->whereDate('created_at','<=',Carbon::parse($lastDate));
            }
        }

        if ($search=="" && $searchDate=="") {
            $notify[] = ['error','Search data field empty'];
            return back()->withNotify($notify);
        }


        if($scope == 'pending') {
            $smslogs = $smslogs->where('status',SMSlog::PENDING);
        }elseif($scope == 'success'){
            $smslogs = $smslogs->where('status',SMSlog::SUCCESS);
        }elseif($scope == 'schedule'){
            $smslogs = $smslogs->where('status',SMSlog::SCHEDULE);
        }elseif($scope == 'failed'){
            $smslogs = $smslogs->where('status',SMSlog::FAILED);
        }
        $smslogs = $smslogs->orderBy('id','desc')->with('user', 'androidGateway', 'smsGateway')->paginate(paginateNumber());

        return view('admin.sms.index', compact('title', 'smslogs', 'search', 'searchDate'));
    }

    public function smsStatusUpdate(Request $request)
    {
        $request->validate([
            'id' => 'nullable|exists:s_m_slogs,id',
            'status' => 'required|in:1,3,4',
        ]);
        $general = GeneralSetting::first();
        $smsGateway = SmsGateway::where('id', $general->sms_gateway_id)->first();

        if(!$smsGateway){
            $notify[] = ['error', 'Invalid Sms Gateway'];
            return back()->withNotify($notify);
        }

        if($request->input('smslogid') !== null){
            $smsLogIds = array_filter(explode(",",$request->input('smslogid')));
            if(!empty($smsLogIds)){
                $this->smsService->smsLogStatusUpdate((int) $request->status, (array) $smsLogIds, $general, $smsGateway);
            }
        }

        if($request->has('id')){
            $this->smsService->smsLogStatusUpdate((int) $request->status, (array) $request->input('id'), $general, $smsGateway);
        }

        $notify[] = ['success', 'SMS status has been updated'];
        return back()->withNotify($notify);
    }

    public function create()
    {
        $title = "Compose SMS";
        $templates = Template::whereNull('user_id')->get();
        $groups = Group::whereNull('user_id')->get();
        return view('admin.sms.create', compact('title', 'groups', 'templates'));
    }

    public function store(Request $request)
    {

        session()->put('old_sms_message',$request->message?  $request->message :"");
        $request->validate([
            'message' => 'required',
            'smsType' => 'required|in:plain,unicode',
            'schedule' => 'required|in:1,2',
            'shedule_date' => 'required_if:schedule,2',
            'group_id' => 'nullable|array|min:1',
            'group_id.*' => 'nullable|exists:groups,id',
        ]);

        if(!$request->number && !$request->group_id && !$request->file){
            $notify[] = ['error', 'Invalid number collect format'];
            return back()->withNotify($notify);
        }
        $numberGroupName  = [];
        $allContactNumber = [];
        if($request->number){
            $contactNumber = preg_replace('/[ ,]+/', ',', trim($request->number));
            $recipientNumber  = explode(",",$contactNumber);
            array_push($allContactNumber, $recipientNumber);
        }
        if($request->group_id){
            $groupNumber = Contact::whereNull('user_id')->whereIn('group_id', $request->group_id)->pluck('contact_no')->toArray();
            $numberGroupName = Contact::whereNull('user_id')->whereIn('group_id', $request->group_id)->pluck('name','contact_no')->toArray();
            array_push($allContactNumber, $groupNumber);
        }
        if($request->file){
            $service = new FileProcessService();
            $extension = strtolower($request->file->getClientOriginalExtension());
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

        $smsGateway = SmsGateway::where('id', $general->sms_gateway_id)->first();
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
                if($general->sms_gateway == 1){
                    $log->api_gateway_id = $smsGateway->id;
                }
                $log->to = $contact ;
                $log->word_length = $wordLenght;
                $log->sms_type = $request->smsType == "plain" ? 1 : 2;
                $log->initiated_time = $request->schedule == 1 ? Carbon::now() : $request->shedule_date;
    
                if(array_key_exists($value,$numberGroupName)){
                    $finalContent = str_replace('{{name}}', $numberGroupName ? $numberGroupName[$value]:$value, offensiveMsgBlock($request->message));
                }
                else{
                    $finalContent = str_replace('{{name}}',$value, offensiveMsgBlock($request->message));
                }
                $log->message = $finalContent;
                $log->status = $request->schedule == 2 ? 2 : 1;
                $log->schedule_status = 1;
                $log->save();
    
                if($general->sms_gateway == 1){
                    if($log->status == 1){
                        if(count($contactNewArray) == 1 && $request->schedule==1){
                            ProcessSms::dispatchNow($value, $request->smsType, $finalContent, (object)$smsGateway->credential, $smsGateway->gateway_code, $log->id);
                        }else{
                            $smsType = $log->sms_type == 1 ? 'plain' : 'unicode';
                            ProcessSms::dispatch($value, $smsType, $finalContent, (object)$smsGateway->credential, $smsGateway->gateway_code, $log->id)->delay(Carbon::parse($setTimeInDelay));
                        }
                    }
                }
            }

        }
        $notify[] = ['success', 'New SMS request sent, please see in the SMS history for final status'];
        session()->forget('old_sms_message');
        return back()->withNotify($notify);
    }

    public function delete(Request $request)
    {
        $this->validate($request, [
            'id' => 'required'
        ]);
        try {
            $smsLog = SMSlog::findOrFail($request->id);
            $general = GeneralSetting::first();

            $wordLenght = $smsLog->sms_type == 1 ? $general->sms_word_text_count : $general->sms_word_unicode_count;

            if ($smsLog->status==1) {
                $user = User::find($smsLog->user_id);
                if($user){
                    $messages = str_split($smsLog->message,$wordLenght);
                    $totalcredit = count($messages);

                    $user->credit += $totalcredit;
                    $user->save();

                    $creditInfo = new CreditLog();
                    $creditInfo->user_id = $smsLog->user_id;
                    $creditInfo->credit_type = "+";
                    $creditInfo->credit = $totalcredit;
                    $creditInfo->trx_number = trxNumber();
                    $creditInfo->post_credit =  $user->credit;
                    $creditInfo->details = $totalcredit." Credit Return ".$smsLog->to." is Falied";
                    $creditInfo->save();
                }
            }
            $smsLog->delete();
            $notify[] = ['success', "Successfully SMS log deleted"];
        } catch (Exception $e) {
            $notify[] = ['error', "Error occour in SMS delete time. Error is "+$e->getMessage()];
        }
        return back()->withNotify($notify);
    }
}
