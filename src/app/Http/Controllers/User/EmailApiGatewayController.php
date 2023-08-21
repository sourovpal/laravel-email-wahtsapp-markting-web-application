<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\GeneralSetting;
use App\Models\MailConfiguration;
use App\Models\SmsGateway;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;

class EmailApiGatewayController extends Controller
{
    public function index()
    {
        $title = "Mail Configuration";
        $mails = MailConfiguration::latest()->get();

        $user = Auth::user();
        $setting = GeneralSetting::first();
        $defaultGateway = Arr::get($user->gateways_credentials, 'email.default_gateway_id',  $setting->email_gateway_id);
        return view('user.mail.index', compact('title', 'mails', 'defaultGateway'));
    }

    public function edit($id)
    {
        $title = "Mail updated";
        $mail = MailConfiguration::findOrFail($id);

        $user = Auth::user();
        $mailName = $mail->name;
        if($mail->name == "SendGrid Api"){
            $mailName = 'send_grid_api';
        }
        $credentials = array_replace_recursive(config('setting.gateway_credentials.email.'.$mailName), Arr::get($user->gateways_credentials, 'email.'.$mailName, []));
        return view('user.mail.edit', compact('title', 'mail', 'credentials'));
    }

    public function update(Request $request, $id)
    {
        
        $user = Auth::user();
        $mail = MailConfiguration::findOrFail($id);
        $mailName = $mail->name;

        if($mail->name == "SendGrid Api"){
            $mailName = 'send_grid_api';
        }

        $credentials = array_replace_recursive(config('setting.gateway_credentials.email.'.$mailName), Arr::get($user->gateways_credentials, 'email.'.$mailName, []));
        foreach ($credentials as $key => $value){
            $this->validate($request, [
                $key => 'required'
            ]);
        }

        $data = $user->gateways_credentials;
        Arr::set($data, 'email.'.$mailName, $request->only(array_keys($credentials)));

        $user->gateways_credentials = $data;
        $user->save();

        $notify[] = ['success',  ucfirst($mail->name).' mail method has been updated'];
        return back()->withNotify($notify);
    }


    public function defaultGateway(Request $request)
    {
        $this->validate($request, [
            'default_gateway_id' => 'required|exists:sms_gateways,id'
        ]);

        $mail = MailConfiguration::findOrFail($request->input('default_gateway_id'));
        $user = Auth::user();

        $credentials = $user->gateways_credentials;
        Arr::set($credentials, 'email.default_gateway_id', $request->input('default_gateway_id'));

        $user->gateways_credentials = $credentials;
        $user->save();

        $notify[] = ['success', 'Default Email Gateway has been updated'];
        return back()->withNotify($notify);
    }
}
