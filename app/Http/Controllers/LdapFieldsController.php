<?php

namespace App\Http\Controllers;

use App\LdapFields;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class LdapFieldsController extends Controller
{
    public function addField(Request $request)
    {
        if(Gate::allows("administration"))
        {
            LdapFields::create([
                'name' => $request->input("name"),
                'alias' => $request->input("alias")
            ]);

            return redirect()->route("listFields");
        }
        else return response("You don't have permission to execute this action.", 403);
    }

    public function editField(Request $request)
    {
        if(Gate::allows("administration"))
        {
            $field = LdapFields::find($request->input("id"));
            $field->name = $request->input("name");
            $field->alias = $request->input("alias");
            $field->save();

            return redirect()->route("listFields");
        }
        else return response("You don't have permission to execute this action.", 403);
    }

    public function deleteField(Request $request)
    {
        if(Gate::allows("administration"))
        {
            LdapFields::destroy($request->input("id"));
            return redirect()->route("listFields");
        }
        else return response("You don't have permission to execute this action.", 403);
    }
}