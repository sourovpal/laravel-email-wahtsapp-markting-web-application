<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CreditLog;
use App\Models\EmailCreditLog;
use App\Models\GeneralSetting;
use App\Models\PricingPlan;
use App\Models\WhatsappCreditLog;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Contact;
use App\Models\EmailContact;
use App\Models\SMSlog;
use App\Models\EmailLog;
use Carbon\Carbon;
use Illuminate\Validation\Rules;
use Illuminate\Support\Facades\Hash;

class CustomerController extends Controller
{

    public function index()
    {
        $title = "All user";
        $customers = User::latest()->paginate(paginateNumber());
        return view('admin.customer.index', compact('title', 'customers'));
    }

    public function active()
    {
        $title = "Active user";
        $customers = User::active()->paginate(paginateNumber());
        return view('admin.customer.index', compact('title', 'customers'));
    }

    public function banned()
    {
        $title = "Banned user";
        $customers = User::banned()->paginate(paginateNumber());
        return view('admin.customer.index', compact('title', 'customers'));
    }

    public function details($id)
    {
        $title = "User Details";
        $user = User::findOrFail($id);
        $log = [
            'contact' => Contact::where('user_id', $user->id)->count(),
            'sms' => SMSlog::where('user_id', $user->id)->count(),
            'email_contact' => EmailContact::where('user_id', $user->id)->count(),
            'email' =>  EmailLog::where('user_id', $user->id)->count()
        ];

        return view('admin.customer.details', compact('title', 'user', 'log'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $general = GeneralSetting::first();

        $user = User::create([
            'name' => $request->input('name'),
            'email' => $request->input('email'),
            'status' => 1,
            'password' => Hash::make($request->input('password')),
        ]);

        $plan = PricingPlan::find($general->plan_id);
        if($general->sign_up_bonus == 1 && $plan){
            $user->credit = $plan->credit;
            $user->email_credit = $plan->email_credit;
            $user->whatsapp_credit = $plan->whatsapp_credit;
            $user->save();
        }

        $notify[] = ['success', 'User has been created'];
        return back()->withNotify($notify);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'nullable|max:120',
            'email' => 'nullable|unique:users,email,'.$id,
            'address' => 'nullable|max:250',
            'city' => 'nullable|max:250',
            'state' => 'nullable|max:250',
            'zip' => 'nullable|max:250',
            'status' => 'nullable|in:1,2'
        ]);

        $user = User::findOrFail($id);
        $user->name = $request->name;
        $user->email = $request->email;
        $address = [
            'address' => $request->address,
            'city' => $request->city,
            'state' => $request->state,
            'zip' => $request->zip
        ];
        $user->address = $address;
        $user->status = $request->status;
        $user->save();

        $notify[] = ['success', 'User has been updated'];
        return back()->withNotify($notify);
    }

    public function search(Request $request, $scope)
    {

        $search = $request->search;
        $searchDate = $request->date;


        if ($search!="") {
            $customers = User::where(function ($q) use ($search) {
                $q->where('name','like',"%$search%")->orWhere('email', 'like', "%$search%");
            });
        }

        if ($searchDate!="") {
            $searchDate_array = explode('-',$request->date);
            $firstDate = $searchDate_array[0];
            $lastDate = null;
            if (count($searchDate_array)>1) {
                $lastDate = $searchDate_array[1];
            }
            $matchDate = "/\d{2}\/\d{2}\/\d{4}/";
            if ($firstDate && !preg_match($matchDate,$firstDate)) {
                $notify[] = ['error','Invalid order search date format'];
                return back()->withNotify($notify);
            }
            if ($lastDate && !preg_match($matchDate,$lastDate)) {
                $notify[] = ['error','Invalid order search date format'];
                return back()->withNotify($notify);
            }
            if ($firstDate) {
                $customers = User::whereDate('created_at',Carbon::parse($firstDate));
            }
            if ($lastDate){
                $customers = User::whereDate('created_at','>=',Carbon::parse($firstDate))->whereDate('created_at','<=',Carbon::parse($lastDate));
            }
        }

        if ($search=="" && $searchDate=="") {
            $notify[] = ['error','Search data field empty'];
            return back()->withNotify($notify);
        }


        $title = '';
        if ($scope == 'active') {
            $title = 'Active ';
            $customers = $customers->active();
        }elseif($scope == 'banned'){
            $title = 'Banned';
            $customers = $customers->banned();
        }
        $customers = $customers->paginate(paginateNumber());
        $title .= 'User Search - ' . $search;
        return view('admin.customer.index', compact('title', 'search', 'scope', 'customers'));
    }

    public function contact($id)
    {
        $user = User::findOrFail($id);
        $title = @$user->name." Contact List";
        $users = User::select('id', 'name')->get();
        $contacts = Contact::where('user_id', $user->id)->latest()->with('user', 'group')->paginate(paginateNumber());
        return view('admin.phone_book.sms_contact', compact('title', 'contacts', 'users'));
    }


    public function sms($id)
    {
        $user = User::findOrFail($id);
        $title = @$user->name." sms list";
        $smslogs = SMSlog::where('user_id', $user->id)->latest()->with('user', 'androidGateway', 'smsGateway')->paginate(paginateNumber());
        return view('admin.sms.index', compact('title', 'smslogs'));
    }

    public function emailContact($id)
    {
        $user = User::findOrFail($id);
        $title = @$user->name." email contact list";
        $users = User::select('id', 'name')->get();
        $emailContacts = EmailContact::where('user_id', $user->id)->latest()->with('user', 'emailGroup')->paginate(paginateNumber());
        return view('admin.phone_book.email_contact', compact('title', 'emailContacts', 'users'));
    }


    public function emailLog($id)
    {
        $user = User::findOrFail($id);
        $title = @$user->name." email list";
        $emailLogs = EmailLog::where('user_id', $user->id)->latest()->with('user','sender')->paginate(paginateNumber());
        return view('admin.email.index', compact('title', 'emailLogs'));
    }


    public function addReturnCredit(Request $request)
    {
        $request->validate([
            'type' => 'required|in:1,2',
            'sms_credit' => 'nullable|integer|gt:0',
            'email_credit' => 'nullable|integer|gt:0',
            'whatsapp_credit' => 'nullable|integer|gt:0',
        ]);

        $user = User::findOrFail($request->input('id'));

        $smsCredit = 0; $emailCredit = 0; $whatsappCredit = 0;

        if($request->input('sms_credit')){
            if($request->input('type') == 2 && $user->credit < $request->input('sms_credit')){
                $notify[] = ['error',  'Invalid SMS Credit Number'];
                return back()->withNotify($notify);
            }
            $smsCreditLog = $this->smsCredit($request, $user);
            $smsCredit = $smsCreditLog->credit;
        }

        if($request->input('email_credit')){
            if($request->input('type') == 2 && $user->email_credit < $request->input('email_credit')){
                $notify[] = ['error',  'Invalid Email Credit Number'];
                return back()->withNotify($notify);
            }

            $emailCreditLog = $this->emailCredit($request, $user);
            $emailCredit = $emailCreditLog->credit;
        }

        if($request->input('whatsapp_credit')){

            if($request->input('type') == 2 && $user->whatsapp_credit < $request->input('whatsapp_credit')){
                $notify[] = ['error',  'Invalid WhatsApp Credit Number'];
                return back()->withNotify($notify);
            }

            $whatsappCreditLog = $this->whatsAppCreate($request, $user);
            $whatsappCredit = $whatsappCreditLog->credit;
        }

        if($request->input('type') == 1){
            $user->credit += $smsCredit;
            $user->email_credit += $emailCredit;
            $user->whatsapp_credit += $whatsappCredit;
            $notify[] = ['success',  'Credit has been added'];
        }else{
            $user->credit -= $smsCredit;
            $user->email_credit -= $emailCredit;
            $user->whatsapp_credit -= $whatsappCredit;
            $notify[] = ['success',  'Credit has been returned'];
        }
        $user->save();
        return back()->withNotify($notify);
    }


    protected function smsCredit(Request $request, User $user)
    {
        $creditInfo = new CreditLog();
        $creditInfo->user_id = $user->id;
        $creditInfo->credit_type = $request->input('type') == 1 ? "+" : "-";
        $creditInfo->credit = $request->input('sms_credit');
        $creditInfo->trx_number = trxNumber();
        $creditInfo->post_credit =  $user->credit;
        $creditInfo->details =  $request->input('type') == 1 ? "Added by admin" : "Returnted by admin";;
        $creditInfo->save();

        return $creditInfo;
    }


    protected function emailCredit(Request $request, User $user)
    {
        $emailCredit = new EmailCreditLog();
        $emailCredit->user_id = $user->id;
        $emailCredit->type = $request->input('type') == 1 ? "+" : "-";
        $emailCredit->credit = $request->input('email_credit');
        $emailCredit->trx_number = trxNumber();
        $emailCredit->post_credit =  $user->email_credit;
        $emailCredit->details = $request->input('type') == 1 ? "Added by admin" : "Returnted by admin";
        $emailCredit->save();

        return $emailCredit;
    }


    protected function whatsAppCreate(Request $request, User $user)
    {
        $whatsappCredit = new WhatsappCreditLog();
        $whatsappCredit->user_id = $user->id;
        $whatsappCredit->type = $request->input('type') == 1 ? "+" : "-";
        $whatsappCredit->credit = $request->input('whatsapp_credit');
        $whatsappCredit->trx_number = trxNumber();
        $whatsappCredit->post_credit =  $user->whatsapp_credit;
        $whatsappCredit->details = $request->input('type') == 1 ? "Added by admin" : "Returnted by admin";
        $whatsappCredit->save();

        return $whatsappCredit;
    }

}
