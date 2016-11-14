<?php

namespace App\Providers;

use App\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Boot the authentication services for the application.
     *
     * @return void
     */
    public function boot()
    {
        // Here you may define how you wish users to be authenticated for your Lumen
        // application. The callback which receives the incoming request instance
        // should return either a User instance or null. You're free to obtain
        // the User instance via an API token or any other method necessary.

//        $this->app['auth']->viaRequest('api', function ($request) {
//            if ($request->input('api_token')) {
//                return User::where('api_token', $request->input('api_token'))->first();
//            }
//        });

        Gate::define('administration', function ($user) {
            return $user->role == "admin";
        });

        $this->app['auth']->viaRequest('api', function ($request) {
            if ($request->hasHeader('Authorization')) {
                $args = explode(' ', $request->header('Authorization'));
                if($args[0] == "Basic")
                {
                    $params = explode(':', base64_decode($args[1]));
                    $username = $params[0];
                    $password = $params[1];

                    $user = User::where('username', $username)->first();

                    if(is_null($user))
                    {
                        echo "No API user found with this username." . "<br>";
                        return NULL;
                    }
                    else if(Hash::check($password, $user->password)) return $user;
                    else
                    {
                        echo "Wrong password.";
                        return NULL;
                    }
                }
                else
                {
                    echo "Authentication method is not Basic. Only Basic method is accept." . "<br>";
                    return NULL;
                }
            }
            else
            {
                echo "The request doesn't has the Authentication header. Check your request header." . "<br>";
                return NULL;
            }
        });
    }
}
