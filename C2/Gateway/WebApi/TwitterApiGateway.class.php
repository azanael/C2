<?php
require_once 'TwitterApiException.class.php';

/**
 * Provides access to Twitter.
 */
class TwitterApiGateway
{
    const API_URL = 'https://api.twitter.com/';
    const API_VERSION = '1.1';
    
    /**
     * Application Only Auth
     */
    const AUTHENTICATION_APPLICATION_ONLY = 1;

    /**
     * OAuth Signed
     */
    const AUTHENTICATION_3_LEGGED = 2;
    
    const REQUEST_METHOD_GET = 'GET';
    const REQUEST_METHOD_POST = 'POST';
    
    private $_consumerKey;
    private $_consumerSecret;
    
    private $_oauthToken;
    private $_oauthTokenSecret;
    
    private $_authenticationBy = self::AUTHENTICATION_APPLICATION_ONLY;
    
    public function __construct($consumerKey = null, $consumerSecret = null)
    {
        $this->_consumerKey = $consumerKey;
        $this->_consumerSecret = $consumerSecret;
    }
    
    /**
     * Set consumer key and consumer secret.
     *
     * @param string $consumerKey
     * @param string $consumerSecret
     */
    public function setConsumer($consumerKey, $consumerSecret)
    {
        $this->_consumerKey = $consumerKey;
        $this->_consumerSecret = $consumerSecret;
    }
    
    /**
     * Set oauth token and oauth token secret.
     *
     * @param string $oauthToken
     * @param string $oauthTokenSecret
     */
    public function setOauthToken($oauthToken, $oauthTokenSecret = null)
    {
        $this->_oauthToken = $oauthToken;
        if ($oauthTokenSecret) {
            $this->_oauthTokenSecret = $oauthTokenSecret;
        }
    }
    
    /**
     * Set authentication method.
     *
     * @param int $authenticationBy
     */
    public function setAuthenticationBy($authenticationBy = self::AUTHENTICATION_APPLICATION_ONLY)
    {
        $this->_authenticationBy = $authenticationBy;
    }

    /**
     * oauth/request_token
     *
     * Note: Set Callback URL in your twitter application first.
     * If none or empty, this method throws exception. (HTTPCODE=401)
     *
     * @category oauth
     * @param string $oauthCallback
     * @throws TwitterApiException
     * @return array 'oauth_token' and 'oauth_token_secret'
     */
    public function oauth_request_token($oauthCallback)
    {
        $url = self::API_URL . 'oauth/request_token';
        $headers = array();
        $headers[] = 'Authorization: ' . $this->_getOauthHeader($url, $params, self::REQUEST_METHOD_POST, $oauthCallback);
        $response = $this->_request($url, $params, $headers, self::REQUEST_METHOD_POST);
        parse_str($response, $result);
        if ($result['oauth_callback_confirmed'] !== 'true') {
            throw new TwitterApiException('oauth_callback_confirmed was not true.');
        }
        return array('oauth_token' => $result['oauth_token'], 'oauth_token_secret' => $result['oauth_token_secret']);
    }
    
    /**
     * oauth/authenticate
     *
     * @category oauth
     * @param string $oauthToken oauth_token returned from request_token API.
     * @param bool $forceLogin "Forces the user to enter their credentials to ensure the correct users account is authorized."
     * @param string $screenName "Prefills the username input box of the OAuth login screen with the given value."
     * @return string Authenticate URL.
     */
    public function oauth_authenticate($oauthToken, $forceLogin = true, $screenName = null)
    {
        $params['oauth_token'] = $oauthToken;
        if ($forceLogin !== true) {
            $params['force_login'] = false;
        }
        if ($screenName !== null) {
            $params['screen_name'] = $screenName;
        }
        return self::API_URL . 'oauth/authenticate?' . http_build_query($params);
    }
    
    /**
     * oauth/access_token
     *
     * @category oauth
     * @param string $oauthToken oauth_token included in client's redirect response.
     * @param string $oauthVerifier oauth_verifier included in client's redirect response.
     * @return array 'oauth_token', 'oauth_token_secret', 'user_id' and 'screen_name'
     */
    public function oauth_access_token($oauthToken, $oauthVerifier)
    {
        $url = self::API_URL . 'oauth/access_token';
        $this->setOauthToken($oauthToken);
        $params['oauth_verifier'] = $oauthVerifier;
        $headers = array();
        $headers[] = 'Authorization: ' . $this->_getOauthHeader($url, $params, self::REQUEST_METHOD_POST);
        $response = $this->_request($url, $params, $headers, self::REQUEST_METHOD_POST);
        parse_str($response, $result);
        return array(
            'oauth_token' => $result['oauth_token'],
            'oauth_token_secret' => $result['oauth_token_secret'],
            'user_id' => $result['user_id'],
            'screen_name' => $result['screen_name']
        );
    }
    
    /**
     * users/lookup
     *
     * @param int $userId
     * @param string $screenName
     * @param bool $includeEntities
     * @return array
     */
    public function users_lookup($userId = null, $screenName = null, $includeEntities = false)
    {
        $api = 'users/lookup';
        if ($userId !== null) {
            $params['user_id'] = $userId;
        }
        if ($screenName !== null) {
            $params['screen_name'] = $screenName;
        }
        if ($includeEntities === true) {
            $params['include_entities'] = true;
        }
        return $this->_requestApi($api, $params, self::REQUEST_METHOD_GET);
    }
    
    /**
     * statuses/oembed
     *
     * @param int $id
     * @return array
     */
    public function statuses_oembed($id)
    {
        $api = 'statuses/oembed';
        $url = "https://twitter.com/twitter/status/$id";
        $params = compact('id', 'url');
        return $this->_requestApi($api, $params, self::REQUEST_METHOD_GET);
    }
    
    /**
     * statuses/update
     *
     * @category statuses
     * @param string $status
     * @param string $inReplyToStatusId
     * @param float $lat
     * @param float $long
     * @param int $placeId
     * @param bool $displayCoordinates
     * @param bool $trimUser
     * @return array
     */
    public function statuses_update($status, $inReplyToStatusId = null, $lat = null, $long = null, $placeId = null, $displayCoordinates = null, $trimUser = null)
    {
        $api = 'statuses/update';
        $params['status'] = $status;
        if ($inReplyToStatusId) {
            $params['in_reply_to_status_id'] = $inReplyToStatusId;
        }
        if ($lat) {
            $params['lat'] = $lat;
        }
        if ($long) {
            $params['long'] = $long;
        }
        if ($placeId) {
            $params['place_id'] = $placeId;
        }
        if ($displayCoordinates) {
            $params['display_coordinates'] = $displayCoordinates;
        }
        if ($trimUser) {
            $params['trim_user'] = $trimUser;
        }
        return $this->_requestApi($api, $params, self::REQUEST_METHOD_POST, self::AUTHENTICATION_3_LEGGED);
    }
    
    /**
     * statuses/update_with_media
     *
     * @category statuses
     * @param string $status
     * @param file $media
     * @param bool $possiblySensitive
     * @param int $inReplyToStatusId
     * @param float $lat
     * @param float $long
     * @param bool $displayCoordinates
     * @return mixed
     */
    public function statuses_update_with_media($status, $media, $possiblySensitive = null, $inReplyToStatusId = null, $lat = null, $long = null, $displayCoordinates = null)
    {
        $url = self::API_URL . self::API_VERSION . '/statuses/update_with_media.json';
        $params['status'] = $status;
        $params['media[]'] = file_get_contents(ltrim($media, '@'));
        $headers[] = 'Authorization: ' . $this->_getOauthHeader($url, $params, self::REQUEST_METHOD_POST);
        $headers[] = 'Content-Type: multipart/form-data';
        $headers[] = 'Content-Length: ' . filesize(ltrim($media, '@'));
        return $this->_request($url, $params, $headers, self::REQUEST_METHOD_POST);
    }
    
    /**
     * search/tweets
     *
     * @param string $q
     * @param float $geocode
     * @param string $lang
     * @param string $locale
     * @param string $resultType
     * @param int $count
     * @param string $until
     * @param string $sinceId
     * @param string $maxId
     * @param bool $includeEntities
     * @param string $callback
     * @return mixed
     */
    public function search_tweets($q, $geocode = null, $lang = null, $locale = null, $resultType = null, $count = null, $until = null, $sinceId = null, $maxId = null, $includeEntities = null, $callback = null)
    {
        $api = 'search/tweets';
        $params['q'] = rawurlencode($q);
        if ($count) {
            $params['count'] = ($count <= 100) ? $count : 100;
        }
        return $this->_requestApi($api, $params, self::REQUEST_METHOD_GET);
    }
    
    private function _requestApi($api, $params, $method = self::REQUEST_METHOD_GET, $authenticationBy = null)
    {
        $url = self::API_URL . self::API_VERSION . '/' . $api . '.json';
        $headers = array();
        
        if ($authenticationBy === null) {
            $authenticationBy = $this->_authenticationBy;
        }
        
        switch ($authenticationBy) {
            case self::AUTHENTICATION_APPLICATION_ONLY:
                $headers[] = 'Authorization: ' . $this->_getBearerHeader();
                break;
            case self::AUTHENTICATION_3_LEGGED:
                $headers[] = 'Authorization: ' . $this->_getOauthHeader($url, $params, $method);
                break;
            default:
                throw new TwitterApiInvalidArgumentException('Unknown authentication type.');
        }
        
        switch ($method) {
            case self::REQUEST_METHOD_GET:
                if (is_array($params)) {
                    $url .= '?' . http_build_query($params);
                }
                break;
            case self::REQUEST_METHOD_POST:
                break;
            default:
                throw new TwitterApiInvalidArgumentException('Unknown Method');
        }
        $response = $this->_request($url, $params, $headers, $method);
        $result = json_decode($response, true);
        if (isset($result['errors'])) {
            throw new TwitterApiException('Twitter returns 200, but returns errors. code=' . $result['errors']['code'] . ' message=' . $result['errors']['message']);
        }
        return $result;
    }
    
    private function _request($url, $params, $header, $method)
    {
        $ch = curl_init();
        /* Curl settings */
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        if ($method == self::REQUEST_METHOD_POST) {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
        }
        curl_setopt($ch, CURLOPT_URL, $url);
        $response = curl_exec($ch);
        $error = curl_errno($ch);
        if ($error) {
            curl_close($ch);
            throw new TwitterApiConnectionException('Connection Error. curl errno=' . $error . ' error=' . curl_error($ch));
        }
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if ($httpCode != 200) {
            curl_close($ch);
            throw new TwitterApiConnectionException('Twitter doesn\'t returns 200 response. httpcode=' . $httpCode . ' url=' . $url . ' response=' . print_r($response, true));
        }
        curl_close($ch);
        return $response;
    }
    
    /**
     * Implementation of "Application-only authentication"
     *
     * @throws InvalidArgumentException
     * @throws TwitterApiException
     * @return string Bearer token
     */
    private function _getBearerHeader()
    {
        if ($this->_consumerKey === null || $this->_consumerSecret === null) {
            throw new TwitterApiInvalidArgumentException('Consumer Key or Consumer Secret is null.');
        }
        // Step 1: Encode consumer key and secret
        $bearerTokenCredencials = base64_encode(rawurlencode($this->_consumerKey) . ':' . rawurlencode($this->_consumerSecret));
    
        // Step 2: Obtain a bearer token
        $header = array(
            'Authorization: Basic ' . $bearerTokenCredencials,
            'Content-Type: application/x-www-form-urlencoded;charset=UTF-8'
        );
        $params = 'grant_type=client_credentials';
        $response = $this->_request(self::API_URL . 'oauth2/token', $params, $header, self::REQUEST_METHOD_POST);
        $result = json_decode($response, true);
        if (isset($result['errors'])) {
            throw new TwitterApiException($result['errors']);
        }
        return 'Bearer ' . $result['access_token'];
    }
    
    /**
     * Implementation of "Authorizing a request"
     *
     * @see https://dev.twitter.com/docs/auth/authorizing-request
     * @param string $url API base url (ex. 'https://api.twitter.com/1.1/statuses/update.json')
     * @param array $params request parameters.
     * @param string $method API request method; 'GET', 'POST', and so on.
     * @param string $callback Callback Url.
     * @return string DST (string for adding to Authorization header)
     */
    private function _getOauthHeader($url, $params, $method, $callback = null)
    {
        if ($this->_consumerKey === null || $this->_consumerSecret === null) {
            throw new TwitterApiInvalidArgumentException('Consumer Key or Consumer Secret is null.');
        }
        if (empty($params)) {
            $params = array();
        }
        if ($callback !== null) {
            $headerParams['oauth_callback'] = $callback;
        }
        $headerParams['oauth_consumer_key'] = $this->_consumerKey;
        $headerParams['oauth_nonce'] = self::_getRandomString(32);
        $headerParams['oauth_signature_method'] = 'HMAC-SHA1';
        $headerParams['oauth_timestamp'] = time();
        if ($this->_oauthToken) {
            $headerParams['oauth_token'] = $this->_oauthToken;
        }
        $headerParams['oauth_version'] = '1.0';
        $headerParams['oauth_signature'] = $this->_createSignature($url, $headerParams, $method);
        $headerParams = array_map('rawurlencode', $headerParams);
        
        $dst = 'OAuth ';
        if ($callback !== null) {
            $dst .= 'oauth_callback="' . $headerParams['oauth_callback'] . '",';
        }
        $dst .= 'oauth_consumer_key="' . $headerParams['oauth_consumer_key'] . '", ';
        $dst .= 'oauth_nonce="' . $headerParams['oauth_nonce'] . '", ';
        $dst .= 'oauth_signature="' . $headerParams['oauth_signature'] . '", ';
        $dst .= 'oauth_signature_method="' . $headerParams['oauth_signature_method'] . '", ';
        $dst .= 'oauth_timestamp="' . $headerParams['oauth_timestamp'] . '", ';
        if ($this->_oauthToken) {
            $dst .= 'oauth_token="' . $headerParams['oauth_token'] . '", ';
        }
        $dst .= 'oauth_version="' . $headerParams['oauth_version'] . '"';
        return $dst;
    }
    
    /**
     * Create request signature (part of _getOauthHeader)
     *
     * @param string $url
     * @param array $params
     * @param string $method
     * @return string request signature
     */
    private function _createSignature($url, $headerParams, $method)
    {
        $headerParams = array_map('rawurlencode', $headerParams);
        
        $parameterString = '';
        foreach ($headerParams as $key => $value) {
            $parameterString .= $key . '=' . $value . '&';
        }
        $parameterString = rtrim($parameterString, '&');
        $signatureString = $method . '&' . rawurlencode($url) . '&' . rawurlencode($parameterString);
        
        $signingKey = $this->_consumerSecret . '&' . $this->_oauthTokenSecret;
        
        return base64_encode(hash_hmac('sha1', $signatureString, $signingKey, true));
    }
    
    /**
     * Generate Random String (Utility)
     *
     * @param int $length
     */
    private static function _getRandomString($length = 8){
        $charList = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $res = '';
        for ($i = 0; $i < $length; $i++) {
            $res .= $charList{mt_rand(0, strlen($charList) - 1)};
        }
        return $res;
    }
}