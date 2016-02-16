<?php



//アプリ設定ファイルを読み込む
require_once "application_config.php";


require_once "FaceBookOAuth2.php";

require_once '../../Util/Logger.php';

class FaceBookClient {

  private $config = array();
  
  static $facebook_auth;
  
  private $authUrl;
  
  private $approvalPrompt = 'force';
    
  public function getAuthUrl(){
     return self::$facebook_auth ->getAuthUrl();
  }
  
  public function logout(){
     session_destroy();
     $this->cacheFile->refreshFaceBookTokenValue("","");
     self::$facebook_auth->logout();
  }
  
  public function __construct() {
    global $app_config;
    $this->cacheFile = CasheDataUtil::getInstance();
    if(!empty($app_config['application_name']))  {
       $this->config['application_name']=$app_config['application_name'];
    }
    if(!empty($app_config['oauth2_client_id']))  {
       $this->config['oauth2_client_id']=$app_config['oauth2_client_id'];
    }
    if(!empty($app_config['oauth2_client_secret']))  {
       $this->config['oauth2_client_secret']=$app_config['oauth2_client_secret'];
    }
    if(!empty($app_config['oauth2_redirect_uri']))  {
       $this->config['oauth2_redirect_uri']=$app_config['oauth2_redirect_uri'];
    }
    if(!empty($app_config['scope']))  {
       $this->config['scope']=$app_config['scope'];
    }
    if(!empty($app_config['proxyarray']))  {
       $this->config['proxyarray']=$app_config['proxyarray'];
    }
    $acessValue=$this->cacheFile->readFaceBookAccessTokenValue();
    if(is_array($acessValue)){
      $accessToken = array();
      if(isset($acessValue['accessToken'])){
        $accessToken['access_token'] = $acessValue['accessToken'];
      }
      if(isset($acessValue['expires'])){   
        $accessToken['expires'] = $acessValue['expires'];
      }
      if($this->validateToken($accessToken)){
         $this->config['access_token'] = $accessToken;
      }
    }
    self::$facebook_auth  = new FaceBookOAuth2($this->config);
  }
  
  protected function validateToken($token){
      if(isset($token) && is_array($token) && strlen($token['access_token']) > 20 ){
         return TRUE;
      }
      return FALSE;
  }
  
  //FaceBookサーバへのリクエスト
  public function makeRequest($url, $method = 'GET', $parameters = array(), $returnType = 'json'){
    // 認証されたかどうか
    if(self::$facebook_auth ->isAuthorized()) {
        $authParam=self::$facebook_auth->getAccessToken();
        $parameters['access_token'] = $authParam['access_token'];
        $response = self::$facebook_auth ->makeRequest($url,$method,$parameters,$returnType);
        return $response;
    }
    else {
       if (isset($_GET['code'])) {
          if($this->authenticate($_GET['code'])){
             header('Location: http://' . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF']);
          }
       }
     }
  }
  
  public function getAccessToken(){
     return self::$facebook_auth->getAccessToken();
  }
  
  //ユーザが認証されたかどうか
  public function authenticate($code = null) {
    return self::$facebook_auth ->authenticate($this->config['scope'],array('code' => $code,'grant_type' => 'authorization_code'), 'json', array('access_token','refresh_token','expires_in','token_type') ,$code);
  }

  public function setScopes($scope_array = array()) {
     $this->config['scope'] = array_merge($this->config['scope'],$scope_array);
  }
  
  public function setApplicationName($app_name){
     $this->config['application_name']=$app_name;
  }
  
  public function validateAccessToken(){
     return self::$facebook_auth ->validateAccessToken($this->config['scope']);
  }
  
  public function isAuthorized(){
     $token_data = $this->getAccessToken();
     return $this->validateToken($token_data);
  }
}

?>

