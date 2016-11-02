<?php

namespace App\Http\Controllers;

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

  private function getAttributesOf($attributesArray, $brPersonCPF)
  {
    $ldapServer = $this->bindToServer();

    $filter = "(" . 'uid' . "=" . $brPersonCPF . ")";
    $desiredAttributes = $attributesArray;
    $searchResults = ldap_search($ldapServer, 'dc=ufop,dc=br', $filter, $desiredAttributes);
    $entries = ldap_get_entries($ldapServer, $searchResults);

    if ($entries['count'] > 0) {
      $response = array();

      foreach ($desiredAttributes as $attribute) {
        $response[$attribute] = $entries[0][$attribute][0];
      }

      $this->unbindFromServer($ldapServer);
      return $response;
    }
    else return response('User Not found', 404);;
  }

  public function authenticate(Request $request)
  {
    $userCPF =  $request->input('brPersonCPF');
    $userRawPassword = $request->input('password');
    $isAuthenticate = $this->checkUserCredentials($userCPF, $userRawPassword);

    if($isAuthenticate != FALSE) {
      $attributes = ['userpassword', 'gecos', 'mail', 'telephonenumber', 'o', 'gidnumber', 'ou'];
      $userDetails = $this->getAttributesOf($attributes, $userCPF);

      $jsonResponse = array();
      $jsonResponse['nome'] = $userDetails['gecos'];
      $jsonResponse['email'] = $userDetails['mail'];
      $jsonResponse['telefone'] = $userDetails['telephonenumber'];
      $jsonResponse['grupo'] = $userDetails['ou'];
      $jsonResponse['idGrupo'] = $userDetails['gidnumber'];
      $jsonResponse['local'] = $userDetails['o'];

      return response()->json($jsonResponse);
    }
    else return response('Invalid credentials.', 401);
  }
}
