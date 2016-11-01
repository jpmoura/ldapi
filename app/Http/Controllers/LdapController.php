<?php

namespace App\Http\Controllers;

use App\OAuth;
use App\LdapSettings;
use Illuminate\Http\Request;

class LdapController extends Controller
{

  private function bindToServer()
  {
    $settings = $settings = LdapSettings::first();
    #var_dump($settings);
      #echo $settings->server."\n";
      #echo $settings->domain."\n";
      #echo $settings->user."\n";
      #echo $settings->pwd."\n";
      #exit;
      $serverResource = ldap_connect($settings->server);

    if($serverResource != FALSE) {
      $isBinded = ldap_bind($serverResource, $settings->user . ',' . $settings->domain , $settings->pwd);

      if($isBinded == TRUE) return $serverResource;
      else abort(404, "Cannot authenticate to LDAP server. Verify your credentials!");
    }
    else abort(404, "No LDAP Server available.");
  }

    private function bindToServerUser($username,$password)
    {
        $settings = $settings = LdapSettings::first();
        $serverResource = ldap_connect($settings->server);
        #var_dump($settings);
        #exit;
        if($serverResource != FALSE) {
            $isBinded = ldap_bind($serverResource, $settings->user_id . '=' . $username.', ' . $settings->struct_domain, $password);

            if($isBinded == TRUE){ echo"It's me! ";  return $serverResource;}
            else abort(404, "Cannot authenticate to LDAP server. Verify your credentials!");
        }
        else abort(404, "No LDAP Server available.");
    }

  private function unbindFromServer($serverResource)
  {
    return ldap_unbind($serverResource);
  }

  public function findByBrPersonCPF($brPersonCPF)
  {
    $ldapServer = $this->bindToServer();

    $filter = "(" . 'uid' . "=" . $brPersonCPF . ")";
    $desiredAttributes = array('uid', 'cn', 'sn', 'mail', 'ou', 'telephoneNumber', 'userPassword', 'gidNumber', 'o', 'gecos');
    $searchResults = ldap_search($ldapServer, 'dc=ufop,dc=br', $filter, $desiredAttributes);
    $entries = ldap_get_entries($ldapServer, $searchResults);

    if ($entries['count'] > 0) {
      $response = array();

      $response['Primeiro Nome'] = $entries[0]['cn'][0];
      $response['Ultimo Nome'] = $entries[0]['sn'][0];
      $response['Nome Completo'] = $entries[0]['gecos'][0];
      $response['CPF'] = $entries[0]['uid'][0];
      $response['E-mail'] = $entries[0]['mail'][0];
      $response['Grupo'] = $entries[0]['ou'][0];
      $response['ID do Grupo'] = $entries[0]['gidnumber'][0];
      $response['Telefones'] = $entries[0]['telephonenumber'][0];
      $response['Local'] = $entries[0]['o'][0];
      $response['Senha'] = $entries[0]['userpassword'][0];

      $this->unbindFromServer($ldapServer);
      return response()->json($response);
    }
    else return response('User Not found', 404);
  }

  private function getAttributesOf($attributesArray, $brPersonCPF)
  {
    $ldapServer = $this->bindToServerUser();

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

  public function hexToStr($hex)
  {
    $string = '';
    for ($i = 0; $i < strlen($hex) - 1; $i+=2) {
      $string .= chr(hexdec($hex[$i] . $hex[$i + 1]));
    }
    return $string;
  }

  public function encodePassword($raw)
  {
    $password = base64_encode($this->hexToStr(md5($raw)));
    return "{md5}" . $password;
  }

  public function isPasswordValid($fromLDAP, $raw)
  {
    $encoded = $this->encodePassword($raw);
    return $fromLDAP == $encoded;
  }

  public function authenticate(Request $request)
  {
    $userCPF =  $request->input('brPersonCPF');
    $userRawPassword = $request->input('password');

    // buscar todos atributos do usuario (nome, telefone, email)
    $attributes = ['userpassword', 'gecos', 'mail', 'telephonenumber', 'o', 'gidnumber', 'ou'];
    $userDetails = $this->getAttributesOf($attributes, $userCPF);
    $isAuthenticate = $this->isPasswordValid($userDetails['userpassword'], $userRawPassword);
    $isAuthenticate = $this->bindToServerUser($userCPF, $userRawPassword);

    if($isAuthenticate == TRUE) {

      // reponder com os atributos
      $jsonResponse = array();
      $jsonResponse['nome'] = $userDetails['gecos'];
      $jsonResponse['email'] = $userDetails['mail'];
      $jsonResponse['telefone'] = $userDetails['telephonenumber'];
      $jsonResponse['grupo'] = $userDetails['ou'];
      $jsonResponse['idGrupo'] = $userDetails['gidnumber'];
      $jsonResponse['local'] = $userDetails['o'];

      return response()->json($jsonResponse);
    }
    else return response('User password do not match', 401);
  }

}
