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
            $isBind = ldap_bind($serverResource, $settings->user . ',' . $settings->domain , $settings->pwd);

            if($isBind == TRUE) return $serverResource;
            else return response("Invalid credentials for reader user.", 401);
        }
        else return response("No LDAP Server available.", 404);
    }

    private function checkUserCredentials($username, $password)
    {
        $settings = LdapSettings::first();
        $serverResource = @ldap_connect($settings->server);

        if($serverResource != FALSE) {
            $isBind = @ldap_bind($serverResource, $settings->user_id . '=' . $username.', ' . $settings->struct_domain, $password);

            if($isBind == TRUE) return $serverResource;
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

        $settings = LdapSettings::first();
        $fields = LdapFields::all();

        foreach ($fields as $row) $ldapNames[$row->nickname] = $row->ldapname;

        $filter = "(" . $settings->user_id . "=" . $userIdField . ")";

        $desiredAttributes = array();
        foreach ($attributesArray as $attribute) {
            if(isset($ldapNames[$attribute])){
                $desiredAttributes[] = $ldapNames[$attribute];
                $responseAttributes[$attribute] = $ldapNames[$attribute];
            }
            else return array(FALSE, $attribute);
        }

        $searchResults = ldap_search($ldapServer, $settings->domain, $filter, $desiredAttributes);
        $entries = ldap_get_entries($ldapServer, $searchResults);
        $responseAttributes = array();
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

            if(isset($attributes)) {
                $userDetails = $this->getAttributesOf($attributes, $user);

                if(!$userDetails[0]) return response('Invalid requested fields/attributes. Invalid field: '.$userDetails[1], 404);
                else {
                    $jsonResponse = $userDetails[1];
                    return response()->json($jsonResponse);
                }
            }
            else return response("Ok", 200);
        }
        else return response('Invalid credentials.', 401);
    }

    public function searchLikeLDAP(Request $request)
    {
        $filter = $request->input("filter");

        if(isset($filter)) {

            $attributesArray = $request->input("attributes");
            if(!is_null($attributesArray)) {

                $ldapServer = $this->bindToServer();
                $settings = LdapSettings::first();

                // Tradução dos campos para o nome correto no LDAP
                $fields = LdapFields::all(); // tabela de campos com apelidos
                $desiredAttributes = array();
                $jsonAttributes = array();

                foreach ($fields as $row) $ldapNames[$row->nickname] = $row->ldapname; // gera um array usando apelido como index e valor como o nome do campo no ldap
                foreach ($attributesArray as $attribute) { // Para cada apelido de campo
                    if(isset($ldapNames[$attribute])){ // se ele existir no array associativo de apelido com nome original
                        $desiredAttributes[] = $ldapNames[$attribute]; // cria um array só com os nomes originais do ldap
                        $jsonAttributes[$attribute] = $ldapNames[$attribute]; // cria um array associativo somente com os campos passados como parametro
                    }
                    else return response("Attribute not found: " . $attribute, 404);
                }

                $desiredAttributes = array();
                $searchResults = @ldap_search($ldapServer, $settings->domain, $filter, $desiredAttributes);
                if($searchResults == FALSE) return response("Bad search filter.", 404);
                else {
                    $entries = @ldap_get_entries($ldapServer, $searchResults);

                    if($entries == FALSE) return response("Error occured while getting all entries.", 404);
                    else {

                        $allEntries = array();
                        unset($entries["count"]);
                        //dd($entries);
                        foreach ($entries as $entry) {
                            $oneJsonObject = array();
                            //var_dump($entry);
                            foreach ($jsonAttributes as $nickName => $attribute) $oneJsonObject[$nickName] = $entry[$attribute][0];
                            $allEntries[] = $oneJsonObject;
                        }

                        $this->unbindFromServer($ldapServer);
                        return response()->json($allEntries);
                    }
                }
            }
            else return response("You need to inform the attributes that you want.", 404);
        }
        else return response("You need to inform a filter.", 404);
    }

    public function search(Request $request)
    {
        return response("To do", 200);
    }
}
