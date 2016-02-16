<?php

require_once '../../Util/Logger.php';

function checkLibrary()
{
  if (! function_exists('curl_init')) {
     return FASE;
  }

  if (! function_exists('json_decode')) {
     return FASE;
  }

  if (! function_exists('http_build_query')) {
     return FASE;
  }
  
  if (! ini_get('date.timezone') && function_exists('date_default_timezone_set')) {
    date_default_timezone_set('UTC');
  }
  return TRUE;
}

if(!checkLibrary())
{
  echo 'PHP Library can not be found!';
  exit;
}

abstract class AccessTokenStorage
{
	abstract public function setAccessToken($accesstoken);

    abstract public function getAccessToken();
    
    abstract public function getAccessTokenSecret();
    
    abstract public function setAccessTokenSecret($accesstokenSecret);
    
    abstract public function setRequestToken($requesttoken);

    abstract public function getRequestToken();
    
    abstract public function getRequestTokenSecret();
    
    abstract public function setRequestTokenSecret($requesttokenSecret);
   
}


abstract class OAuthSignatureMethod {

  public static function urlencode_rfc3986($input) {
    if (is_array($input)) {
       return array_map(array('OAuthSignatureMethod', 'urlencode_rfc3986'), $input);
    } else if (is_scalar($input)) {
      return str_replace(
        '+',
        ' ',
        str_replace('%7E', '~', rawurlencode($input))
      );
    } else {
      return '';
    }
  }
  
  abstract public function get_name();

  abstract public function build_signature($base_string,$consumerSecret,$tokenSecret);

  public function check_signature($base_string,$consumerKey,$tokenKey, $signature) {
    $built = $this->build_signature($base_string, $consumerKey, $tokenKey);
    return $built == $signature;
  }
}


class OAuthSignatureMethod_HMAC_SHA1 extends OAuthSignatureMethod {
  public function get_name() {
    return "HMAC-SHA1";
  }
  
  public function build_signature($base_string,$consumerSecret,$tokenSecret) {
    $key_parts = array(
      $consumerSecret,
      ($tokenSecret) ? $tokenSecret : ""
    );

    $key_parts = OAuthSignatureMethod::urlencode_rfc3986($key_parts);
    $key = implode('&', $key_parts);
   
    return base64_encode(hash_hmac('sha1', $base_string, $key, true));
  }
}

abstract class OAuth  {
  
  protected $accessTokenStorage;
  
  protected $redirectUri;
  
  protected $consumerKey;
  
  protected $consumerSecret;
  
  protected $authUrl;
  
  protected $proxy = array();
  
  protected $sig_method;
  
  protected $handler;
  
  abstract protected function getOauthVersion();
  
  public function setAuthSuccessHandle(array $registhandler){
     $this->handler = $registhandler;
  }
 
  public function setAccessTokenStorage(AccessTokenStorage $storage){
     $this->accessTokenStorage = $storage;
  }
  
  public function getAccessToken(){
    if(isset($this->accessTokenStorage)){
        return $this->accessTokenStorage->getAccessToken();
    }
    return NULL;
  }
  
   public function getAccessTokenSecret(){
    if(isset($this->accessTokenStorage)){
        return $this->accessTokenStorage->getAccessTokenSecret();
    }
    return NULL;
  }
  
  public function getRequestToken(){
    if(isset($this->accessTokenStorage)){
        return $this->accessTokenStorage->getRequestToken();
    }
    return NULL;
  }
  
   public function getRequestTokenSecret(){
    if(isset($this->accessTokenStorage)){
        return $this->accessTokenStorage->getRequestTokenSecret();
    }
    return NULL;
  }

  public function setAccessToken($accessToken){
    if(isset($this->accessTokenStorage)){
         $this->accessTokenStorage->setAccessToken($accessToken);
    }
  }
  
   public function setAccessTokenSecret($accessTokenSecret){
    if(isset($this->accessTokenStorage)){
       $this->accessTokenStorage->setAccessTokenSecret($accessTokenSecret);
    }
  }
  
  public function setRequestToken($requestToken){
    if(isset($this->accessTokenStorage)){
        $this->accessTokenStorage->setRequestToken($requestToken);
    }
  }
  
   public function setRequestTokenSecret($requestTokenSecret){
    if(isset($this->accessTokenStorage)){
         $this->accessTokenStorage->setRequestTokenSecret($requestTokenSecret);
    }
  }
  
  
  public function __construct($app_config = array()) {
    if (! empty($app_config['oauth_consumer_key'])) {
      $this->consumerKey = $app_config['oauth_consumer_key'];
    }

    if (! empty($app_config['oauth_consumer_secret'])) {
      $this->consumerSecret = $app_config['oauth_consumer_secret'];
    }

    if (! empty($app_config['oauth_redirect_uri'])) {
      $this->redirectUri = $app_config['oauth_redirect_uri'];
    }
    
    if(! empty($app_config['proxyarray'])) {
      $this->proxy = $app_config['proxyarray'];
    }
   
    if(isset($app_config['request_token'])){
       $request_token = $app_config['request_token'];
       $this->setRequestToken($request_token);
    }
    
    if(isset($app_config['request_token_secret'])){
       $request_token_secret = $app_config['request_token_secret'];
       $this->setRequestTokenSecret($request_token_secret);
    }
    
    if(isset($app_config['access_token'])){
       $access_token = $app_config['access_token'];
       $this->setAccessToken($access_token);
    }
    
    if(isset($app_config['access_token_secret'])){
       $access_token_secret = $app_config['access_token_secret'];
       $this->setAccessTokenSecret($access_token_secret);
    }
    $this->sig_method = new OAuthSignatureMethod_HMAC_SHA1();
  }
  
  public function fetchRequestToken($parameterArray = NULL){
     $parameters = array();
     if (isset($parameterArray['oauth_callback'])) {
        $parameters['oauth_callback'] = urlencode($parameterArray['oauth_callback']);
     }else{
        $parameters['oauth_callback'] = urlencode($this->redirectUri);
     }
     $url=$this->getOAuth1_Auth_URL();
     $parameters = array_merge($parameters,$parameterArray);
     $response = $this->makeRequest($this->getOAuth1_Auth_URL(), 'GET',$parameters,'flat');
     return $response ;
  }
  
  abstract protected function getUserAgent();
  
  abstract protected function getConnectTimeout();
  
  abstract protected function getTimeout();
  
  abstract protected function getRequestHeader($returnType);
  
  abstract protected function getAuthUrlParameter();
  
  abstract protected function getOAuth1_Auth_URL();
  
  abstract protected function getOAuth1_LoginToken_URL();
   
  abstract protected function getOAuth1_AccessToken_URL();
   
  protected function getSSLVerifyPeer(){
      return FALSE;
  }  
  
  protected function getProxyPropery(){
     return $this->proxy;
  }
  
  
  function createSignature($method = 'GET',$url,$parameters = array()){
      $p = array();
      foreach($parameters as $k => $v){
         $p[] = $k . '=' . $v;
      }
      $querystring = implode('&', $p);
      $encodeStr = OAuthSignatureMethod::urlencode_rfc3986($querystring);
      $encodeUrl = OAuthSignatureMethod::urlencode_rfc3986($url);
      if($method == 'GET'){
        $sigstr = 'GET&' . $encodeUrl . '&' . $encodeStr;
      }else{
        $sigstr = 'POST&' . $encodeUrl . '&' . $encodeStr;
      }
      $signature=$this->sig_method->build_signature($sigstr,$this->consumerSecret,$this->getAccessTokenSecret());   
      return $signature;
  }
  
  function createQueryString($parameters = array(),$signature){
      $p = array();
      $index =0;
      $i=0;
      foreach($parameters as $k => $v){
         $p[] = $k . '=' . $v;
         if($k === 'oauth_signature_method'){
            $index = $i;
         }
         $i++;
      }
      $signature=OAuthSignatureMethod::urlencode_rfc3986($signature);
      $p = array_merge(array_slice($p, 0, $index), array('oauth_signature=' . $signature), array_slice($p, $index));  
      $querystring = implode('&', $p);
      return $querystring;
  }
  
  function makeRequest($url, $method = 'GET', Array $parameters = array(), $returnType = 'json') { 
      $signature = $this->createSignature($method,$url,$parameters);
      if($method == 'GET'){
         $url = $url . '?' .$this->createQueryString($parameters,$signature);
      }else{
         $postParameter = $this->createQueryString($parameters,$signature);
      }
      
      $ch = curl_init();
      curl_setopt($ch, CURLOPT_URL, $url);
      $userAgent = $this->getUserAgent();
      if(!is_null($userAgent)){
          curl_setopt($ch, CURLOPT_USERAGENT,$userAgent);
      }
      
      $connectionTimeOut = $this->getConnectTimeout();
      if(is_null($connectionTimeOut)){
          curl_setopt($ch, CURLOPT_CONNECTTIMEOUT,$connectionTimeOut);
      }
       
      $timeout = $this->getTimeout();
      if(is_null($timeout)){
          curl_setopt($ch, CURLOPT_TIMEOUT,$timeout);
      }
          
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
      curl_setopt($ch, CURLOPT_HTTPHEADER, array('Expect:'));
      curl_setopt($ch, CURLOPT_HEADER, FALSE);
    
      $requestHeaders = $this->getRequestHeader($returnType);
        // set post fields for POST requests
      if($method != 'GET' ){
          curl_setopt($ch, CURLOPT_POSTFIELDS,$postParameter);      
      }

      curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, $this->getSSLVerifyPeer());
      
      if (!is_null($requestHeaders) && is_array($requestHeaders)) {
           $parsed = array();
           foreach ($requestHeaders as $k => $v) {
             $parsed[] = "$k: $v";
           }
           curl_setopt($ch, CURLOPT_HTTPHEADER,$parsed);
      }
      
      $proxyInfo = $this->getProxyPropery();
      if(!empty($proxyInfo)){
           curl_setopt($ch, CURLOPT_PROXY, $proxyInfo['PROXY_HOST']); 
           curl_setopt($ch, CURLOPT_PROXYPORT, $proxyInfo['PROXY_PORT']); 
           curl_setopt($ch, CURLOPT_PROXYUSERPWD, $proxyInfo['PROXY_USERNAME'] . ':' . $proxyInfo['PROXY_PASSWORD']); 
      }
          
      curl_setopt($ch, CURLINFO_HEADER_OUT, true);
     
      $response = curl_exec($ch);
       
      $info = curl_getinfo($ch);
      

      curl_close($ch);
      
      if($info['http_code'] != 200){
             return NULL;
      }
        
      // return json decoded array or plain response
      if($returnType == 'json'){
            return json_decode($response, true);
      } else {
            return $response;
      }
  }
  
  public function isAuthorized(){
    if(is_null($this->getAccessToken()) || is_null($this->getAccessTokenSecret())){
       return FALSE;
    }
    return TRUE;
  }
  
  
  protected function clearRequestToken(){
      $this->setRequestTokenSecret(NULL);
      $this->setRequestToken(NULL);
  }
  
  protected function clearAccessToken(){
      $this->setAccessTokenSecret(NULL);
      $this->setAccessToken(NULL);
  }
  
  public function validateAccessToken(){
    if ($this->isAuthorized()) {
      return TRUE;
    }
    else {
      $this->clearAccessToken();
      if(!is_null($this->getRequestToken()) && !is_null($this->getRequestTokenSecret())) {
           $this->code = $_GET['oauth_token'];
           $authUrlparamarray = $this->getAuthUrlParameter();
           if (isset($_GET['oauth_token']) && $this->isValidateState()) {
             $response=$this->fetchAccessToken($_REQUEST['oauth_verifier']);         
             $this->processAccessToken($response);
             if(!is_null($this->getAccessToken()) && !is_null($this->getAccessTokenSecret())){
                if(is_callable($this->handler)){
                  call_user_func($this->handler,array("request_token" => $this->getRequestToken(),
                  "request_token_secret" => $this->getRequestTokenSecret(),
                  "access_token" => $this->getAccessToken(),
                  "access_token_secret" => $this->getAccessTokenSecret()
                  ));
                }
                return TRUE; 
             }else{
                $this->clearRequestToken();
                return FALSE;
             }
           }else {
              header('Location: ' . $this->authUrl); 
              return FALSE;
           }
      }
      else{
         $this->createRequestURL();
         return FALSE;
      }
    }
  } 
  
  
  public function makeApiRequest($url, $method = 'GET', Array $parameters = array(), $returnType = 'json'){
     $methodname = $this->sig_method->get_name();
     $defaults = array( "oauth_consumer_key" => $this->consumerKey,
                        "oauth_nonce" => $this->generate_nonce(),
                        "oauth_signature_method" => $methodname,
                        "oauth_timestamp" => $this->generate_timestamp(),
                        "oauth_token" => $this->getAccessToken(),
                        "oauth_version" => $this->getOauthVersion()
                      );
     $parameters=OAuthSignatureMethod::urlencode_rfc3986($parameters);
     $parameters = array_merge($defaults,$parameters);     
     $response = $this->makeRequest($url,$method,$parameters,$returnType);
     return $response;
  }
  
  public function fetchAccessToken($oauth_verifier = FALSE,$parameterArray = array()){
     $methodname = $this->sig_method->get_name();
     $defaults = array( "oauth_consumer_key" => $this->consumerKey,
                        "oauth_nonce" => $this->generate_nonce(),
                        "oauth_signature_method" => $methodname,
                        "oauth_timestamp" => $this->generate_timestamp(),
                        "oauth_token" => $this->getRequestToken()
                      );
     if (!empty($oauth_verifier)) {
        $defaults['oauth_verifier'] = $oauth_verifier;
     }
     $defaults['oauth_version'] = $this->getOauthVersion();
     $response = $this->makeRequest($this->getOAuth1_AccessToken_URL(), 'GET',$defaults,'flat');
     return $response ;
  }
  
  abstract protected function processRequestToken($response);
  
  abstract protected function processAccessToken($response);
  
  
  public static function parse_parameters( $input ) {
    if (!isset($input) || !$input) return array();

    $pairs = explode('&', $input);
    $parsed_parameters = array();
    foreach ($pairs as $pair) {
      $split = explode('=', $pair, 2);
      $parameter = urldecode($split[0]);
      $value = isset($split[1]) ? urldecode($split[1]) : '';
      if (isset($parsed_parameters[$parameter])) {
        if (is_scalar($parsed_parameters[$parameter])) {
          $parsed_parameters[$parameter] = array($parsed_parameters[$parameter]);
        }

        $parsed_parameters[$parameter][] = $value;
      } else {
        $parsed_parameters[$parameter] = $value;
      }
    }
    return $parsed_parameters;
  }
 
  public function createRequestURL(){
      $authUrlparamarray = $this->getAuthUrlParameter();
      $methodname = $this->sig_method->get_name();
      $defaults = array("oauth_consumer_key" => $this->consumerKey,
                        "oauth_nonce" => $this->generate_nonce(),
                        "oauth_signature_method" => $methodname,
                        "oauth_timestamp" => $this->generate_timestamp(),
                        "oauth_version" => $this->getOauthVersion()
                      );
      if(!empty($authUrlparamarray)){
         $authUrlparamarray = array_merge($authUrlparamarray,$defaults);
      }else{
         $authUrlparamarray =$defaults;
      }
      $response = $this->fetchRequestToken($authUrlparamarray);
      $this->processRequestToken($response);
      if(!is_null($this->getRequestToken()) && !is_null($this->getRequestTokenSecret())){
         $this->authUrl = $this->getOAuth1_LoginToken_URL() . "?oauth_token={$this->getRequestToken()}";
      }else{
         unset($this->authUrl);
      }
  }
  
  private function generate_timestamp() {
    return time();
  }

  private function generate_nonce() {
    $mt = microtime();
    $rand = mt_rand();
    return md5($mt . $rand);
  }
  
  public function getAuthUrl(){
     return $this->authUrl;
  }
  
  private function isValidateState(){
     if(!is_null($this->getRequestToken()) && strcmp($this->getRequestToken(),$_GET['oauth_token']) === 0){
        return TRUE;
     }
     return FALSE;
  }
 
  public function logout(){
      unset($this->authUrl);
      $this->clearRequestToken();
      $this->clearAccessToken();
  }
 
}
