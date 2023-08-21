<?php

namespace App\Http\Controllers\Api\IncomingApi;

use App\Http\Controllers\Controller;
use App\Http\Resources\EmailLogResource;
use App\Http\Resources\GetEmailLogResource;
use App\Jobs\ProcessEmail;
use App\Models\CreditLog;
use App\Models\EmailContact;
use App\Models\EmailCreditLog;
use App\Models\EmailLog;
use App\Models\GeneralSetting;
use App\Models\MailConfiguration;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class EmailController extends Controller
{
    public function getEmailLog(string $uid)
    {
        $emailLog = EmailLog::where('uid', $uid)->first();
        if(!$emailLog){
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid Email Log uid'
            ],404);
        }

        return response()->json([
            'status' => 'success',
            'email_logs' => new GetEmailLogResource($emailLog),
        ],201);
    }

    public function store(Request $request)
    {
        $this->validate($request,[
            'contact' => 'required|array|min:1',
            'contact.*.subject' => 'required|max:255',
            'contact.*.email' => 'required|email|max:255',
            'contact.*.message' => 'required',
            'contact.*.sender_name' => 'required|max:255',
            'contact.*.reply_to_email' => 'required|email|max:255',
        ]);

        $general = GeneralSetting::first();
        $emailMethod = MailConfiguration::where('id',$general->email_gateway_id)->first();
        if(!$emailMethod){
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid Email Gateway'
            ],404);
        }

        $user = User::where('api_key', $request->header('Api-key'))->first();

        if($user){
            $totalContact = count($request->input('contact'));
            if($totalContact > $user->email_credit){
                return response()->json([
                    'status' => 'error',
                    'message' => 'You do not have a sufficient email credit for send mail'
                ],404);
            }

            $user->email_credit -=  $totalContact;
            $user->save();

            $emailCredit = new EmailCreditLog();
            $emailCredit->user_id = $user->id;
            $emailCredit->type = "-";
            $emailCredit->credit = $totalContact;
            $emailCredit->trx_number = trxNumber();
            $emailCredit->post_credit =  $user->email_credit;
            $emailCredit->details = $totalContact." credits were cut for send email";
            $emailCredit->save();
        }

        $emailHistory = collect();

        foreach($request->input('contact') as $key => $value){
            $emailLog = new EmailLog();
            $emailLog->user_id = $user ? $user->id : null;
            $emailLog->from_name = Arr::get($value, 'sender_name', 'Xsender');
            $emailLog->reply_to_email = Arr::get($value, 'sender_name');
            $emailLog->sender_id = $emailMethod->id;
            $emailLog->to = Arr::get($value, 'email');
            $emailLog->initiated_time = Carbon::now();
            $emailLog->status = 1;
            $emailLog->subject = Arr::get($value, 'subject');
            $emailLog->message = Arr::get($value, 'message');
            $emailLog->save();

            $emailHistory->push(new EmailLogResource($emailLog));
            ProcessEmail::dispatch($emailLog->id);
        }

        return response()->json([
            'status' => 'success',
            'email_logs' => $emailHistory->toArray(),
            'message' => 'New Email request sent, please see in the Email history for final status'
        ],201);
    }

}
