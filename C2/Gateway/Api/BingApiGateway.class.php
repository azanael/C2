<?php

class BingException extends Exception
{
    public function __construct($message, $code = 100)
    {
        parent::__construct($message, $code);
    }
}

class BingAuthorizationException extends BingException
{
    public function __construct($message, $code = 101)
    {
        parent::__construct($message, $code);
    }
}

class BingInvalidArgumentException extends BingException
{
    public function __construct($message, $code = 102)
    {
        parent::__construct($message, $code);
    }
}

class BingRuntimeException extends RuntimeException
{
    public function __construct($message, $code = 150)
    {
        parent::__construct($message, $code);
    }
}

/**
 * Bing API Gateway.
 *
 * Original: http://msdn.microsoft.com/en-us/library/ff512421.aspx#phpexample
 */
class BingApiGateway
{
    //Client ID of the application.
    private $clientID;
    //Client Secret key of the application.
    private $clientSecret;
    //OAuth Url.
    private static $authUrl = "https://datamarket.accesscontrol.windows.net/v2/OAuth2-13/";
    //Application Scope Url
    private static $scopeUrl = "http://api.microsofttranslator.com";
    //Application grant type
    private static $grantType = "client_credentials";
    
    public function __construct($clientID = null, $clientSecret = null)
    {
        if ($clientID !== null) {
            $this->clientID = $clientID;
        }
        if ($clientSecret !== null) {
            $this->clientSecret = $clientSecret;
        }
    }
    
    /**
     * Set the client keys.
     *
     * @param string $clientID
     * @param string $clientSecret
     */
    public function setClient($clientID, $clientSecret)
    {
        $this->clientID = $clientID;
        $this->clientSecret = $clientSecret;
    }
    
    /**
     * Get the access token.
     *
     * @return string.
     */
    public function getTokens()
    {
        if ($this->clientID === null || $this->clientSecret === null) {
            throw new BingInvalidArgumentException('clientID or clientSecret is empty.');
        }
        try {
            //Initialize the Curl Session.
            $ch = curl_init();
            //Create the request Array.
            $paramArr = array(
                 'grant_type'    => self::$grantType,
                 'scope'         => self::$scopeUrl,
                 'client_id'     => $this->clientID,
                 'client_secret' => $this->clientSecret
            );
            //Create an Http Query.//
            $paramArr = http_build_query($paramArr);
            //Set the Curl URL.
            curl_setopt($ch, CURLOPT_URL, self::$authUrl);
            //Set HTTP POST Request.
            curl_setopt($ch, CURLOPT_POST, TRUE);
            //Set data to POST in HTTP "POST" Operation.
            curl_setopt($ch, CURLOPT_POSTFIELDS, $paramArr);
            //CURLOPT_RETURNTRANSFER- TRUE to return the transfer as a string of the return value of curl_exec().
            curl_setopt ($ch, CURLOPT_RETURNTRANSFER, TRUE);
            //CURLOPT_SSL_VERIFYPEER- Set FALSE to stop cURL from verifying the peer's certificate.
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            //Execute the  cURL session.
            $strResponse = curl_exec($ch);
            //Get the Error Code returned by Curl.
            $curlErrno = curl_errno($ch);
            if ($curlErrno) {
                $curlError = curl_error($ch);
                throw new BingRuntimeException($curlError);
            }
            //Close the Curl Session.
            curl_close($ch);
            //Decode the returned JSON string.
            $objResponse = json_decode($strResponse);
            if ($objResponse->error){
                throw new BingAuthorizationException($objResponse->error_description);
            }
            return $objResponse->access_token;
        } catch (Exception $e) {
            throw new BingAuthorizationException($e->getMessage());
        }
    }
    
    /**
     * Translation text.
     *
     * @param string $text
     * @param string $toLanguage
     * @param string $fromLanguage
     * @throws BingException
     * @return string translated text
     */
    public function translate($text, $toLanguage, $fromLanguage = null)
    {
        //Create the authorization Header string.
        $authHeader = "Authorization: Bearer ". $this->getTokens();
        
        //Set the params.
        $contentType  = 'text/plain';
        
        $params = "text=" . urlencode($text) . "&to=" . $toLanguage . '&contentType=' . $contentType;
        if ($fromLanguage !== null) {
            $params .= "&from=" . $fromLanguage;
        }
        $translateUrl = "http://api.microsofttranslator.com/v2/Http.svc/Translate?$params";
        try {
            $curlResponse = $this->_curlRequest($translateUrl, $authHeader);
            
            //Interprets a string of XML into an object.
            $xmlObj = simplexml_load_string($curlResponse);
            foreach((array)$xmlObj[0] as $val){
                $translatedStr = $val;
            }
        } catch (Exception $e) {
            throw new BingException($e->getMessage());
        }
        return $translatedStr;
    }
    
    private function _curlRequest($url, $authHeader)
    {
        //Initialize the Curl Session.
        $ch = curl_init();
        //Set the Curl url.
        curl_setopt ($ch, CURLOPT_URL, $url);
        //Set the HTTP HEADER Fields.
        curl_setopt ($ch, CURLOPT_HTTPHEADER, array($authHeader,"Content-Type: text/xml"));
        //CURLOPT_RETURNTRANSFER- TRUE to return the transfer as a string of the return value of curl_exec().
        curl_setopt ($ch, CURLOPT_RETURNTRANSFER, TRUE);
        //CURLOPT_SSL_VERIFYPEER- Set FALSE to stop cURL from verifying the peer's certificate.
        curl_setopt ($ch, CURLOPT_SSL_VERIFYPEER, False);
        //Execute the  cURL session.
        $curlResponse = curl_exec($ch);
        //Get the Error Code returned by Curl.
        $curlErrno = curl_errno($ch);
        if ($curlErrno) {
            $curlError = curl_error($ch);
            throw new BingRuntimeException($curlError);
        }
        //Close a cURL session.
        curl_close($ch);
        return $curlResponse;
    }
}