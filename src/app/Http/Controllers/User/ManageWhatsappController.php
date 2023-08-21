<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Contact;
use App\Models\SMSlog;
use App\Models\GeneralSetting;
use App\Models\CreditLog;
use App\Models\SmsGateway;
use Carbon\Carbon;
use Shuchkin\SimpleXLSX;
use App\Jobs\ProcessSms;
use App\Jobs\ProcessWhatsapp;
use App\Models\WhatsappCreditLog;
use App\Models\WhatsappDevice;
use App\Models\WhatsappLog;
use App\Rules\MessageFileValidationRule;
use App\Service\FileProcessService;
use Exception;
use Illuminate\Support\Facades\Http;

class ManageWhatsappController extends Controller
{
    public function create()
    {
    	$title = "Compose WhatsApp Massage";
    	$user = Auth::user();
    	$groups = $user->group()->get();
    	$templates = $user->template()->get();
    	return view('user.whatsapp.create', compact('title', 'groups', 'templates'));
    }

    public function index()
    {
    	$title = "WhatsApp History";
        $user = Auth::user();
        $whatsApp = WhatsappLog::where('user_id', $user->id)->orderBy('id', 'DESC')->with('whatsappGateway')->paginate(paginateNumber());
    	return view('user.whatsapp.index', compact('title', 'whatsApp'));
    }


    public function pending()
    {
        $title = "Pending WhatsApp Message History";
        $user = Auth::user();
        $whatsApp = WhatsappLog::where('user_id', $user->id)->orderBy('id', 'DESC')->where('status', 1)->with('whatsappGateway')->paginate(paginateNumber());
        return view('user.whatsapp.index', compact('title', 'whatsApp'));
    }


    public function delivered()
    {
        $title = "Delivered WhatsApp Message History";
        $user = Auth::user();
        $whatsApp = WhatsappLog::where('user_id', $user->id)->orderBy('id', 'DESC')->where('status', 4)->with('whatsappGateway')->paginate(paginateNumber());
        return view('user.whatsapp.index', compact('title', 'whatsApp'));
    }

    public function failed()
    {
        $title = "Failed WhatsApp Message History";
        $user = Auth::user();
        $whatsApp = WhatsappLog::where('user_id', $user->id)->orderBy('id', 'DESC')->where('status', 3)->with('whatsappGateway')->paginate(paginateNumber());
        return view('user.whatsapp.index', compact('title', 'whatsApp'));
    }

    public function scheduled()
    {
    	$title = "Scheduled WhatsApp Message History";
    	$user = Auth::user();
        $whatsApp = WhatsappLog::where('user_id', $user->id)->orderBy('id', 'DESC')->where('status', 2)->with('whatsappGateway')->paginate(paginateNumber());
        return view('user.whatsapp.index', compact('title', 'whatsApp'));
    }

    public function processing()
    {
        $title = "Processing WhatsApp Message History";
        $user = Auth::user();
        $whatsApp = WhatsappLog::where('user_id', $user->id)->orderBy('id', 'DESC')->where('status', 5)->with('whatsappGateway')->paginate(paginateNumber());
        return view('user.whatsapp.index', compact('title', 'whatsApp'));
    }

    public function store(Request $request)
    {
        session()->put('old_wa_message',$request->message?  $request->message :"");
        $user = Auth::user();

        $message = 'message';
        $rules = 'required';
        if($request->hasFile('document')){
            $message = 'document';
            $rules = ['required', new MessageFileValidationRule('document')];
        } else if($request->hasFile('audio')){
            $message = 'audio';
            $rules = ['required', new MessageFileValidationRule('audio')];
        } else if($request->hasFile('image')){
            $message = 'image';
            $rules = ['required', new MessageFileValidationRule('image')];
        } else if($request->hasFile('video')){
            $message = 'video';
            $rules = ['required', new MessageFileValidationRule('video')];
        }

        $request->validate([
            $message => $rules,
            'schedule' => 'required|in:1,2',
            'shedule_date' => 'required_if:schedule,2',
            'group_id' => 'nullable|array|min:1',
            'group_id.*' => 'nullable|exists:groups,id,user_id,'.$user->id,
        ]);

        if(!$request->number && !$request->group_id && !$request->file){
            $notify[] = ['error', 'Invalid number collect format'];
            return back()->withNotify($notify);
        }

        $allContactNumber = [];
        $numberGroupName  = [];
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
            if(!in_array($extension, ['csv','xlsx'])){
                $notify[] = ['error', 'Invalid file extension'];
                return back()->withNotify($notify);
            }
            $service = new FileProcessService();
         
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

        $contactNewArray = [];
        foreach($allContactNumber as $childArray){
            foreach($childArray as $value){
                $contactNewArray[] = $value;
            }
        }
         
        $general = GeneralSetting::first();
        $wordLenght = $general->whatsapp_word_count;

        $messages = str_split($request->message,$wordLenght);
        $totalMessage = count($messages);
        $totalNumber = count($contactNewArray);
        $totalCredit = $totalNumber * $totalMessage;

        if($totalCredit > $user->whatsapp_credit){
            $notify[] = ['error', 'You do not have a sufficient credit for send message'];
            return back()->withNotify($notify);
        }

        $user->whatsapp_credit -=  $totalCredit;
        $user->save();

        $creditInfo = new WhatsappCreditLog();
        $creditInfo->user_id = $user->id;
        $creditInfo->type = "-";
        $creditInfo->credit = $totalCredit;
        $creditInfo->trx_number = trxNumber();
        $creditInfo->post_credit =  $user->whatsapp_credit;
        $creditInfo->details = $totalCredit." credits were cut for " .$totalNumber . " number send message";
        $creditInfo->save();

        $whatsappGateway = WhatsappDevice::where('user_id', auth()->user()->id)->where('status', 'connected')->pluck('delay_time','id')->toArray();

        if(count($whatsappGateway) < 1){
            $notify[] = ['error', 'Not available WhatsApp Gateway'];
            return back()->withNotify($notify);
        }
        $postData = [];
        if($request->hasFile('document')){
            $file = $request->file('document');
            $fileName = uniqid().time().'.'.$file->getClientOriginalExtension();
            $path = filePath()['whatsapp']['path_document'];
            if(!file_exists($path)){
                mkdir($path, 0777, true);
            }
            try {
                move_uploaded_file($file->getRealPath(), $path.'/'.$fileName);
            } catch (\Exception $e) {

            }
            $postData['type'] = 'document';
            $postData['url_file'] = $path.'/'.$fileName;
            $postData['name'] = $fileName;
        }
        if($request->hasFile('audio')){
            $file = $request->file('audio');
            $fileName = uniqid().time().'.'.$file->getClientOriginalExtension();
            $path = filePath()['whatsapp']['path_audio'];
            if(!file_exists($path)){
                mkdir($path, 0777, true);
            }
            try {
                move_uploaded_file($file->getRealPath(), $path.'/'.$fileName);
            } catch (\Exception $e) {

            }
            $postData['type'] = 'audio';
            $postData['url_file'] = $path.'/'.$fileName;
            $postData['name'] = $fileName;
        }
        if($request->hasFile('image')){
            $file = $request->file('image');
            $fileName = uniqid().time().'.'.$file->getClientOriginalExtension();
            $path = filePath()['whatsapp']['path_image'];
            if(!file_exists($path)){
                mkdir($path, 0777, true);
            }
            try {
                move_uploaded_file($file->getRealPath(), $path.'/'.$fileName);
            } catch (\Exception $e) {

            }
            $postData['type'] = 'image';
            $postData['url_file'] = $path.'/'.$fileName;
            $postData['name'] = $fileName;
        }
        if($request->hasFile('video')){
            $file = $request->file('video');
            $fileName = uniqid().time().'.'.$file->getClientOriginalExtension();
            $path = filePath()['whatsapp']['path_video'];
            if(!file_exists($path)){
                mkdir($path, 0777, true);
            }
            try {
                move_uploaded_file($file->getRealPath(), $path.'/'.$fileName);
            } catch (\Exception $e) {

            }
            $postData['type'] = 'video';
            $postData['url_file'] = $path.'/'.$fileName;
            $postData['name'] = $fileName;
        }
        $delayTimeCount = [];
        $setTimeInDelay = 0;
        if($request->schedule == 2){
            $setTimeInDelay = $request->shedule_date;
        }else{
            $setTimeInDelay = Carbon::now();
        }
        $contactNewArray = array_unique($contactNewArray);
        $setWhatsAppGateway =  $whatsappGateway;
        $i = 1; $addSecond = 10;$gateWayid = null;
        foreach (array_filter($contactNewArray) as $key => $value) {

            if(filter_var($value, FILTER_SANITIZE_NUMBER_INT)){
                $contact =  preg_replace('/[^0-9]/', '', trim(str_replace('+', '', $value)));
                foreach ($setWhatsAppGateway as $key => $appGateway){
                    $addSecond = $appGateway * $i;
                    $gateWayid = $key;
                    unset($setWhatsAppGateway[$key]);
                    if(empty($setWhatsAppGateway)){
                        $setWhatsAppGateway =  $whatsappGateway;
                        $i++;
                    }
                    break;
                }
                $log = new WhatsappLog();
                $log->user_id = $user->id;
                if(count($whatsappGateway) > 0){
                    $log->whatsapp_id =  $gateWayid;
                }
                $log->to = $contact;
                $log->initiated_time = $request->schedule == 1 ? Carbon::now() : $request->shedule_date;
                if(array_key_exists($value,$numberGroupName)){
                    $finalContent = str_replace('{{name}}', $numberGroupName ? $numberGroupName[$value]:$value, offensiveMsgBlock($request->message));
                }
                else{
                    $finalContent = str_replace('{{name}}',$value, offensiveMsgBlock($request->message));
                }
                $log->message = $finalContent;
                $log->word_length  = $wordLenght;
                $log->status = $request->schedule == 2 ? 2 : 1;
                if($request->hasFile('document')){
                    $log->document = $fileName;
                }
                if($request->hasFile('audio')){
                    $log->audio = $fileName;
                }
                if($request->hasFile('image')){
                    $log->image = $fileName;
                }
                if($request->hasFile('video')){
                    $log->video = $fileName;
                }
                $log->schedule_status = $request->schedule;
                $log->save();

                if(count($contactNewArray) == 1 && $request->schedule == 1){
                    dispatch_now(new ProcessWhatsapp($finalContent, $value, $log->id, $postData));
                }
                else{
                    dispatch(new ProcessWhatsapp($finalContent, $value, $log->id, $postData))->delay(Carbon::parse($setTimeInDelay)->addSeconds($addSecond));
                }

            }

        }
        $notify[] = ['success', 'New WhatsApp Message request sent, please see in the WhatsApp Log history for final status'];
        session()->forget('old_wa_message');
        return back()->withNotify($notify);
    }


    public function search(Request $request, $scope)
    {
        $title = "WhatsApp History";
        $search = $request->search;
        $searchDate = $request->date;
        $user = Auth::user();


        if ($search!="") {
            $smslogs = WhatsappLog::where('user_id', $user->id)->where('to','like',"%$search%");
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
                $smslogs = WhatsappLog::where('user_id', $user->id)->whereDate('created_at',Carbon::parse($firstDate));
            }
            if ($lastDate){
                $smslogs = WhatsappLog::where('user_id', $user->id)->whereDate('created_at','>=',Carbon::parse($firstDate))->whereDate('created_at','<=',Carbon::parse($lastDate));
            }

        }
        if ($search=="" && $searchDate==""){
                $notify[] = ['error','Please give any search filter data'];
                return back()->withNotify($notify);
        }
        if($scope == 'pending') {
            $smslogs = $smslogs->where('status',WhatsappLog::PENDING);
        }elseif($scope == 'delivered'){
            $smslogs = $smslogs->where('status',WhatsappLog::SUCCESS);
        }elseif($scope == 'schedule'){
            $smslogs = $smslogs->where('status',WhatsappLog::SCHEDULE);
        }elseif($scope == 'failed'){
            $smslogs = $smslogs->where('status',WhatsappLog::FAILED);
        }

        $whatsApp = $smslogs->orderBy('id','desc')->with('user', 'whatsappGateway')->paginate(paginateNumber());
        return view('user.whatsapp.index', compact('title', 'whatsApp', 'search','searchDate'));
    }


    public function statusUpdate(Request $request)
    {
        $request->validate([
            'status' => 'required|in:1,3,4',
        ]);

        if($request->input('smslogid') !== null){
            $whatsappLogs = array_filter(explode(",",$request->input('smslogid')));
            if(!empty($whatsappLogs)){
                $this->whatsappLogStatusUpdate((int) $request->status, (array) $whatsappLogs);
            }
        }

        $notify[] = ['success', 'WhatsApp status has been updated'];
        return back()->withNotify($notify);
    }

    private function whatsappLogStatusUpdate(int $status, array $whatsappLogs): void
    {
        foreach($whatsappLogs as $id){
            $whatsappLog = WhatsappLog::where('id', $id)->where('status', 1)->where('user_id', auth()->user()->id)->first();
            if(!$whatsappLog){
                continue;
            }

            $whatsappLog->status = $status;
            $whatsappLog->save();
        }
    }

}

