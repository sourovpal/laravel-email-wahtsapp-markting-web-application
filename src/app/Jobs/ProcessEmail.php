<?php

namespace App\Jobs;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Mail;
use App\Models\EmailLog;
use App\Models\EmailCreditLog;
use App\Models\User;
use App\Models\Admin;
use App\Models\GeneralSetting;
use App\Models\MailConfiguration;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Http\Utility\SendEmail;
use Exception;

class ProcessEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    protected $emailLogId;


    public function __construct($emailLogId)
    {
        $this->emailLogId = $emailLogId;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $emailLog = EmailLog::find($this->emailLogId);
        if(!$emailLog){
            return;
        }

        $general = GeneralSetting::first();
        $emailMethod = MailConfiguration::where('status',1)->where('id', $general->email_gateway_id)->first();

        if(!is_null($emailLog->user_id)){
            $user = User::find($emailLog->user_id);
            $emailGatewayId = Arr::get($user->gateways_credentials, 'email.default_gateway_id', 1);
            $emailMethod = MailConfiguration::where('status',1)->where('id', $emailGatewayId)->first();
        }

        if(!$emailMethod){
            return;
        }

        $emailLog->sender_id = $emailMethod->id;
        $emailLog->save();

        if($emailLog->user_id){
            $user = User::where('id', $emailLog->user_id)->first();

            if($emailMethod->name != "PHP MAIL"){
                $mailName = $emailMethod->name;
                if($emailMethod->name == "SendGrid Api"){
                    $mailName = 'send_grid_api';
                }

                $credentials = array_replace_recursive(config('setting.gateway_credentials.email.'.$mailName), Arr::get($user->gateways_credentials, 'email.'.$mailName, []));
    
                if($emailMethod->name == "SMTP"){
                    $encryption = Arr::get($credentials, 'encryption', config('setting.gateway_credentials.email.SMTP.encryption'));
                    $config = array(
                        'driver'     => Arr::get($credentials, 'driver', config('setting.gateway_credentials.email.SMTP.driver')),
                        'host'       => Arr::get($credentials, 'host', config('setting.gateway_credentials.email.SMTP.host')),
                        'port'       => Arr::get($credentials, 'port', config('setting.gateway_credentials.email.SMTP.port')),
                        'from'       => [
                            'address'=> Arr::get($credentials, 'from_address', config('setting.gateway_credentials.email.SMTP.port')),
                            'name'   => Arr::get($credentials, 'from_name', config('setting.gateway_credentials.email.SMTP.from_name')),
                        ],
                        'encryption' => $encryption =="PWMTA"?null:$encryption,
                        'username'   => Arr::get($credentials, 'username', config('setting.gateway_credentials.email.SMTP.username')),
                        'password'   => Arr::get($credentials, 'password', config('setting.gateway_credentials.email.SMTP.password')),
                        'sendmail'   => '/usr/sbin/sendmail -bs',
                        'pretend'    => false,
                    );
                    Config::set('mail', $config);
                }

                $emailFrom      = Arr::get($credentials, 'from_address', 'demo@gmail.com');
                $emailFromName  = Arr::get($credentials, 'from_name', $emailLog->from_name);
                $emailReplyTo   = $emailLog->reply_to_email ?? Arr::get($credentials, 'from_address', 'demo@gmail.com');
            }else{
                $emailFrom      = $general->mail_from;
                $emailFromName  = $general->site_name;
                $emailReplyTo   = $general->mail_from;
            }

        }else{
            if($emailMethod->name != "PHP MAIL"){
                $admin = Admin::where('id', 1)->first();
                $emailFrom      = $emailMethod->driver_information->from->address;
                $emailFromName  = $emailLog->from_name==''?$emailMethod->driver_information->from->name:$emailLog->from_name;
                $emailReplyTo   = $emailLog->reply_to_email==''?$admin->email:$emailLog->reply_to_email;
            }else{
                $emailFrom      = $general->mail_from;
                $emailFromName  = $general->site_name;
                $emailReplyTo   = $general->mail_from;
            }
        }

        $emailTo = $emailLog->to; $subject = $emailLog->subject; $messages = $emailLog->message;
    

        if($emailMethod->name == "PHP MAIL"){
          
            SendEmail::SendPHPmail($emailFrom, $emailFromName, $emailTo, $subject, $messages, $emailLog);
        }elseif($emailMethod->name == "SMTP"){

            SendEmail::SendSmtpMail($emailFrom, $emailFromName, $emailTo, $emailReplyTo, $subject, $messages, $emailLog);
        }elseif($emailMethod->name == "SendGrid Api"){
 
            $apiKey = '';
            if(is_null($emailLog->user_id)){
                $apiKey = @$emailMethod->driver_information->app_key;
            }

            $user = User::find($emailLog->user_id);
            if ($user && !is_null($user->gateways_credentials)) {
                $apiKey = Arr::get($user->gateways_credentials, 'email.send_grid_api.app_key', '###');
            }
            SendEmail::SendGrid($emailFrom, $emailFromName, $emailTo, $subject, $messages, $emailLog, $apiKey);
        }
    }

    public function failed($exception)
    {
        $data = EmailLog::find($this->emailLogId);
        if ($data->status==EmailLog::PENDING) {
            $data->status = EmailLog::FAILED;
            $data->save();

            $user = User::find($data->user_id);
            if($user){
                $user->email_credit += 1;
                $user->save();
                $emailCredit = new EmailCreditLog();
                $emailCredit->user_id = $user->id;
                $emailCredit->type = "+";
                $emailCredit->credit = 1;
                $emailCredit->trx_number = trxNumber();
                $emailCredit->post_credit =  $user->email_credit;
                $emailCredit->details = "Credit Added for failed " .$data->to;
                $emailCredit->save();
            }
        }
    }
}
