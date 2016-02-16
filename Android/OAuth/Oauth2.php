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

abstract class TokenStorage
{
	abstract public function setAccessToken($accesstoken);

    abstract public function getAccessToken();
    
    abstract public function setAccessState($accessstate);

    abstract public function getAccessState();
    
    abstract public function clearAccessToken();

    abstract public function clearAccessState();
   
}

abstract class OAuth2  {
      
  protected $clientId;
  
  protected $clientSecret;
  
  protected $redirectUri;
  
  protected $authUrl;
  
  protected $accessType = 'offline';
  
  protected $proxy = array();

  protected $tokenStorage;
  
  abstract protected function getOAuth2_Revoke_URL();
  
  abstract protected function getOAuth2_Token_URL();
   
  abstract protected function getOAuth2_Auth_URL();
  
  abstract public function scopeArrayToString($scope_array = array());
  
  abstract protected function getReturnParameter();
  
  
  public function setTokeStorage(TokenStorage $storage){
     $this->tokenStorage = $storage;
  }
  
  public function __construct($app_config = array()) {
    if (! empty($app_config['oauth2_client_id'])) {
      $this->clientId = $app_config['oauth2_client_id'];
    }

    if (! empty($app_config['oauth2_client_secret'])) {
      $this->clientSecret = $app_config['oauth2_client_secret'];
    }

    if (! empty($app_config['oauth2_redirect_uri'])) {
      $this->redirectUri = $app_config['oauth2_redirect_uri'];
    }
    
    if (! empty($app_config['oauth2_access_type'])) {
      $this->accessType = $app_config['oauth2_access_type'];
    }
    
    if(! empty($app_config['proxyarray'])) {
      $this->proxy = $app_config['proxyarray'];
    }
    if(isset($app_config['access_token'])){
       $access_token = $app_config['access_token'];
       $this->setStorageToken($access_token);
    }
  }
  
  public function getAuthUrl(){
     return $this->authUrl;
  }
  
  private function setStorageToken($token){
     if(isset($this->tokenStorage)){
        $this->tokenStorage->setAccessToken($token);
     }
  }
  
  private function getStorageToken(){
     if(isset($this->tokenStorage)){
        return $this->tokenStorage->getAccessToken();
     }
     return NULL;
  }
  
  private function setStorageState($state){
    if(isset($this->tokenStorage)){
        $this->tokenStorage->setAccessState($state);
    }
  }
  
  private function getStorageState(){
     if(isset($this->tokenStorage)){
         return $this->tokenStorage->getAccessState();
     }
     return NULL;
  }
  
  private function unsetStorageState(){
     if(isset($this->tokenStorage)){
        return $this->tokenStorage->clearAccessState();
     }
  }
  
  private function unsetStorageToken(){
     if(isset($this->tokenStorage)){
        return $this->tokenStorage->clearAccessToken();
     }
  }
  
  abstract protected function getAccessTokenRequestHeader();
  
  abstract protected function getAPIRequestHeader();  
    
  abstract protected function getAccessTokenUserAgent();
  
  abstract protected function getAPIUserAgent();
  
  protected function getProxyPropery(){
     return $this->proxy;
  }
  
  public function makeRequest($url, $method = 'GET', Array $parameters = array(), $returnType = 'json'){
      if(count($parameters) > 0 && $method == 'GET' && strpos($url, '?') === false){
           $p = array();
           foreach($parameters as $k => $v){
                $p[] = $k . '=' . $v;
           }
           $querystring = implode('&', $p);
           $url = $url . '?' . $querystring;
        }
        $ch = curl_init();
       
        curl_setopt($ch, CURLOPT_URL, $url);
           
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); 
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); 


        $requestHeaders = $this->getAPIRequestHeader();
        // set post fields for POST requests
        if($method == 'POST' || $method == 'PUT'){
           $postBody = http_build_query($parameters, '', '&');
           curl_setopt($ch, CURLOPT_POSTFIELDS,$postBody);
           $postsLength = strlen($postBody);
           $requestHeaders['content-length'] = $postsLength;           
        }

        $userAgent = $this->getAPIUserAgent();
        if(!is_null($userAgent)){
           curl_setopt($ch, CURLOPT_USERAGENT,$userAgent);
        }
        
        if (!is_null($requestHeaders) && is_array($requestHeaders)) {
           $parsed = array();
           foreach ($requestHeaders as $k => $v) {
             $parsed[] = "$k: $v";
           }
           curl_setopt($ch, CURLOPT_HTTPHEADER,$parsed);
        }
  
        $proxyInfo = $this->getProxyPropery();
        if(!empty($proxyInfo)){
           //curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
           curl_setopt($ch, CURLOPT_PROXY, $proxyInfo['PROXY_HOST']); 
           curl_setopt($ch, CURLOPT_PROXYPORT, $proxyInfo['PROXY_PORT']); 
           curl_setopt($ch, CURLOPT_PROXYUSERPWD, $proxyInfo['PROXY_USERNAME'] . ':' . $proxyInfo['PROXY_PASSWORD']); 
        }
        
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        //curl_setopt($ch, CURLINFO_HEADER_OUT, true);
    

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
  
  
  public function makeTokenRequest($url, $method = 'GET', Array $parameters = array(), $returnType = 'flat') {         
       if(count($parameters) > 0 && $method == 'GET' && strpos($url, '?') === false){
           $p = array();
           foreach($parameters as $k => $v){
                $p[] = $k . '=' . $v;
           }
           $querystring = implode('&', $p);
           $url = $url . '?' . $querystring;
        }
        
        $ch = curl_init();
              
        curl_setopt($ch, CURLOPT_URL, $url);
           
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); 
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); 

        //var_dump($parameters);
        //exit;
        $requestHeaders = $this->getAccessTokenRequestHeader();

        // set post fields for POST requests
        if($method == 'POST' || $method == 'PUT'){
           $postBody = http_build_query($parameters, '', '&');
           curl_setopt($ch, CURLOPT_POSTFIELDS,$postBody);
           $postsLength = strlen($postBody);
           $requestHeaders['content-length'] = $postsLength;           
        }
   
        $userAgent = $this->getAccessTokenUserAgent();
        if(!is_null($userAgent)){
           //curl_setopt($ch, CURLOPT_USERAGENT,$userAgent);
        }
  
        if (!is_null($requestHeaders) && is_array($requestHeaders)) {
           $parsed = array();
           foreach ($requestHeaders as $k => $v) {
             $parsed[] = "$k: $v";
           }
           curl_setopt($ch, CURLOPT_HTTPHEADER,$parsed);
        }
        $proxyInfo = $this->getProxyPropery();
        if(!empty($proxyInfo)){
           //curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
           curl_setopt($ch, CURLOPT_PROXY, $proxyInfo['PROXY_HOST']); 
           curl_setopt($ch, CURLOPT_PROXYPORT, $proxyInfo['PROXY_PORT']); 
           curl_setopt($ch, CURLOPT_PROXYUSERPWD, $proxyInfo['PROXY_USERNAME'] . ':' . $proxyInfo['PROXY_PASSWORD']); 
        }
        
curl_setopt($ch, CURLINFO_HEADER_OUT, true);
        
        $response = curl_exec($ch);

        $info = curl_getinfo($ch);
  
   //var_dump($info);
      //  exit;

        curl_close($ch);
        
        if($info['http_code'] != 200){
             $this->unsetStorageToken();
             return NULL;
        }
        // return json decoded array or plain response
        if($returnType == 'json'){
             return json_decode($response, true);
        } else {
             return $response;
        }       
  }
  
  
  private function getParameters($response, $returnType) {  
     if($returnType != 'json'){
          $r = explode('&', $response);
          $params = array();
          foreach($r as $v){
              $param = explode('=', $v);
              $params[$param[0]] = $param[1];
          }
      } else 
      {
          $params = $response;
      }
      return $params;
  }
  
  abstract protected function getCustomReturnValue($params);
  
   //ƒ†[ƒU‚ª”FØ‚³‚ê‚½‚©‚Ç‚¤‚©
  public function authenticate($scope_array=array(), $extern_parameter = array(), $returnType='flat' , $code = null) {
    if (!$code && isset($_GET['code'])) {
       $code = $_GET['code'];
    }
    if ($code) {
       $extern_parameter['redirect_uri'] = $this->redirectUri;
       $extern_parameter['client_id'] = $this->clientId;
       $extern_parameter['client_secret'] = $this->clientSecret;
       
       $response = $this->makeTokenRequest($this->getOAuth2_Token_URL(), 'POST',$extern_parameter,$returnType);
       if(is_null($response)){
           $this->clearState();
           return FALSE;
       }
       
       $returnParam = $this->getReturnParameter();
       $params = $this->getParameters($response, $returnType);
       $params = $this->getCustomReturnValue($params);
       
       for($i=0;$i<count($returnParam);$i++) { 
          $paramName = $returnParam[$i];
          if(isset($params[$paramName])){
            $gettoken[$paramName] = $params[$paramName];
            $gettoken['expires'] = time() + $params[$paramName];  
          }else {
            $gettoken[$paramName] = $params[$paramName];
          }
       }
       
       if(isset($gettoken['expires']) && isset($gettoken['access_token'])){
          $this->setStorageToken($gettoken);
          return TRUE;
       }else{
          $logger = Logger::getLogger();
          $logger->debug($params);
          $this->clearState();
       }
    }
    return FALSE;
  }

  abstract protected function getCreateAuthUrlParameter();
  
  
  abstract protected function getAuthUrlParameter();
  
  
  public function validateAccessToken($scope = array()){
    if ($this->isAuthorized()) {
      return TRUE;
    }
    else {
      $this->code = $_GET['code'];
      $authUrlparamarray = $this->getAuthUrlParameter();
      if (isset($_GET['code']) && $this->isValidateState()) {
          $paramarray = $this->getCreateAuthUrlParameter();
          if(!$this->authenticate($scope,$paramarray,'flat',$_GET['code'])){
             $this->authUrl = $this->createAuthUrl($scope,$authUrlparamarray);
             $this->unsetStorageToken();
             return FALSE;
          }
          header('Location: http://' . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF']);
          $this->clearState();
          return TRUE;
       }
       else{
         $this->authUrl = $this->createAuthUrl($scope,$authUrlparamarray);
         $this->unsetStorageToken();
         return FALSE;
       }
    }
  }  
  
  protected function establishCSRFTokenState() {
    $state = $this->getStorageState();
    if (is_null($state)) {
      $state = md5(uniqid(mt_rand(), true));
      $this->setStorageState($state);
    }
  }
  
  protected function clearState(){
     $this->unsetStorageState();
  }
  
  public function logout(){
     $this->unsetStorageState();
     $this->unsetStorageToken();
  }
  
  protected function isValidateState(){
     if(!is_null($this->getStorageState()) && strcmp($this->getStorageState(),$_GET['state']) === 0){
        return TRUE;
     }
     return FALSE;
  }
  
    
  public function createURLRequestParameter($scope_array = array()){
     $scope = $this->scopeArrayToString($scope_array);
     $params = array(
        'redirect_uri=' . $this->redirectUri,
        'client_id=' . $this->clientId,
        'scope=' . $scope,
        'access_type=' . $this->accessType,
    );
    return $params;
  }
  
  public function createAuthUrl($scope_array = array() ,$extern_parameter = array()) {
    $params = $this->createURLRequestParameter($scope_array);
    $params  = array_merge($params,$extern_parameter);
    $this->establishCSRFTokenState();
    $state = $this->getStorageState();
    if (!is_null($state)) {
      $params[] = 'state=' . urlencode($state);
    }
    $params = implode('&', $params);
    $url =  $this->getOAuth2_Auth_URL() . "?$params";
    return $url;
  }

  public function getAccessToken() {
    return $this->getStorageToken();
  }
  
  public function setState($state) {
    $this->setStorageState($state);
  }

  public function setAccessType($accessType) {
    $this->accessType = $accessType;
  }

  public function refreshToken($refreshToken) {
    
  }

  private function refreshTokenRequest($params) {
    
  }
   
  public function revokeToken($token = null) {
    return false;
  }

  public function isAccessTokenExpired() {
   
  }
  
  public function isAuthorized(){
    $accesstoken = $this->getStorageToken();
    if(is_null($accesstoken)){
      return FALSE;
    }
    else {
      if(is_null($accesstoken)){
         return FALSE;
      }
    }
    return TRUE;
  }
  
  public function storageAccessToken($token) {
    if(isset($this->tokenStorage)){
        $this->tokenStorage->setAccessToken($token);
    }
  }
}
