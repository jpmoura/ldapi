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
    return response("LD(AP)I version 1.0.0");
});

$app->group(['middleware' => 'auth', 'namespace' => 'App\Http\Controllers'], function () use ($app) {

    /* Dashboard routes */
    $app->get('/admin', ['as' => 'getHome', 'uses' => 'PagesController@getHome']);

    $app->get('/add/user', ['as' => 'getAddUser', 'uses' => 'PagesController@getAddUser']);
    $app->get('/add/fields', ['as' => 'getAddField', 'uses' => 'PagesController@getAddLdapFields']);

    $app->post('/add/user', ['as' => 'doAddUser', 'uses' => 'UserController@addUser']);
    $app->post('/add/fields', ['as' => 'doAddField', 'uses' => 'LdapFieldsController@addField']);

    $app->get('/list/settings', ['as' => 'listSettings', 'uses' => 'PagesController@getListLdapSettings']);
    $app->get('/list/fields', ['as' => 'listFields', 'uses' => 'PagesController@getListLdapFields']);
    $app->get('/list/users', ['as' => 'listUser', 'uses' => 'PagesController@getListUsers']);

    $app->get('/edit/user/{id}', ['as' => 'getEditUser', 'uses' => 'PagesController@getEditUser']);
    $app->get('/edit/settings/{id}', ['as' => 'getEditLdapSettings', 'uses' => 'PagesController@getEditLdapSettings']);
    $app->get('/edit/fields/{id}', ['as' => 'getEditLdapFields', 'uses' => 'PagesController@getEditLdapField']);

    $app->post('/edit/user', ['as' => 'postEditUser', 'uses' => 'UserController@editUser']);
    $app->post('/edit/settings', ['as' => 'postEditSettings', 'uses' => 'LdapSettingsController@editSettings']);
    $app->post('/edit/fields', ['as' => 'postEditField', 'uses' => 'LdapFieldsController@editField']);

    $app->post('/delete/user', ['as' => 'deleteUser', 'uses' => 'UserController@deleteUser']);
    $app->post('/delete/fields', ['as' => 'deleteField', 'uses' => 'LdapFieldsController@deleteField']);

    /* API routes */
    $app->post('/aliases', ['as' => "getAliases", 'uses' => "PagesController@getAliasesList"]);
    $app->post('/auth', ['as' => "auth", 'uses' => "LdapController@authenticate"]);
    $app->post('/search', ['as' => "search", 'uses' => "LdapController@search"]);
    $app->post('/searchLikeLdap', ['as' => "searchLikeLdap", 'uses' => "LdapController@searchLikeLDAP"]);
});
