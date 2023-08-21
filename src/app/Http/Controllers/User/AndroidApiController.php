<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\AndroidApi;
use App\Models\AndroidApiSimInfo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AndroidApiController extends Controller
{

    public function index()
    {
        $title = "Android Gateway list";
        $androids = AndroidApi::where('user_id', auth()->user()->id)->orderBy('id', 'DESC')->paginate(paginateNumber());
        return view('user.android.index', compact('title', 'androids'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required',
            'password' => 'required|confirmed',
            'status' => 'required|in:1,2'
        ]);

        AndroidApi::create([
            'name' => $request->name,
            'user_id' => auth()->user()->id,
            'show_password' => $request->password,
            'password' =>  Hash::make($request->password),
            'status' => $request->status,
        ]);
        $notify[] = ['success', 'New Android Gateway has been created'];
        return back()->withNotify($notify);
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'name' => 'required',
            'password' => 'required',
            'status' => 'required|in:1,2'
        ]);
        $androidApi = AndroidApi::where('user_id', auth()->user()->id)->where('id', $request->id)->firstOrFail();
        $androidApi->update([
            'name' => $request->name,
            'user_id' => auth()->user()->id,
            'show_password' => $request->password,
            'password' => Hash::make($request->password),
            'status' => $request->status,
        ]);
        $notify[] = ['success', 'Android Gateway has been updated'];
        return back()->withNotify($notify);
    }

    public function simList($id)
    {
        $android = AndroidApi::where('user_id', auth()->user()->id)->where('id', $id)->firstOrFail();
        $title = ucfirst($android->name)." api gateway sim list";
        $simLists = AndroidApiSimInfo::where('android_gateway_id', $android->id)->latest()->with('androidGatewayName')->paginate(paginateNumber());
        return view('user.android.sim', compact('title', 'android', 'simLists'));
    }

    public function delete(Request $request)
    {
        $android = AndroidApi::where('user_id', auth()->user()->id)->where('id', $request->input('id'))->firstOrFail();
        $simLists = AndroidApiSimInfo::where('android_gateway_id', $android->id)->delete();
        $android->delete();

        $notify[] = ['success', 'Android Gateway has been deleted'];
        return back()->withNotify($notify);
    }

    public function simNumberDelete(Request $request)
    {
        $simList = AndroidApiSimInfo::where('id', $request->id)->firstOrFail();
        $android = AndroidApi::where('user_id', auth()->user()->id)->where('id', $simList->android_gateway_id)->firstOrFail();
        $simList->delete();

        $notify[] = ['success', 'Android Gateway sim has been deleted'];
        return back()->withNotify($notify);
    }
}
