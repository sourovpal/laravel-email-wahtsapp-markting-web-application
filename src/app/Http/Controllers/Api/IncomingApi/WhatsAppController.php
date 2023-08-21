<?php

namespace App\Http\Controllers\Api\IncomingApi;

use App\Http\Controllers\Controller;
use App\Http\Resources\GetWhatsAppLogResource;
use App\Http\Resources\WhatsAppLogResource;
use App\Jobs\ProcessWhatsapp;
use App\Models\Admin;
use App\Models\Contact;
use App\Models\GeneralSetting;
use App\Models\User;
use App\Models\WhatsappCreditLog;
use App\Models\WhatsappDevice;
use App\Models\WhatsappLog;
use App\Rules\MessageFileValidationRule;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Shuchkin\SimpleXLSX;

class WhatsAppController extends Controller
{
    public function getWhatsAppLog(string $uid)
    {
        $whatsLog = WhatsappLog::where('uid', $uid)->first();
        if(!$whatsLog){
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid Whatsapp Log uid'
            ],404);
        }

        return response()->json([
            'status' => 'success',
            'whats_log' => new GetWhatsAppLogResource($whatsLog),
        ],201);
    }

    public function store(Request $request)
    { 
        $this->validate($request,[
            'contact' => 'required|array|min:1',
            'contact.*.number' => 'required|max:255',
            'contact.*.message' => 'required'
        ]);

        $general = GeneralSetting::first();
        $user = User::where('api_key', $request->header('Api-key'))->first();
        $admin = Admin::where('api_key', $request->header('Api-key'))->first();

        $whatsAppHistory = collect();

        $whatsappGateway = null;

        if($user){
            $whatsappGateway = WhatsappDevice::where('user_id', $user->id)->where('status', 'connected')->pluck('delay_time','id')->toArray();
        }

        if($admin){
            $whatsappGateway = WhatsappDevice::where('admin_id', $admin->id)->where('status', 'connected')->pluck('delay_time','id')->toArray();
        }

        if(count($whatsappGateway) < 1 || is_null($whatsappGateway)){
            return response()->json([
                'status' => 'error',
                'message' => 'Not available WhatsApp Gateway'
            ],403);
        }

        $delayTimeCount = [];
      
        $setTimeInDelay = Carbon::now();
        
        $setWhatsAppGateway =  $whatsappGateway;
        $i = 1; $addSecond = 10; $gateWayid = null;
        foreach($request->input('contact') as $key => $value){
            if($user){
                $messages = str_split($request->message,$general->whatsapp_word_count);
                $totalCredit = count($messages);

                if($totalCredit > $user->whatsapp_credit){
                    return response()->json([
                        'status' => 'error',
                        'message' => 'You do not have a sufficient credit for send message'
                    ],403);
                }

                $user->whatsapp_credit -=  $totalCredit;
                $user->save();

                $creditInfo = new WhatsappCreditLog();
                $creditInfo->user_id = $user->id;
                $creditInfo->type = "-";
                $creditInfo->credit = $totalCredit;
                $creditInfo->trx_number = trxNumber();
                $creditInfo->post_credit =  $user->whatsapp_credit;
                $creditInfo->details = $totalCredit." credits were cut for 1 number send message";
                $creditInfo->save();
            }

            $postData = [
                'type' => Arr::get($value,'media'),
                'url_file' => Arr::get($value,'url'),
                'name' => Arr::get($value,'url')
            ];

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
            $log->user_id = $user ? $user->id : null;
            $log->word_length = $general->whatsapp_word_count;
            $log->whatsapp_id = count($whatsappGateway) > 0 ?  $gateWayid : null;
            $log->to = Arr::get($value, 'number');
            $log->message = str_replace('{{name}}',Arr::get($value, 'number'),offensiveMsgBlock(Arr::get($value, 'message')));
            $log->status =  1;
            $log->document = Arr::get($value, 'url');
            $log->save();  

            $whatsAppHistory->push(new WhatsAppLogResource($log));

            dispatch(new ProcessWhatsapp($log->message, $log->to, $log->id, $postData))->delay(Carbon::parse($setTimeInDelay)->addSeconds($addSecond));
        }

        return response()->json([
            'status' => 'success',
            'whatsapp_logs' => $whatsAppHistory->toArray(),
            'message' => 'New WhatsApp Message request sent, please see in the WhatsApp Log history for final status'
        ],201);
    }
}
