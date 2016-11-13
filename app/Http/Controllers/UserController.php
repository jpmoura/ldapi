<?php

namespace App\Http\Controllers;

use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function addUser(Request $request)
    {
        if(Gate::allows("administration"))
        {
            try
            {
                $user = User::create([
                    'username' => $request->input("username"),
                    'password' => Hash::make($request->input("password")),
                    'description' => $request->input("description"),
                    'role' => $request->input("role")
                ]);

                if(is_null($user)) $this->generateStatus("An error has occurred while saving saving user in database", "fail");
                else $this->generateStatus("User created.");
            }
            catch(\PDOException $exception)
            {
                $this->generateStatus($exception->getMessage(), "fail");
            }

            return redirect()->route("listUser");
        }
        else return response("You don't have permission to execute this action.", 403);
    }

    public function editUser(Request $request)
    {
        if(Gate::allows("administration"))
        {
            $user = User::where("username", $request->input("id"))->first();
            $user->username = $request->input("username");
            $user->description = $request->input("description");
            $user->role = $request->input("role");
            if($request->input("password") != "") $user->password = Hash::make($request->input("password"));
            $user = $user->save();

            if(is_null($user)) $this->generateStatus("An error has occurred while saving saving user in database", "fail");
            else $this->generateStatus("User updated.");

            return redirect()->route("listUser");
        }
        else return response("You don't have permission to execute this action.", 403);
    }

    public function deleteUser(Request $request)
    {
        if(Gate::allows("administration"))
        {
            User::destroy($request->input("id"));
            $this->generateStatus("User deleted.");
            return redirect()->route("listUser");
        }
        else return response("You don't have permission to execute this action.", 403);
    }

    private function generateStatus($message, $type = "success")
    {
        session_start();

        if($type = "success") $_SESSION["type"] = "success";
        else $_SESSION["type"] = "fail";

        $_SESSION["message"] = $message;

        return;
    }
}
