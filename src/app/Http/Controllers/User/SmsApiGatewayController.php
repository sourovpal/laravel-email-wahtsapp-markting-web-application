<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\GeneralSetting;
use App\Models\SmsGateway;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;

class SmsApiGatewayController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $setting = GeneralSetting::first();

        $title = "SMS API Gateway list";
        $smsGateways = SmsGateway::orderBy('id','asc')->paginate(paginateNumber());
        $defaultGateway = Arr::get($user->gateways_credentials, 'sms.default_gateway_id',  $setting->sms_gateway_id);
        return view('user.gateway.index', compact('title', 'smsGateways', 'defaultGateway'));
    }

    public function edit($id)
    {
        $title = "SMS API Gateway update";
        $smsGateway = SmsGateway::findOrFail($id);
        $user = Auth::user();

        $credentials = Arr::get($user->gateways_credentials, 'sms.'.$smsGateway->gateway_code, (array)$smsGateway->credential);
        return view('user.gateway.edit', compact('title', 'smsGateway', 'credentials'));
    }

    public function update(Request $request, $id)
    {
        $user = Auth::user();
        $smsGateway = SmsGateway::findOrFail($id);
        $credentials = Arr::get($user->gateways_credentials, 'sms.'.$smsGateway->gateway_code, (array)$smsGateway->credential);

        foreach ($credentials as $key => $value){
            $this->validate($request, [
                $key => 'required'
            ]);
        }

        $data = $user->gateways_credentials;
        Arr::set($data, 'sms.'.$smsGateway->gateway_code, $request->only(array_keys($credentials)));

        $user->gateways_credentials = $data;
        $user->save();

        $notify[] = ['success', 'SMS Gateway has been updated'];
        return back()->withNotify($notify);
    }

    public function defaultGateway(Request $request)
    {
        $this->validate($request, [
           'default_gateway_id' => 'required|exists:sms_gateways,id'
        ]);

        $smsGateway = SmsGateway::findOrFail($request->input('default_gateway_id'));
        $user = Auth::user();

        $credentials = $user->gateways_credentials;
        Arr::set($credentials, 'sms.default_gateway_id', $request->input('default_gateway_id'));
        $user->gateways_credentials = $credentials;
        $user->save();

        $notify[] = ['success', 'Default SMS Gateway has been updated'];
        return back()->withNotify($notify);
    }
}
