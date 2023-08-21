<?php

namespace App\Http\Controllers;


use App\Service\SmsService;
use App\Models\AndroidApi;
use App\Models\AndroidApiSimInfo;
use App\Models\SMSlog;
use App\Models\Subscription;
use App\Models\GeneralSetting;
use App\Models\EmailLog;
use Carbon\Carbon;
use App\Jobs\ProcessEmail;
use App\Models\Import;

class CronController extends Controller
{

    public $smsService ;
    public function __construct(SmsService $smsService)
    {
        $this->smsService = $smsService;
    }

    public function run()
    {
        $setting = GeneralSetting::first();

        $this->unlinkImportFile();
        $this->getewayCheck();
        $this->androidApiSim();
        $this->emailSchedule();
        $this->smsSchedule();
        if(Carbon::parse($setting->schedule_at)->addMinute(30) < Carbon::now()){
            $this->subscription();
            $setting->schedule_at = Carbon::now();
        }

        $setting->cron_job_run = Carbon::now();
        $setting->save();
    }
    protected function androidApiSim(){
        $smslogs = SMSlog::whereNull('api_gateway_id')->whereNull('android_gateway_sim_id')->where('status', 1)->get();
        foreach ($smslogs as $key => $smslog) {
            $androidSimInfos = [];
            $androidApis = [];

            if($smslog->user_id){
                $androidApis = AndroidApi::where('status', 1)->where('user_id', $smslog->user_id)->pluck('id')->toArray();
                $androidSimInfos = AndroidApiSimInfo::whereIn('android_gateway_id', $androidApis)->where('status', 1)->pluck('id')->toArray();
            }

            if(is_null($smslog->user_id)){
                $androidApis = AndroidApi::where('status', 1)->whereNotNull('admin_id')->pluck('id')->toArray();
                $androidSimInfos = AndroidApiSimInfo::whereIn('android_gateway_id', $androidApis)->where('status', 1)->pluck('id')->toArray();
            }

            if(!empty($androidSimInfos)){
                $smslog->android_gateway_sim_id = $androidSimInfos[array_rand($androidSimInfos,1)];
                $smslog->save();
            }
        }

    }

    public function unlinkImportFile(){
        $imports = Import::where('status',1)->get();
        foreach($imports  as $import){
            if(unlink(('assets/file/import/'.$import->path))){
                $import->delete();
            }
        }
    }

    protected function getewayCheck(){
        $smslogs = SMSlog::whereNotNull('android_gateway_sim_id')->where('status', 1)->get();
        foreach ($smslogs as $key => $smslog) {
            if($smslog->androidGateway->status == 2){
                $smslog->android_gateway_sim_id = null;
                $smslog->save();
            }
        }
    }


    protected function subscription()
    {
        $subscriptions = Subscription::where('status',1)->get();
        foreach($subscriptions as $subscription){
            $expiredTime = $subscription->expired_date;
            $now = Carbon::now()->toDateTimeString();
            if($now > $expiredTime){
                $subscription->status = 2;
                $subscription->save();
            }
        }
    }

    protected function smsSchedule()
    {
        $smslogs = SMSlog::where('status', 2)->where('schedule_status', 2)->get();
        $general = GeneralSetting::first();

        foreach($smslogs as $smslog){
            $expiredTime = $smslog->initiated_time;
            $now = Carbon::now()->toDateTimeString();
            if($now > $expiredTime){
                if($general->sms_gateway == 1){
                    $this->smsService->sendSmsByOwnGateway($smslog);
                }else{
                    $smslog->status = 1;
                    $smslog->api_gateway_id = null;
                    $smslog->android_gateway_sim_id = null;
                }
                $smslog->save();
            }
        }

        $pendingsmslogs = SMSlog::where('status', 1)->get();

        foreach($pendingsmslogs as $pendingsms){
            if($general->sms_gateway == 1){
                $this->smsService->sendSmsByOwnGateway($pendingsms);
            }else{
                $pendingsms->status = 1;
                $pendingsms->api_gateway_id = null;
            }
            $pendingsms->save();
        }
    }

    protected function emailSchedule()
    {
        $emailLogs = EmailLog::where('status', 2)->where('schedule_status', 2)->get();

        foreach($emailLogs as $emailLog){
            $expiredTime = $emailLog->initiated_time;
            $now = Carbon::now()->toDateTimeString();
            if($now > $expiredTime){
                ProcessEmail::dispatch($emailLog->id);
            }
        }
    }

}
