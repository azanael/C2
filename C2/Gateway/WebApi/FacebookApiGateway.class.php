<?php

class FacebookApiGatewayException extends Exception
{
    
}

class FacebookApiGatewayRuntimeException extends RuntimeException
{
    
}

class FacebookApiGatewayConnectionException extends FacebookApiGatewayRuntimeException
{
    
}

class FacebookApiGateway
{
    const FACEBOOK_HTTPS_DOMAIN = 'https://www.facebook.com';
    const FACEBOOK_GRAPH_API_DOMAIN = 'https://graph.facebook.com';
    
    private $accessToken;
    
    private $clientId;
    private $clientSecret;
    
    private $lastRequestUrl = null;
    private $lastResponseCode = null;
    
    private $lastFacebookErrorCode = null;
    private $lastFacebookErrorMessage = null;
    
    public function __construct($clientId, $clientSecret)
    {
        $this->clientId = $clientId;
        $this->clientSecret = $clientSecret;
    }
    
    public function setToken($accessToken)
    {
        $this->accessToken = $accessToken;
    }
    
    public function getLastRequestUrl()
    {
        return $this->lastRequestUrl;
    }
    
    public function getLastResponseCode()
    {
        return $this->lastResponseCode;
    }
    
    public function getLastFacebookErrorCode()
    {
        return $this->lastFacebookErrorCode;
    }
    
    public function getLastFacebookErrorMessage()
    {
        return $this->lastFacebookErrorMessage;
    }
    
    public function getLoginUrl($callbackUrl, $scope = array('publish_stream', 'user_about_me', 'email', 'user_birthday'))
    {
        $params = array(
            'client_id' => $this->clientId,
            'redirect_uri' => $callbackUrl,
            'scope' => implode(',', $scope)
        );
        return self::FACEBOOK_HTTPS_DOMAIN . '/dialog/oauth?' . http_build_query($params);
    }
    
    public function getAccessToken($code, $callbackUrl)
    {
        $params = array(
            'client_id' => $this->clientId,
            'client_secret' => $this->clientSecret,
            'redirect_uri' => $callbackUrl,
            'code' => $code
        );
        $url = self::buildRequestUrl('/oauth/access_token', $params, true);
        $result = $this->request($url);
        $params = null;
        parse_str($result, $params);
        return $params['access_token'];
    }
    
    /**
     * Facebookのユーザプロフィール情報を取得する
     */
    public function getUserProfile($access_token)
    {
        $result = $this->get('me', array('access_token' => $access_token));
        $user = json_decode($result, true);
        return $user;
    }
    
    /**
     * Facebookのユーザプロフィール画像のURLを取得する
     */
    public function getUserProfileImageUrl($fbUserId)
    {
        if (empty($fbUserId)) {
            return false;
        }
        return $this->get("$fbUserId/picture", array('return_ssl_resources' => 1, 'type' => 'normal'));
    }
    
    /**
     * ウォールへ投稿する
     */
    public function postWall($userId, $message, $link = null, $name = null, $caption = null, $description = null, $picture = null, $source = null)
    {
        if (!C2_Validator::isUnsigned($userId)) {
            throw new InvalidArgumentException('userId is not unsigned value. userId=' . dumpVar($userId));
        }
        if (empty($message)) {
            throw new InvalidArgumentException('string is empty.');
        }
        if (empty($this->accessToken)) {
            throw new InvalidArgumentException('Access Token is empty.');
        }
        $params = array();
        $body = array(
            'access_token' => $this->accessToken,
            'message' => $message,
            'picture' => $picture,
            'link' => $link,
            'name' => $name,
            'caption' => $caption,
            'description' => $description,
            'source' => $source
        );
        return $this->post('/me/feed', $params, $body);
    }
    
    private function get($api, $params)
    {
        $url = self::buildRequestUrl($api, $params);
        return $this->request($url);
    }
    
    private function post($api, $params, $body = null)
    {
        $url = self::buildRequestUrl($api, $params);
        return $this->request($url, 'POST', $body);
    }
    
    private function request($url, $method = 'GET', $body = null)
    {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        if ($method == 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
        }
        $result = curl_exec($ch);
        
        $curlInfo = curl_getinfo($ch);
        
        $this->lastRequestUrl = $url;
        $this->lastResponseCode = $curlInfo['http_code'];
        if ($result === false) {
            $errorCode = curl_errno($ch);
            $errorMessage = curl_error($ch);
            curl_close($ch);
            throw new FacebookApiGatewayConnectionException($errorMessage, $errorCode);
        }
        if ($this->lastResponseCode == 302) {
            $headers = substr($result, 0, $curlInfo["header_size"]); //split out header
            preg_match("!\r\n(?:Location|URI): *(.*?) *\r\n!", $headers, $matches);
            return $matches[1];
        } else if ($this->lastResponseCode != 200) {
            throw new FacebookApiGatewayException('Code ' . $this->lastResponseCode . ' url ' . $url . ' result ' . $result);
        }
        curl_close($ch);
        $responseBody = substr($result, $curlInfo['header_size']);
        return $responseBody;
    }
    
    private static function buildRequestUrl($api, $params = null, $useGraphDomain = true)
    {
        $domain = ($useGraphDomain === true) ? self::FACEBOOK_GRAPH_API_DOMAIN : self::FACEBOOK_HTTPS_DOMAIN;
        $url = $domain . '/' . trim($api, '/');
        if (is_array($params)) {
            $url .= '?';
            foreach ($params as $key => $param) {
                $paramString .= "$key=$param&";
            }
            $url .= rtrim($paramString, '&');
        }
        return $url;
    }
}