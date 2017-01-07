<?php

namespace App\Http\Controllers;

use App\LdapFields;
use App\LdapSettings;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;

class  LdapController extends Controller
{
    /**
     * Binds to AD server using the user saved in settings table present in database.
     *
     * @return resource The resource related to LDAP connection or void if fails.
     */
    private function bindToServer()
    {
        $settings = LdapSettings::first();
        $settings->pwd = Crypt::decrypt($settings->pwd);

        $serverResource = @ldap_connect($settings->server);

        if($serverResource != FALSE) {
            $isBind = ldap_bind($serverResource, $settings->user . ',' . $settings->domain , $settings->pwd);

            if($isBind == TRUE) return $serverResource;
            else abort(401, "Invalid credentials for reader user.");
        }
        else abort(503, "LDAP Server isn't available.");
    }

    /**
     * Checks the credentials of a specific user.
     *
     * @param $username User name
     * @param $password User password
     * @return bool|resource FALSE if the user's credentials are invalid or the connection resource if they are valid.
     */
    private function checkUserCredentials($username, $password)
    {
        $settings = LdapSettings::first();
        $serverResource = @ldap_connect($settings->server);

        if($serverResource != FALSE) {
            $isBind = @ldap_bind($serverResource, $settings->user_id . '=' . $username.', ' . $settings->struct_domain, $password);

            if($isBind == TRUE) return $serverResource;
            else return FALSE;
        }
        else abort(503, "No LDAP Server available.");
    }

    /**
     * Unbinds from AD server.
     * @param $serverResource Resource related to LDAP connection
     * @return bool True on success and False on fail.
     */
    private function unbindFromServer($serverResource)
    {
        return @ldap_unbind($serverResource);
    }

    /**
     * Get all desired attributes of a specific user.
     *
     * @param $attributesArray Array with aliases of all desired attributes
     * @param $userIdField User unique identification
     * @return array Array with all desired attributes
     */
    private function getAttributesOf($attributesArray, $userID)
    {
        $ldapServer = $this->bindToServer();

        $settings = LdapSettings::first();

        $filter = "(" . $settings->user_id . "=" . $userID . ")";

        foreach ($attributesArray as $attribute) $adAttributes[] = $this->aliasToLdapAttribute($attribute);

        $searchResults = ldap_search($ldapServer, $settings->domain, $filter, $adAttributes);
        $entries = ldap_get_entries($ldapServer, $searchResults);

        //dd($entries);

        foreach ($adAttributes as $attribute) $response[$this->ldapAttributeToAlias($attribute)] = $entries[0][$attribute][0];

        $this->unbindFromServer($ldapServer);

        return $response;

    }

    /**
     * Authenticates a user or not.
     *
     * @param Request $request The request with inputs from user.
     * @return \Illuminate\Http\JsonResponse|void A JSON if user is authenticated or void if not.
     */
    public function authenticate(Request $request)
    {
        $user =  $request->input('user');
        $password = $request->input('password');
        $isAuthenticate = $this->checkUserCredentials($user, $password);

        if($isAuthenticate != FALSE)
        {
            $attributes = $request->input('attributes');

            if(isset($attributes))
            {
                $userDetails = $this->getAttributesOf($attributes, $user);
                return response()->json($userDetails);
            }
            else return response()->json(['authenticated' => TRUE]);
        }
        else return abort(401, 'Invalid credentials.');
    }

    /**
     * Search in AD server using raw LDAP filter expressions.
     *
     * @param Request $request Request with inputs that have the raw parameters.
     * @return \Illuminate\Http\JsonResponse|void A JSON with all wanted attributes or void if parameters are invalid.
     */
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

                foreach ($fields as $row) $ldapNames[$row->alias] = $row->name; // gera um array usando apelido como index e valor como o nome do campo no ldap
                foreach ($attributesArray as $attribute) { // Para cada apelido de campo
                    if(isset($ldapNames[$attribute])){ // se ele existir no array associativo de apelido com nome original
                        $desiredAttributes[] = $ldapNames[$attribute]; // cria um array só com os nomes originais do ldap
                        $jsonAttributes[$attribute] = $ldapNames[$attribute]; // cria um array associativo somente com os campos passados como parametro
                    }
                    else abort(400, "Attribute not found: " . $attribute);
                }

                $desiredAttributes = array();
                $searchResults = @ldap_search($ldapServer, $settings->domain, $filter, $desiredAttributes);
                if($searchResults == FALSE) return abort(400, "Bad search filter.");
                else {
                    $entries = @ldap_get_entries($ldapServer, $searchResults);

                    if($entries == FALSE) return abort(500, "Error occurred while getting all entries.");
                    else {

                        $allEntries = array();
                        unset($entries["count"]);

                        foreach ($entries as $entry) {
                            $oneJsonObject = array();

                            foreach ($jsonAttributes as $alias => $attribute)
                                $oneJsonObject[$alias] = $entry[$attribute][0];

                            $allEntries[] = $oneJsonObject;
                        }

                        $this->unbindFromServer($ldapServer);
                        return response()->json($allEntries);
                    }
                }
            }
            else abort(400, "You need to inform the attributes that you want.");
        }
        else abort(400, "You need to inform a filter.");
    }

    /**
     * Search in AD server using API syntax.
     *
     * @param Request $request Request with all necessary inputs.
     * @return \Illuminate\Http\JsonResponse A JSON with all desired returning attributes converted to alias or void if something fail.
     */
    public function search(Request $request)
    {
        if($request->isJson()) {
            $searchRequest = $request->json();
            $error = NULL;

            // Verificação das varáveis necessárias
            $rawBaseConnector = $searchRequest->get("baseConnector");
            if(is_null($rawBaseConnector)) $error = "base connector";

            $rawFilters = $searchRequest->get('filters');
            if(is_null($rawFilters)) $error = "filters";

            $rawDesiredAttributes = $searchRequest->get('attributes');
            if(is_null($rawDesiredAttributes)) $error = "returning attributes";

            $searchBase = $searchRequest->get('searchBase');
            if(is_null($searchBase)) $searchBase = LdapSettings::first()->domain;

            if(isset($error)) abort(400, "You need to inform the " . $error . ".If you informed, check if the JSON is well formed.");

            // Formatação do filtro de pesquisa
            $baseConnector = $this->getBinaryConnector($searchRequest->get('baseConnector'));
            $decodedSearchFilter = '(' . $baseConnector;
            foreach ($rawFilters as $filter) $decodedSearchFilter .= $this->parseSearchFilter($filter);
            $decodedSearchFilter .=  ')';

            // Transformação dos apelidos dos atributos para o nome dos atributos no servidor LDAP
            $desiredAttributes = $this->convertToLdapAttributes($rawDesiredAttributes);

            $ldapServer = $this->bindToServer();
            $searchResults = @ldap_search($ldapServer, $searchBase, $decodedSearchFilter, $desiredAttributes);
            if($searchResults == FALSE) return response("Bad search filter.", 406);
            else
            {
                $entries = @ldap_get_entries($ldapServer, $searchResults);
                $this->unbindFromServer($ldapServer);

                if($entries == FALSE) return abort(500, "Error occurred while getting all entries.");
                else {
                    $allEntries["count"] = $entries["count"]; // Quantidade de registros encontrados
                    unset($entries["count"]);

                    $allEntries["ldapSearch"] = $decodedSearchFilter; // Como o filtro foi gerado

                    foreach ($entries as $entry) {
                        foreach ($desiredAttributes as $ldapAttribute)
                        {
                            $alias = $this->ldapAttributeToAlias($ldapAttribute);
                            if(isset($entry[$ldapAttribute])) $oneJsonObject[$alias] = $entry[$ldapAttribute][0];
                            else $oneJsonObject[$alias] = "NULL";
                        }

                        $allEntries["result"][] = $oneJsonObject;
                    }

                    return response()->json($allEntries);
                }
            }
        }
        else abort(406, "Request must be in JSON format. Check the Content-type HTTP header of your request.");
    }

    /**
     * Converts an array of alias attributes into an array of AD attributes.
     *
     * @param $desiredAttributes Array of aliases
     * @return array Array of AD attributes
     */
    private function convertToLdapAttributes($desiredAttributes)
    {
        $convertedAttributes = array();
        foreach ($desiredAttributes as $attributeAlias) $convertedAttributes[] = $this->aliasToLdapAttribute($attributeAlias);
        return $convertedAttributes;
    }

    /**
     * Parses one pf the user's filters in a LDAP representation.
     *
     * @param $filter Raw filter from user request
     * @return string LDAP representation of user filter
     */
    private function parseSearchFilter($filter)
    {
        // Se o filtro tiver mais de uma entrada e uma delas for o operador, abre-se o parêntese da composição
        if(count($filter) > 1 && array_key_exists("operator", $filter)) $ldapEncodedFilter = "(";
        else $ldapEncodedFilter = "";

        // Se existir o operador, então é composição de filtro, senão o atriburo será concatenado ao conector base
        if(isset($filter['operator'])) {
            $operator = $this->getBinaryConnector($filter["operator"]);

            if($operator == '!' && count($filter) > 2) abort(400, "NOT operator MUST have only one parameter.");
            else
            {
                unset($filter["operator"]);
                $ldapEncodedFilter .= $operator; // (& ou (| ou (! -> abertura do filtro
            }
        }

        $keys = array_keys($filter);
        foreach ($keys as $key) $ldapEncodedFilter .= $this->parseMatchRule($key, $filter[$key]);

        if(count($filter) > 1 && array_key_exists("operator", $filter)) $ldapEncodedFilter .= ')'; // fecha a composição

        return $ldapEncodedFilter;
    }

    /**
     * Get the AD field of an alias.
     *
     * @param $alias Alias of a LDAP attribute
     * @return string The attribute name in  server
     */
    private function aliasToLdapAttribute($alias)
    {
        $ldapField = LdapFields::where('alias', $alias)->first();
        if(is_null($ldapField)) abort(400, "Alias '" . $alias . "' not found. Check for typo.");
        else return $ldapField->name;
    }

    /**
     * Get the alias of an AD field.
     *
     * @param $ldapAttribute Attribute name in LDAP server
     * @return Alias of LDAP attribute
     */
    private function ldapAttributeToAlias($ldapAttribute)
    {
        $ldapField = LdapFields::where('name', $ldapAttribute)->first();
        if(is_null($ldapField)) abort(500, "No alias for for attribute '" . $ldapAttribute . "' not found. Contact webmaster and check for typo in database.");
        else return $ldapField->alias;
    }

    /**
     * Parses the match rule of a filter.
     *
     * @param $key
     * @param $rawFilter
     * @return string
     */
    private function parseMatchRule($key, $rawFilter)
    {
        $ldapAttribute = $this->aliasToLdapAttribute($key);
        $matchOperator = $this->getMatchOperator($rawFilter[0]);
        return '(' . $ldapAttribute . $matchOperator . $rawFilter[1] . ')';
    }

    /**
     * Get the match operator of a filter.
     *
     * @param $operator Operator from request body
     * @return null|string Null if is a nonexistent operator or its value in LDAP filter syntax
     */
    private function getMatchOperator($operator)
    {
        if(strcasecmp ($operator, "equals") == 0) return "=";
        elseif(strcasecmp ($operator, "present") == 0) return "*=";
        elseif(strcasecmp ($operator, "approximately") == 0) return "~=";
        elseif(strcasecmp ($operator, "lessOrEquals") == 0) return "<=";
        elseif(strcasecmp ($operator, "greaterOrEquals") == 0) return ">=";
        elseif(strcasecmp ($operator, "lessThan") == 0) return "<";
        elseif(strcasecmp ($operator, "greaterThan") == 0) return ">";
        else abort(400, "No LDAP operator for '" . $operator . "' found. Check for typo.");
    }

    /**
     * Get the connector between attributes of a filter.
     *
     * @param $connector
     * @return null|string Null if is a nonexistent connector or its value in LDAP filter syntax
     */
    private function getBinaryConnector($connector)
    {
        if(strcasecmp ($connector, "and") == 0) return '&';
        elseif(strcasecmp ($connector, "or") == 0) return '|';
        elseif(strcasecmp ($connector, "not") == 0) return '!';
        else abort(400, "No LDAP connector for '" . $connector . "' found. Check for typo.");
    }
}
