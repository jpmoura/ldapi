<?php

namespace App\Http\Controllers;

use App\LdapFields;
use App\LdapSettings;
use App\User;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Gate;

class PagesController extends Controller
{
    public function getHome()
    {
        if(Gate::allows("administration")) return response(view('pages.home'));
        else return response("You don't have permission for access the control panel.", 401);
    }

    public function getListLdapSettings()
    {
        if(Gate::allows("administration")) return response(view('actions.list.ldapsettings')->with('settings', LdapSettings::first()));
        else return response("You don't have permission for access the control panel.", 401);
    }

    public function getListLdapFields()
    {
        if(Gate::allows("administration")) return response(view('actions.list.ldapfields')->with('fields', LdapFields::all()));
        else return response("You don't have permission for access the control panel.", 401);
    }

    public function getListUsers()
    {
        if(Gate::allows("administration")) return response(view('actions.list.users')->with('users', User::all()));
        else return response("You don't have permission for access the control panel.", 401);
    }

    public function getAddUser()
    {
        if(Gate::allows("administration")) return response(view('actions.add.user'));
        else return response("You don't have permission for access the control panel.", 401);
    }

    public function getAddLdapFields()
    {
        if(Gate::allows("administration")) return response(view('actions.add.ldapfields'));
        else return response("You don't have permission for access the control panel.", 401);
    }

    public function getEditUser($id)
    {
        if(Gate::allows("administration"))
        {
            $user = User::where('username', base64_decode($id))->first();

            if(is_null($user)) return response("No user found with this ID.", 404);
            else return response(view('actions.edit.user')->with('user', $user));
        }
        else return response("You don't have permission for access the control panel.", 401);
    }

    public function getEditLdapSettings($id)
    {
        if(Gate::allows("administration"))
        {
            $settings = LdapSettings::where('server', base64_decode($id))->first();
            $settings->pwd = Crypt::decrypt($settings->pwd);

            if(is_null($settings)) return response("No settings found.", 404);
            else return response(view('actions.edit.ldapsettings')->with('settings', $settings));
        }
        else return response("You don't have permission for access the control panel.", 401);
    }

    public function getEditLdapField($id)
    {
        if(Gate::allows("administration"))
        {
            $field = LdapFields::where('name', base64_decode($id))->first();

            if(is_null($field)) return response("No field found with this ID.", 404);
            else return response(view('actions.edit.ldapfields')->with('field', $field));
        }
        else return response("You don't have permission for access the control panel.", 401);
    }

    public function getAliasesList()
    {
        if(Gate::allows("administration"))
        {
            $fields = LdapFields::all();

            foreach ($fields as $field) $aliases[] = $field->alias;

            return response()->json($aliases);
        }
        else return response("You don't have permission for access the control panel.", 401);
    }
}