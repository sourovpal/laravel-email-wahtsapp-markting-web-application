<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

use App\Models\User;
use App\Models\WhatsappCreditLog;
use App\Models\WhatsappLog;
use Exception;
use Illuminate\Support\Facades\Http;

class ProcessWhatsapp implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $message;
    protected $number;
    protected $logId;
    protected $postData;


    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($message, $number, $logId, $postData)
    {
        $this->message = $message;
        $this->number = $number;
        $this->logId = $logId;
        $this->postData = $postData;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $whatsappLog = WhatsappLog::with('whatsappGateway')->find(trim($this->logId));

        if(!$whatsappLog){
            return false;
        }
        if($this->message != null){
            $body = ['text'=>$this->message];
        }
        if(array_key_exists('type', $this->postData)){
            $url = $this->postData['url_file'];
            if (filter_var($url, FILTER_VALIDATE_URL)) { 
                $media_url = $url;
            } else { 
                $media_url = url($url);
            }
            if($this->postData['type'] == "image" ){
                $body = [
                    'image'=>[
                        'url'=>$media_url
                    ],
                    'mimetype' => 'image/jpeg',
                    'caption'=>$this->message,
                ];
            }
            else if($this->postData['type'] == "audio" ){
                $body = [
                    'audio'=>[
                        'url'=>$media_url
                    ],
                    'caption'=>$this->message,
                ];
            }
            else if($this->postData['type'] == "video" ){
                $body = [
                    'video'=>[
                        'url'=>$media_url
                    ],
                    'caption'=>$this->message,
                ];
            }
            else if($this->postData['type'] == "document" ){
                $body = [
                    'document'=>[
                        'url'=>$media_url
                    ],
                    'mimetype' => 'application/pdf',
                    'fileName' => $this->postData['name'],
                    'caption'  => $this->message,
                ];
            }
        }

        //send api
        $response = null;
        try{
            $apiURL = config('requirements.core.wa_key_get').'/chats/send?id='.$whatsappLog->whatsappGateway->name;
            $postInput = [
                'receiver' => trim($this->number),
                'message' => $body
            ];
            $headers = [
                'Content-Type' => 'application/json',
            ]; 
            
            $response = Http::withoutVerifying()->withHeaders($headers)->post($apiURL, $postInput);

            if ($response) {
                $res = json_decode($response->getBody(), true);
                if($res['success']){
                    $whatsappLog->status = WhatsappLog::SUCCESS;
                    $whatsappLog->save();
                }else{
                    $whatsappLog->status = WhatsappLog::FAILED;
                    $whatsappLog->response_gateway = $res['message'];
                    $whatsappLog->save();
                    $user = User::find($whatsappLog->user_id);
                    if($user){
                        $messages = str_split($whatsappLog->message,$whatsappLog->word_length);
                        $totalcredit = count($messages);

                        $user->whatsapp_credit += $totalcredit;
                        $user->save();
                        $creditInfo = new WhatsappCreditLog();
                        $creditInfo->user_id = $whatsappLog->user_id;
                        $creditInfo->type = "+";
                        $creditInfo->credit = $totalcredit;
                        $creditInfo->trx_number = trxNumber();
                        $creditInfo->post_credit =  $user->whatsapp_credit;
                        $creditInfo->details = $totalcredit." Credit Return ".$whatsappLog->to." is Falied";
                        $creditInfo->save();
                    }
                }
            }else{
                $whatsappLog->status = WhatsappLog::FAILED;
                $whatsappLog->response_gateway = 'Error::2 Failed to send the message.';
                $whatsappLog->save();
                $user = User::find($whatsappLog->user_id);
                if($user){
                    $messages = str_split($whatsappLog->message,$whatsappLog->word_length);
                    $totalcredit = count($messages);

                    $user->whatsapp_credit += $totalcredit;
                    $user->save();

                    $creditInfo = new WhatsappCreditLog();
                    $creditInfo->user_id = $whatsappLog->user_id;
                    $creditInfo->type = "+";
                    $creditInfo->credit = $totalcredit;
                    $creditInfo->trx_number = trxNumber();
                    $creditInfo->post_credit =  $user->whatsapp_credit;
                    $creditInfo->details = $totalcredit." Credit Return ".$whatsappLog->to." is Falied";
                    $creditInfo->save();
                }
            } 
        } catch(Exception $exception){
                
        }
    }
}