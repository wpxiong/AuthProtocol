<?php

//アプリ設定ファイルを読み込む
require_once "application_config.php";

require_once "TwitterOAuth.php";

require_once '../../Util/Logger.php';

class TwitterClient
{
  private $config = array();
  
  static $twitter_auth;

  private $clientName;
  
  static $version = '1.1';
  
  private $cacheFile;
  
  const TOKEN_MIN_LENGTH = 20;
    
  public function logout(){
     session_destroy();
     $this->cacheFile->refreshAccessTokenValue("","","","");
     self::$twitter_auth->logout();
  }
 
  public function handleAuthSuccess($tokenArray){
     $this->cacheFile->refreshAccessTokenValue($tokenArray["access_token"],$tokenArray["access_token_secret"],$tokenArray["request_token"],$tokenArray["request_token_secret"]);
  }
  
  public function __construct() {
    global $app_config;
    $this->cacheFile = CasheDataUtil::getInstance();
    
    if(!empty($app_config['application_name']))  {
       $this->config['application_name']=$app_config['application_name'];
    }
    if(!empty($app_config['oauth_consumer_key']))  {
       $this->config['oauth_consumer_key']=$app_config['oauth_consumer_key'];
    }
    if(!empty($app_config['oauth_consumer_secret']))  {
       $this->config['oauth_consumer_secret']=$app_config['oauth_consumer_secret'];
    }
    if(!empty($app_config['oauth_redirect_uri']))  {
       $this->config['oauth_redirect_uri']=$app_config['oauth_redirect_uri'];
    }
    if(!empty($app_config['proxyarray']))  {
       $this->config['proxyarray']=$app_config['proxyarray'];
    }
    $accessToken=$this->cacheFile->readTwitterAccessTokenValue();
    $accessTokenSecret=$this->cacheFile->readTwitterAccessTokenSecretValue();
    $requestToken=$this->cacheFile->readTwitterRequestTokenValue();
    $requestTokenSecret=$this->cacheFile->readTwitterRequestTokenSecretValue();
    if($this->validateToken($accessToken) && $this->validateToken($accessTokenSecret) &&
       $this->validateToken($requestToken) && $this->validateToken($requestTokenSecret)){
      $this->config['request_token'] = $requestToken;
      $this->config['request_token_secret'] = $requestTokenSecret;
      $this->config['access_token'] = $accessToken;
      $this->config['access_token_secret'] = $accessTokenSecret;
    }
    self::$twitter_auth = new TwitterOAuth($this->config);
    self::$twitter_auth->setAuthSuccessHandle(array($this, 'handleAuthSuccess'));
  }
  
  
  protected function validateToken($token){
      if(isset($token) && strlen($token) > TOKEN_MIN_LENGTH){
         return TRUE;
      }
      return FALSE;
  }
  
  //Google Calendarサーバへのリクエスト
  public function makeApiRequest($url, $method = 'GET', $parameters = array(), $returnType = 'json'){
    // 認証されたかどうか
    if(self::$twitter_auth->isAuthorized()) {
        $url = self::$twitter_auth->getBaseUrl() . self::$version . '/' .$url . '.' . $returnType;
        $response = self::$twitter_auth->makeApiRequest($url,$method,$parameters,$returnType);
        return  $response;    
    }
  }
  
  public function getAccessToken(){
     return self::$twitter_auth->getAccessToken();
  }
  
  public function getAccessTokenSecurty(){
     return self::$twitter_auth->getAccessTokenSecret();
  }
  
  public function getRequestToken(){
     return self::$twitter_auth->getRequestToken();
  }
  
  public function getRequestTokenSecurty(){
     return self::$twitter_auth->getRequestTokenSecret();
  }
  
  //ユーザが認証されたかどうか
  public function authenticate($code = null) {
    //return self::$twitter_auth->authenticate();
  }
  
  public function validateAccessToken(){
     return self::$twitter_auth->validateAccessToken();
  }
  
  public function setApplicationName($clientName){
     $this->clientName = $clientName;
  }
  
  public function getAuthUrl(){
     return self::$twitter_auth->getAuthUrl();
  }
  
  public function createRequestURL(){
     self::$twitter_auth->createRequestURL();
  }
  
}