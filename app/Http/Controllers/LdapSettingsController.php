<?php

namespace App\Http\Controllers;

use App\LdapSettings;
use Illuminate\Support\Facades\Gate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;

class LdapSettingsController extends Controller
{
    public function editSettings(Request $request)
    {
        if(Gate::allows("administration"))
        {
            $settings = LdapSettings::where('server', $request->input("id"))->first();
            $settings->server = $request->input("server");
            $settings->user = $request->input("user");
            $settings->domain = $request->input("domain");
            $settings->pwd = Crypt::encrypt($request->input("password"));
            $settings->user_id = $request->input("userid");
            $settings->struct_domain = $request->input("structdomain");
            $settings->save();

            return redirect()->route("listSettings");
        }
        else return response("You don't have permission to execute this action.", 403);
    }
}