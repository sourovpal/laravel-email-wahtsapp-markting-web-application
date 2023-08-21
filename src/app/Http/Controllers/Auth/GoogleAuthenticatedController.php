<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Laravel\Socialite\Facades\Socialite;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use App\Providers\RouteServiceProvider;
use App\Models\GeneralSetting;
use App\Models\PricingPlan;


class GoogleAuthenticatedController extends Controller
{
    public function redirectToGoogle()
    {
        $general = GeneralSetting::first();

        if(Arr::get($general->social_login, 'g_client_status', 1) != 1){
            $notify[] = ['error', 'Currently, social login is not enabled'];
            return back()->withNotify($notify);
        }
        return Socialite::driver('google')->redirect();
    }

    public function handleGoogleCallback()
    {
        try {
            $user = Socialite::driver('google')->user();
        } catch (\Exception $e) {
            return redirect('/');
        }
        $existingUser = User::where('email', $user->email)->first();
        $general = GeneralSetting::first();
        if($existingUser){
            Auth::login($existingUser);
            return redirect(RouteServiceProvider::HOME);
        } else {
            $newUser  = new User();
            $newUser->name = $user->name;
            $newUser->email = $user->email;
            $newUser->google_id = $user->id;
            $newUser->gateways_credentials = config('setting.gateway_credentials');
            $newUser->save();

            if($general->sign_up_bonus == 1){
                $plan = PricingPlan::find($general->plan_id);
                if($plan){
                    $newUser->credit = $plan->credit;
                    $newUser->email_credit = $plan->email_credit;
                    $newUser->save();
                }
            }
            Auth::login($newUser);
            return redirect(RouteServiceProvider::HOME);
        }
        return redirect()->route('login');
    }
}
