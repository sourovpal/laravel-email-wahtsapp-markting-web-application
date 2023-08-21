<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\EmailLog;
use App\Models\EmailCreditLog;
use App\Models\EmailContact;
use App\Models\MailConfiguration;
use App\Models\GeneralSetting;
use Shuchkin\SimpleXLSX;
use Carbon\Carbon;
use App\Jobs\ProcessEmail;
use App\Service\FileProcessService;

class ManageEmailController extends Controller
{
    public function create()
    {
    	$title = "Compose Email";
    	$user = Auth::user();
    	$emailGroups = $user->emailGroup()->get();
    	return view('user.email.create', compact('title', 'emailGroups'));
    }

    public function index()
    {
    	$title = "All Email History";
        $user = Auth::user();
        $emailLogs = EmailLog::where('user_id', $user->id)->orderBy('id', 'DESC')->with('sender')->paginate(paginateNumber());
    	return view('user.email.index', compact('title', 'emailLogs'));
    }


    public function pending()
    {
        $title = "Pending Email History";
        $user = Auth::user();
        $emailLogs = EmailLog::where('user_id', $user->id)->orderBy('id', 'DESC')->where('status', 1)->with('sender')->paginate(paginateNumber());
        return view('user.email.index', compact('title', 'emailLogs'));
    }


    public function delivered()
    {
        $title = "Delivered Email History";
        $user = Auth::user();
        $emailLogs = EmailLog::where('user_id', $user->id)->orderBy('id', 'DESC')->where('status', 4)->with('sender')->paginate(paginateNumber());
        return view('user.email.index', compact('title', 'emailLogs'));
    }


    public function failed()
    {
        $title = "Failed  Email History";
        $user = Auth::user();
        $emailLogs = EmailLog::where('user_id', $user->id)->orderBy('id', 'DESC')->where('status', 3)->with('sender')->paginate(paginateNumber());
        return view('user.email.index', compact('title', 'emailLogs'));
    }

    public function scheduled()
    {
    	$title = "Scheduled Email History";
    	$user = Auth::user();
        $emailLogs = EmailLog::where('user_id', $user->id)->orderBy('id', 'DESC')->where('status', 2)->with('sender')->paginate(paginateNumber());
        return view('user.email.index', compact('title', 'emailLogs'));
    }

    public function store(Request $request)
    {

        $user = Auth::user();
        $request->validate([
            'subject' => 'required',
            'message' => 'required',
            'schedule' => 'required|in:1,2',
            'shedule_date' => 'required_if:schedule,2',
            'email_group_id' => 'nullable|array|min:1',
            'email_group_id.*' => 'nullable|exists:email_groups,id,user_id,'.$user->id,
            'email.*' => 'nullable',
        ]);
        if(!$request->email && !$request->email_group_id && !$request->file){
            $notify[] = ['error', 'Email address not found'];
            return back()->withNotify($notify);
        }
        $emailGroupName = [];
        $allEmail = [];

        if($request->has('email')){
            $email = EmailContact::whereIn('id', $request->email)->pluck('email','id')->toArray();
            $emailArr = array_values($email) + array_diff($request->email , $email);
            array_push($allEmail, $emailArr);
        }

        if($request->has('email_group_id')){
            $emailGroup = EmailContact::where('user_id', $user->id)->whereIn('email_group_id', $request->email_group_id)->pluck('email')->toArray();
            $emailGroupName = EmailContact::where('user_id', $user->id)->whereIn('email_group_id', $request->email_group_id)->pluck('name','email')->toArray();
            array_push($allEmail, $emailGroup);
        }

        if($request->has('file')){
            $extension = strtolower($request->file->getClientOriginalExtension());
            $service = new FileProcessService();
            if(!in_array($extension, ['csv','xlsx'])){
                $notify[] = ['error', 'Invalid file extension'];
                return back()->withNotify($notify);
            }

            if($extension == "csv"){
                $response =  $service->processCsv($request->file);
                array_push($allEmail,array_keys($response[0]));
                $emailGroupName = array_merge($emailGroupName, $response[0]);
            }
            if($extension == "xlsx"){
                $response =  $service->processExel($request->file);
                array_push($allEmail,array_keys($response[0]));
                $emailGroupName = array_merge($emailGroupName, $response[0]);
            }
        }

        if(!$user->email){
            $notify[] = ['error', 'Please add your email from profile'];
            return back()->withNotify($notify);
        }

        $contactNewArray = [];

        if (empty($allEmail)) {
            $notify[] = ['error', 'Email address not found'];
            return back()->withNotify($notify);
        }
        foreach($allEmail as $childArray){
            foreach($childArray as $value){
                $contactNewArray[] = $value;
            }
        }
        $contactNewArray = array_unique($contactNewArray);
        if(count($contactNewArray) > $user->email_credit){
            $notify[] = ['error', 'You do not have a sufficient email credit for send mail'];
            return back()->withNotify($notify);
        }
        $user->email_credit -=  count($contactNewArray);
        $user->save();

        $emailCredit = new EmailCreditLog();
        $emailCredit->user_id = $user->id;
        $emailCredit->type = "-";
        $emailCredit->credit = count($contactNewArray);
        $emailCredit->trx_number = trxNumber();
        $emailCredit->post_credit =  $user->email_credit;
        $emailCredit->details = count($contactNewArray)." credits were cut for send email";
        $emailCredit->save();

        $general = GeneralSetting::first();
        $emailMethod = MailConfiguration::where('id',$general->email_gateway_id)->first();
        if(!$emailMethod){
            $notify[] = ['error', 'Invalid Mail Gateway'];
            return back()->withNotify($notify);
        }

        $content = buildDomDocument(offensiveMsgBlock($request->message));
        $setTimeInDelay = 0;
        if($request->schedule == 2){
            $setTimeInDelay = $request->shedule_date;
        }else{
            $setTimeInDelay = Carbon::now();
        }
        foreach($contactNewArray as $key => $value) {
            if (filter_var( $value, FILTER_VALIDATE_EMAIL)) {
                $emailLog = new EmailLog();
                $emailLog->user_id = $user->id;
                $emailLog->from_name = $request->from_name;
                $emailLog->reply_to_email = $request->reply_to_email;
                $emailLog->sender_id = $emailMethod->id;
                $emailLog->to = $value;
                $emailLog->initiated_time = $request->schedule == 1 ? Carbon::now() : $request->shedule_date;
                $emailLog->subject = $request->subject;
                if(array_key_exists($value,$emailGroupName)){
                    $emailLog->message = str_replace('{{name}}', $emailGroupName ? $emailGroupName[$value]:$value, $content);
                }
                else{
                    $emailLog->message = str_replace('{{name}}',$value, $content);
                }
                $emailLog->status = $request->schedule == 2 ? 2 : 1;
                $emailLog->schedule_status = $request->schedule;
                $emailLog->save();
                if($emailLog->status == 1){
                    if(count($contactNewArray) == 1 && $request->schedule==1){
                        ProcessEmail::dispatchNow($emailLog->id);
                    }
                    else{
                        ProcessEmail::dispatch($emailLog->id)->delay(Carbon::parse($setTimeInDelay));
                    }
                }
            }   
        }
        $notify[] = ['success', 'New Email request sent, please see in the Email history for final status'];
        return back()->withNotify($notify);
    }

    public function viewEmailBody($id)
    {
        $title = "Details View";
        $user = Auth::user();
        $emailLogs = EmailLog::where('id',$id)->where('user_id',$user->id)->orderBy('id', 'DESC')->limit(1)->first();
        return view('partials.email_view', compact('title', 'emailLogs'));
    }

    public function search(Request $request, $scope)
    {
        $title = "Email History";
        $search = $request->search;
        $searchDate = $request->date;

        $user = Auth::user();

        if ($search!="") {
            $emailLogs = EmailLog::where('user_id', $user->id)->where('to','like',"%$search%");
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
                $emailLogs = EmailLog::where('user_id', $user->id)->whereDate('created_at',Carbon::parse($firstDate));
            }
            if ($lastDate){
                $emailLogs = EmailLog::where('user_id', $user->id)->whereDate('created_at','>=',Carbon::parse($firstDate))->whereDate('created_at','<=',Carbon::parse($lastDate));
            }
        }
        if ($search=="" && $searchDate==""){
                $notify[] = ['error','Please give any search filter data'];
                return back()->withNotify($notify);
        }
        if($scope == 'pending') {
            $emailLogs = $emailLogs->where('status',EmailLog::PENDING);
        }elseif($scope == 'delivered'){
            $emailLogs = $emailLogs->where('status',EmailLog::SUCCESS);
        }elseif($scope == 'schedule'){
            $emailLogs = $emailLogs->where('status',EmailLog::SCHEDULE);
        }elseif($scope == 'failed'){
            $emailLogs = $emailLogs->where('status',EmailLog::FAILED);
        }

        $emailLogs = $emailLogs->orderBy('id','desc')->with('sender')->paginate(paginateNumber());
        return view('user.email.index', compact('title', 'emailLogs', 'search','searchDate'));
    }

    public function emailStatusUpdate(Request $request)
    {
        $request->validate([
            'status' => 'required|in:1,3,4',
        ]);

        if($request->input('emaillogid') !== null){
            $emailLogIds = array_filter(explode(",",$request->input('emaillogid')));
            if(!empty($emailLogIds)){
                $this->emailLogStatusUpdate((int) $request->status, (array) $emailLogIds);
            }
        }

        $notify[] = ['success', 'Email status has been updated'];
        return back()->withNotify($notify);
    }

    private function emailLogStatusUpdate(int $status, array $emailLogIds)
    {
        foreach($emailLogIds as $emailLogId){
            $emailLog = EmailLog::where('user_id', auth()->user()->id)->where('status', 1)->where('id',$emailLogId)->first();
            if(!$emailLog){
                continue;
            }

            $emailLog->status = $status;
            $emailLog->update();
        }
    }
}
