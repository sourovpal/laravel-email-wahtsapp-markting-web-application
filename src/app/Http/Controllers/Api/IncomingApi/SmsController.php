<?php

namespace App\Http\Controllers\Api\IncomingApi;

use App\Http\Controllers\Controller;
use App\Http\Resources\GetSmsLogResource;
use App\Http\Resources\SmsLogResource;
use App\Jobs\ProcessSms;
use App\Models\Contact;
use App\Models\CreditLog;
use App\Models\GeneralSetting;
use App\Models\SmsGateway;
use App\Models\SMSlog;
use App\Models\User;
use App\Service\SmsService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class SmsController extends Controller
{

    public $smsService ;
    public function __construct(SmsService $smsService)
    {
        $this->smsService = $smsService;
    }

    public function getSmsLog(string $uid)
    {
        $smsLog = SMSlog::where('uid', $uid)->first();
        if(!$smsLog){
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid SMS Log uid'
            ],404);
        }

        return response()->json([
            'status' => 'success',
            'sms_logs' => new GetSmsLogResource($smsLog),
        ],201);
    }


    public function store(Request $request)
    {
        try {
            $this->validate($request,[
                'contact' => 'required|array|min:1',
                'contact.*.number' => 'required|max:255',
                'contact.*.body' => 'required',
                'contact.*.sms_type' => 'required|in:plain,unicode',
            ]);

            $general = GeneralSetting::first();

            $user = User::where('api_key', $request->header('Api-key'))->first();
            if($user){
                $smsGateway = SmsGateway::where('id', Arr::get((array)$user->gateways_credentials, 'sms.default_gateway_id', 1))->first();
            }else{
                $smsGateway = SmsGateway::where('id',$general->sms_gateway_id)->first();
            }

            if(!$smsGateway){
                return response()->json([
                    'status' => 'error',
                    'message' => 'Invalid Sms Gateway'
                ],404);
            }

            $smsLogs = collect();

            foreach ($request->input('contact') as $key => $value) {
                $wordLength = null;
                if($user){
                    $wordLength = Arr::get($value, 'sms_type') == "plain" ? $general->sms_word_text_count : $general->sms_word_unicode_count;

                    $messages = str_split($request->message,$wordLength);
                    $totalCredit = count($messages);
                    if($totalCredit > $user->credit){
                        return response()->json([
                            'status' => 'error',
                            'message' => 'You do not have a sufficient credit for send message'
                        ],404);
                    }

                    $user->credit -=  $totalCredit;
                    $user->save();

                    $creditInfo = new CreditLog();
                    $creditInfo->user_id = $user->id;
                    $creditInfo->credit_type = "-";
                    $creditInfo->credit = count($messages);
                    $creditInfo->trx_number = trxNumber();
                    $creditInfo->post_credit =  $user->credit;
                    $creditInfo->details = $totalCredit." credits were cut for 1 number send message";
                    $creditInfo->save();
                }

                $log = new SMSlog();
                $log->user_id = $user ? $user->id : null;
                $log->word_lenght = $wordLength;
                if($user){
                    $log->api_gateway_id = $user->sms_gateway == 1 ? $smsGateway->id : null;
                }else{
                    $log->api_gateway_id = $general->sms_gateway == 1 ? $smsGateway->id : null;
                }

                $log->to = Arr::get($value, 'number');
                $log->sms_type = Arr::get($value, 'sms_type') == "plain" ? 1 : 2;
                $log->message = str_replace('{{name}}',Arr::get($value, 'number'), offensiveMsgBlock(Arr::get($value, 'body')));
                $log->status = 1;
                $log->save();


                if($user){
                    if($user->sms_gateway == 1){
                        $this->smsService->sendSmsByOwnGateway($log);
                    }
                }else{
                    if($general->sms_gateway == 1){
                        $this->smsService->sendSmsByOwnGateway($log);
                    }
                }

                $smsLogs->push(new SmsLogResource($log));
            }

            return response()->json([
                'status' => 'success',
                'sms_logs' => $smsLogs->toArray(),
                'message' => 'New SMS request sent, please see in the SMS history for final status'
            ],201);
        }catch (Throwable $e){
            echo $e;
        }
    }
}
