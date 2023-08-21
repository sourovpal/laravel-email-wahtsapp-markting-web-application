<?php

namespace App\Service;

use App\Http\Utility\SendSMS;
use App\Jobs\ProcessSms;
use App\Models\GeneralSetting;
use App\Models\SmsGateway;
use App\Models\SMSlog;
use App\Models\CreditLog;
use App\Models\User;
use Illuminate\Support\Arr;
use PHPUnit\Exception;

class SmsService
{
    /**
     * @param mixed $smsLog
     * @return void
     */
    public function sendSmsByOwnGateway(mixed $smsLog): void
    {
        $user = User::find($smsLog->user_id);
        $general = GeneralSetting::first();
        if (is_null($smsLog->user_id)) {
            $smsGateway = SmsGateway::where('id', $general->sms_gateway_id)->first();

			if($smsGateway){
				$this->sendSmsProcess($smsLog, (array)$smsGateway->credential, $smsGateway);
			}
		}


        if ($user && !is_null($user->gateways_credentials)) {

			$smsGatewayId = Arr::get($user->gateways_credentials, 'sms.default_gateway_id', 1);
			$smsGateway = SmsGateway::where('id', $smsGatewayId)->first();

			if($smsGateway){
				$credential = Arr::get($user->gateways_credentials, 'sms.' . $smsGateway->gateway_code, []);
				$this->sendSmsProcess($smsLog, (array)$credential, $smsGateway);
			}
		}
	}

	/**
	 * @param $smsLog
	 * @param array $credential
	 * @param SmsGateway $smsGateway
	 * @return void
	 */
	public function sendSmsProcess(SMSlog $smsLog, array $credential, SmsGateway $smsGateway)
	{
		try{
			$smsLog->api_gateway_id = $smsGateway->id;
			$smsLog->android_gateway_sim_id = null;
			$smsType = $smsLog->sms_type == 1 ? 'plain' : 'unicode';
			ProcessSms::dispatch($smsLog->to, $smsType, $smsLog->message, (object)$credential, $smsGateway->gateway_code, $smsLog->id);
		}catch (\Exception $exception){
			echo $exception->getMessage();
		}
	}


	public function smsLogStatusUpdate(int $status, array $smsLogIds, GeneralSetting $general, SmsGateway $smsGateway): void
	{
		foreach($smsLogIds as $smsLogId){
			$smslog = SMSlog::find($smsLogId);

			if(!$smslog){
				continue;
			}

			if($status == 1){
				if($general->sms_gateway == 1){
					$smslog->api_gateway_id = $smsGateway->id;
					$smslog->android_gateway_sim_id = null;
				}else{
					$smslog->api_gateway_id = null;
					$smslog->android_gateway_sim_id = null;
				}
			}

			if ($status == 3) {
				$user = User::find($smslog->user_id);
				if($user){
					$messages = str_split($smslog->message,(int)$smslog->word_lenght);
		            $totalcredit = count($messages);
					 
					$user->credit += $totalcredit;
					$user->save();

					$creditInfo = new CreditLog();
					$creditInfo->user_id = $smslog->user_id;
					$creditInfo->credit_type = "+";
					$creditInfo->credit = $totalcredit;
					$creditInfo->trx_number = trxNumber();
					$creditInfo->post_credit =  $user->credit;
					$creditInfo->details = $totalcredit." Credit Return ".$smslog->to." is Falied";
					$creditInfo->save();
				}
			}
			$smslog->status = $status;
			$smslog->update();
		}
	}


	public function smsLogStatusUpdateByUid(int $status, array $smsLogUids, GeneralSetting $general, SmsGateway $smsGateway): void
	{
		foreach($smsLogUids as $smsLogUid){
			$smslog = SMSlog::where('uid', $smsLogUid)
						      ->whereIn('status', [1, 2])
						      ->where('user_id', auth()->user()->id)
						      ->first();

			if(!$smslog){
				continue;
			}

            if($status == 1){
                if(auth()->user()->sms_gateway == 1){
                    $smslog->api_gateway_id = $smsGateway->id;
                    $smslog->android_gateway_sim_id = null;
                }else{
                    $smslog->api_gateway_id = null;
                    $smslog->android_gateway_sim_id = null;
                }
            }
        

			if ($status == 3) {
				$user = User::find($smslog->user_id);
				if($user){
					$messages = str_split($smslog->message,(int)$smslog->word_lenght);
		            $totalcredit = count($messages);
					 
					$user->credit += $totalcredit;
					$user->save();

					$creditInfo = new CreditLog();
					$creditInfo->user_id = $smslog->user_id;
					$creditInfo->credit_type = "+";
					$creditInfo->credit = $totalcredit;
					$creditInfo->trx_number = trxNumber();
					$creditInfo->post_credit =  $user->credit;
					$creditInfo->details = $totalcredit." Credit Return ".$smslog->to." is Falied";
					$creditInfo->save();
				}
			}

			$smslog->status = $status;
			$smslog->update();
		}

	}
}
