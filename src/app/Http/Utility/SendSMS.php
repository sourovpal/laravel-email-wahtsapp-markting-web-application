<?php
namespace App\Http\Utility;
use Textmagic\Services\TextmagicRestClient;
use Twilio\Rest\Client;
use App\Models\SMSlog;
use App\Models\CreditLog;
use App\Models\User;
use Illuminate\Support\Str;
use GuzzleHttp\Client AS InfoClient;
use Infobip\Api\SendSmsApi;
use Infobip\Configuration;
use Infobip\Model\SmsAdvancedTextualRequest;
use Infobip\Model\SmsDestination;
use Infobip\Model\SmsTextualMessage;
use App\Models\GeneralSetting;
use Exception;


class SendSMS{

	public static function nexmo($to,$datacoding,$message,$credential,$smsId)
	{
		$log = SMSlog::find($smsId);
		try {
			$basic = new \Vonage\Client\Credentials\Basic($credential->api_key, $credential->api_secret);
			$client = new \Vonage\Client($basic);
			$response = $client->sms()->send(
		    	new \Vonage\SMS\Message\SMS($to, $credential->sender_id, $message)
			);
			$message = $response->current();
			if($message->getStatus() == 0){
				$log->status = SMSlog::SUCCESS;
				$log->save();
			}else{
				$log->status = SMSlog::FAILED;
				$user = User::find($log->user_id);
		        if($user){
		            $messages = str_split($log->message,$log->word_length);
		            $totalcredit = count($messages);

		            $user->credit += $totalcredit;
		            $user->save();

		            $creditInfo = new CreditLog();
		            $creditInfo->user_id = $log->user_id;
		            $creditInfo->credit_type = "+";
		            $creditInfo->credit = $totalcredit;
		            $creditInfo->trx_number = trxNumber();
		            $creditInfo->post_credit =  $user->credit;
		            $creditInfo->details = $totalcredit." Credit Return ".$log->to." is Falied";
		            $creditInfo->save();
		        }
			}
		} catch (\Exception $e) {
			$log->status = SMSlog::FAILED;
			$log->response_gateway = $e->getMessage();
			$log->save();
			$user = User::find($log->user_id);
	        if($user){
	            $messages = str_split($log->message,$log->word_length);
	            $totalcredit = count($messages);

	            $user->credit += $totalcredit;
	            $user->save();

	            $creditInfo = new CreditLog();
	            $creditInfo->user_id = $log->user_id;
	            $creditInfo->credit_type = "+";
	            $creditInfo->credit = $totalcredit;
	            $creditInfo->trx_number = trxNumber();
	            $creditInfo->post_credit =  $user->credit;
	            $creditInfo->details = $totalcredit." Credit Return ".$log->to." is Falied";
	            $creditInfo->save();
	        }
		}
	}

	public static function twilio($to,$datacoding,$message,$credential,$smsId)
	{
        $log = SMSlog::find($smsId);
        try{
            $twilioNumber = $credential->from_number;
            $client = new Client($credential->account_sid, $credential->auth_token);
            $create = $client->messages->create('+'.$to, [
                'from' => $twilioNumber,
                'body' => $message
            ]);
            $log->status = SMSlog::SUCCESS;
            $log->save();
        }catch (\Exception $e) {
        	$log->status = SMSlog::FAILED;
			$log->response_gateway = $e->getMessage();
			$log->save();
			$user = User::find($log->user_id);
	        if($user){
	            $messages = str_split($log->message,$log->word_length);
	            $totalcredit = count($messages);
	            $user->credit += $totalcredit;
	            $user->save();
	            $creditInfo = new CreditLog();
	            $creditInfo->user_id = $log->user_id;
	            $creditInfo->credit_type = "+";
	            $creditInfo->credit = $totalcredit;
	            $creditInfo->trx_number = trxNumber();
	            $creditInfo->post_credit =  $user->credit;
	            $creditInfo->details = $totalcredit." Credit Return ".$log->to." is Falied";
	            $creditInfo->save();
	        }
        }
	}

	public static function messageBird($to,$datacoding,$message,$credential, $smsId)
	{
		$log 		 = SMSlog::find($smsId);
		try {
			$MessageBird 		 = new \MessageBird\Client($credential->access_key);
			$Message 			 = new \MessageBird\Objects\Message();
			$Message->originator = $credential->sender_id;
			$Message->recipients = array($to);
			$Message->datacoding = $datacoding;
			$Message->body 		 = $message;
			$MessageBird->messages->create($Message);

			$log->status = SMSlog::SUCCESS;
			$log->save();
		} catch (\Exception $e) {
			$log->status = SMSlog::FAILED;
			$log->response_gateway = $e->getMessage();
			$log->save();
			$user = User::find($log->user_id);
	        if($user){
	            $messages = str_split($log->message,$log->word_length);
	            $totalcredit = count($messages);

	            $user->credit += $totalcredit;
	            $user->save();

	            $creditInfo = new CreditLog();
	            $creditInfo->user_id = $log->user_id;
	            $creditInfo->credit_type = "+";
	            $creditInfo->credit = $totalcredit;
	            $creditInfo->trx_number = trxNumber();
	            $creditInfo->post_credit =  $user->credit;
	            $creditInfo->details = $totalcredit." Credit Return ".$log->to." is Falied";
	            $creditInfo->save();
	        }
		}
	}

	public static function textMagic($to,$datacoding,$message,$credential, $smsId)
	{
		$log = SMSlog::find($smsId);
		$client = new TextmagicRestClient($credential->text_magic_username, $credential->api_key);
		try {
		    $result = $client->messages->create(
		        array(
		            'text' => $message,
		            'phones' => $to,
		        )
		    );
		    $log->status = SMSlog::SUCCESS;
		    $log->save();
		}
		catch (\Exception $e) {
			$log->status = SMSlog::FAILED;
			$log->response_gateway = $e->getMessage();
			$log->save();
			$user = User::find($log->user_id);
	        if($user){
	            $messages = str_split($log->message,$log->word_length);
	            $totalcredit = count($messages);

	            $user->credit += $totalcredit;
	            $user->save();

	            $creditInfo = new CreditLog();
	            $creditInfo->user_id = $log->user_id;
	            $creditInfo->credit_type = "+";
	            $creditInfo->credit = $totalcredit;
	            $creditInfo->trx_number = trxNumber();
	            $creditInfo->post_credit =  $user->credit;
	            $creditInfo->details = $totalcredit." Credit Return ".$log->to." is Falied";
	            $creditInfo->save();
	        }
		}
	}

	public static function clickaTell($to,$datacoding,$message,$credentials,$smsId)
	{
		$log = SMSlog::find($smsId);
		try {
			$message = urlencode($message);
			$response = @file_get_contents("https://platform.clickatell.com/messages/http/send?apiKey=$credentials->clickatell_api_key&to=$to&content=$message");

			if ($response==false) {
				$log->status = SMSlog::FAILED;
				$log->response_gateway = "API Error, Check Your Settings";
				$log->save();
				$user = User::find($log->user_id);
		        if($user){
		            $messages = str_split($log->message,$log->word_length);
		            $totalcredit = count($messages);

		            $user->credit += $totalcredit;
		            $user->save();

		            $creditInfo = new CreditLog();
		            $creditInfo->user_id = $log->user_id;
		            $creditInfo->credit_type = "+";
		            $creditInfo->credit = $totalcredit;
		            $creditInfo->trx_number = trxNumber();
		            $creditInfo->post_credit =  $user->credit;
		            $creditInfo->details = $totalcredit." Credit Return ".$log->to." is Falied";
		            $creditInfo->save();
		        }
			}else{
				$log->status = SMSlog::SUCCESS;
				$log->save();
			}

		} catch (Throwable $e) {

		}
	}

	public static function infoBip($to,$datacoding,$message,$credentials,$smsId)
	{
		$BASE_URL = $credentials->infobip_base_url;
		$API_KEY = $credentials->infobip_api_key;

		$SENDER = $credentials->sender_id;
		$RECIPIENT = $to;
		$MESSAGE_TEXT = $message;

		$configuration = (new Configuration())
		    ->setHost($BASE_URL)
		    ->setApiKeyPrefix('Authorization', 'App')
		    ->setApiKey('Authorization', $API_KEY);

		$client = new InfoClient();

		$sendSmsApi = new SendSMSApi($client, $configuration);
		$destination = (new SmsDestination())->setTo($RECIPIENT);
		$message = (new SmsTextualMessage())
		    ->setFrom($SENDER)
		    ->setText($MESSAGE_TEXT)
		    ->setDestinations([$destination]);

		$request = (new SmsAdvancedTextualRequest())->setMessages([$message]);
		$log = SMSlog::find($smsId);
		try {
		    $smsResponse = $sendSmsApi->sendSmsMessage($request);
		    $log->status = SMSlog::SUCCESS;
			$log->save();
		} catch (Throwable $apiException) {

		}
	}

	public static function smsBroadcast($to,$datacoding,$message,$credentials,$smsId)
	{
		$log = SMSlog::find($smsId);
		try {
			$message = urlencode($message);
			$result = @file_get_contents("https://api.smsbroadcast.com.au/api-adv.php?username=$credentials->sms_broadcast_username&password=$credentials->sms_broadcast_password&to=$to&from=$credential->sender_id,&message=$message&ref=112233&maxsplit=5&delay=15");

			if ($result==Str::contains($result, 'ERROR:') || $result==Str::contains($result, 'BAD:')) {
				$log->status = SMSlog::FAILED;
		        $log->response_gateway = $result;
		        $log->save();

		        $user = User::find($log->user_id);
		        if($user){
		            $messages = str_split($log->message,$log->word_length);
		            $totalcredit = count($messages);

		            $user->credit += $totalcredit;
		            $user->save();

		            $creditInfo = new CreditLog();
		            $creditInfo->user_id = $log->user_id;
		            $creditInfo->credit_type = "+";
		            $creditInfo->credit = $totalcredit;
		            $creditInfo->trx_number = trxNumber();
		            $creditInfo->post_credit =  $user->credit;
		            $creditInfo->details = $totalcredit." Credit Return ".$log->to." is Falied";
		            $creditInfo->save();
		        }
			}else{
				$log->status = SMSlog::SUCCESS;
				$log->save();
			}
		} catch (Throwable $e) {

		}
	}


	public static function mimSMS($to,$datacoding,$message,$credentials,$smsId)
	{
		$log = SMSlog::find($smsId);
		try {
			$message = $log->sms_type=='1'?rawurlencode($message):$message;
			$url 	 = $credentials->api_url;
		  	$data = [
			    "api_key" => $credentials->api_key,
			    "type" => $datacoding,
			    "contacts" => $to,
			    "senderid" => $credentials->sender_id,
			    "msg" => $message,
		  	];
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
			$response = curl_exec($ch);
			curl_close($ch);

			if ($response=='1002' || $response=='1003' || $response=='1004' || $response=='1005' || $response=='1006' || $response=='1007' || $response=='1008' || $response=='1009' || $response=='1010' || $response=='1011') {
				$log->status = SMSlog::FAILED;
		        $log->response_gateway = $response;
		        $log->save();

		        $user = User::find($log->user_id);
		        if($user){

					$messages = str_split($log->message,$log->word_length);
		            $totalcredit = count($messages);

		            $user->credit += $totalcredit;
		            $user->save();

		            $creditInfo = new CreditLog();
		            $creditInfo->user_id = $log->user_id;
		            $creditInfo->credit_type = "+";
		            $creditInfo->credit = $totalcredit;
		            $creditInfo->trx_number = trxNumber();
		            $creditInfo->post_credit =  $user->credit;
		            $creditInfo->details = $totalcredit." Credit Return ".$log->to." is Falied";
		            $creditInfo->save();
		        }
			}else{
				$log->response_gateway = json_encode($data);
				$log->status = SMSlog::SUCCESS;
				$log->save();
			}
		} catch (Exception $e) {

		}
	}

	public static function ajuraSMS($to,$datacoding,$message,$credentials,$smsId)
	{
		$log = SMSlog::find($smsId);

		try {
			$message = urlencode($message);
			$url 	 = $credentials->api_url;
		  	$data = [
			    "apikey" => $credentials->api_key,
			    "secretkey" => $credentials->secret_key,
			    "callerID" => $credentials->sender_id,
			    "toUser" => $to,
			    "messageContent" => $message
		  	];
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
			$response = curl_exec($ch);
			curl_close($ch);
			$response = json_decode($response);
			if ($response->Status=='0') {
				$log->status = SMSlog::SUCCESS;
				$log->save();
			}else{
				$log->status = SMSlog::FAILED;
		        $log->response_gateway = $response;
		        $log->save();

		        $user = User::find($log->user_id);
		        if($user){

					$messages = str_split($log->message,$log->word_length);
		            $totalcredit = count($messages);

		            $user->credit += $totalcredit;
		            $user->save();

		            $creditInfo = new CreditLog();
		            $creditInfo->user_id = $log->user_id;
		            $creditInfo->credit_type = "+";
		            $creditInfo->credit = $totalcredit;
		            $creditInfo->trx_number = trxNumber();
		            $creditInfo->post_credit =  $user->credit;
		            $creditInfo->details = $totalcredit." Credit Return ".$log->to." is Falied";
		            $creditInfo->save();
		        }
			}
		} catch (Exception $e) {

		}
	}

	public static function msg91($to,$datacoding,$message,$credentials,$smsId)
	{  
		$log = SMSlog::find($smsId);
		$unicode = $datacoding == "plain" ? 0 : 1;
		$recipients = array(
		    array(
		        "mobiles" => $to,
		        "VAR1" => $message
		    )
		);

		//Prepare you post parameters
		$postData = array(
		    "sender" => $credentials->sender_id,
		    "flow_id" => $credentials->flow_id,
		    "recipients" => $recipients,
		    "unicode" => $unicode
		);
		$postDataJson = json_encode($postData);

		$url=$credentials->api_url;

		try {
			$curl = curl_init();
			curl_setopt_array($curl, array(
			    CURLOPT_URL => "$url",
			    CURLOPT_RETURNTRANSFER => true,
			    CURLOPT_CUSTOMREQUEST => "POST",
			    CURLOPT_POSTFIELDS => $postDataJson,
			    CURLOPT_HTTPHEADER => array(
			        "authkey: $credentials->auth_key",
			        "content-type: application/json"
			    ),
			));
			$response = curl_exec($curl);
			$err = curl_error($curl);
			curl_close($curl);

			if ($err) { 
			    $log->status = SMSlog::FAILED;
		        $log->response_gateway = "cURL Error #:" . $err;
		        $log->save();

		        $user = User::find($log->user_id);
		        if($user){

					$messages = str_split($log->message,$log->word_length);
		            $totalcredit = count($messages);

		            $user->credit += $totalcredit;
		            $user->save();

		            $creditInfo = new CreditLog();
		            $creditInfo->user_id = $log->user_id;
		            $creditInfo->credit_type = "+";
		            $creditInfo->credit = $totalcredit;
		            $creditInfo->trx_number = trxNumber();
		            $creditInfo->post_credit =  $user->credit;
		            $creditInfo->details = $totalcredit." Credit Return ".$log->to." is Falied";
		            $creditInfo->save();
		        }
			} else {
				if (json_decode($response)->type=="success") {
					$log->status = SMSlog::SUCCESS;
					$log->response_gateway = json_decode($response)->type;
					$log->save();
				}else{
					$log->status = SMSlog::FAILED;
			        $log->response_gateway = "Failed #:" . json_decode($response)->message;
			        $log->save();

			        $user = User::find($log->user_id);
			        if($user){

						$messages = str_split($log->message,$log->word_length);
			            $totalcredit = count($messages);

			            $user->credit += $totalcredit;
			            $user->save();

			            $creditInfo = new CreditLog();
			            $creditInfo->user_id = $log->user_id;
			            $creditInfo->credit_type = "+";
			            $creditInfo->credit = $totalcredit;
			            $creditInfo->trx_number = trxNumber();
			            $creditInfo->post_credit =  $user->credit;
			            $creditInfo->details = $totalcredit." Credit Return ".$log->to." is Falied";
			            $creditInfo->save();
			        }
				}	
			}
		} catch (Exception $e) {
			
		}
	}

}
