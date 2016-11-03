<?php

namespace App\Http\Controllers;

use App\LdapFields;
use DB;
use App\LdapSettings;
use Illuminate\Http\Request;

class LdapController extends Controller
{
    private function bindToServer()
    {
        $settings = LdapSettings::first();
        $serverResource = @ldap_connect($settings->server);

        if($serverResource != FALSE) {
            $isBinded = ldap_bind($serverResource, $settings->user . ',' . $settings->domain , $settings->pwd);

            if($isBinded == TRUE) return $serverResource;
            else return response("Invalid credentials for reader user.", 401);
        }
        else return response("No LDAP Server available.", 404);
    }

    private function checkUserCredentials($username, $password)
    {
        $settings = LdapSettings::first();
        $serverResource = @ldap_connect($settings->server);

        if($serverResource != FALSE) {
            $isBinded = @ldap_bind($serverResource, $settings->user_id . '=' . $username.', ' . $settings->struct_domain, $password);

            if($isBinded == TRUE) return $serverResource;
            else return FALSE;
        }
        else return response("No LDAP Server available.", 404);
    }

    private function unbindFromServer($serverResource)
    {
        return @ldap_unbind($serverResource);
    }

    private function getAttributesOf($attributesArray, $userIdField)
    {
        $ldapServer = $this->bindToServer();

        $settings = LdapSettings::First();
        $fields = LdapFields::All();

        foreach ($fields as $row) $ldapNames[$row->nickname] = $row->ldapname;

        $filter = "(" . $settings->user_id . "=" . $userIdField . ")";

        foreach ($attributesArray as $attribute) {
            if(isset($ldapNames[$attribute])){
                $desiredAttributes[] = $ldapNames[$attribute];
                $responseAttributes[$attribute] = $ldapNames[$attribute];
            }
            else return array(FALSE, $attribute);
        }

        $searchResults = ldap_search($ldapServer, $settings->domain, $filter, $desiredAttributes);
        $entries = ldap_get_entries($ldapServer, $searchResults);

        if ($entries['count'] > 0) {
            $response = array();
            foreach ($responseAttributes as $nickName => $attribute) {
                $response[$nickName] = $entries[0][$attribute][0];
            }

            $this->unbindFromServer($ldapServer);
            return array(TRUE,$response);
        }
    }

    public function authenticate(Request $request)
    {
        $user =  $request->input('user');
        $userRawPassword = $request->input('password');
        $isAuthenticate = $this->checkUserCredentials($user, $userRawPassword);

        if($isAuthenticate != FALSE) {
            $attributes = $request->input('attributes');
            $userDetails = $this->getAttributesOf($attributes, $user);

            if(!$userDetails[0]) return response('Invalid requested fields/attributes. Invalid field: '.$userDetails[1], 404);
            else {
                $jsonResponse = $userDetails[1];
                return response()->json($jsonResponse);
            }
        }
        else return response('Invalid credentials.', 401);
    }
}
