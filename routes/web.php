<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

$app->get('/', function () use ($app) {
    return $app->version();
});

$app->post('/auth', ['as' => "Auth", 'uses' => "LdapController@authenticate"]);
$app->post('/search', ['as' => "Search", 'uses' => "LdapController@search"]);
$app->post('/searchLikeLdap', ['as' => "SearchLikeLdap", 'uses' => "LdapController@searchLikeLDAP"]);
