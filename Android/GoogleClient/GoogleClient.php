<?php



//アプリ設定ファイルを読み込む
require_once "application_config.php";

require_once "GoogleOAuth2.php";

require_once '../../Util/Logger.php';

class GoogleClient {

  private $config = array();
  
  static $google_auth;
  
  private $authUrl;
  
  private $approvalPrompt = 'force';
  
  const TOKEN_MIN_LENGTH = 20;
   
  public function getAuthUrl(){
     return self::$google_auth->getAuthUrl();
  }
  
  public function logout(){
     session_destroy();
     $this->cacheFile->refreshGoogleTokenValue("","");
     self::$google_auth->logout();
  }
  
  public function __construct() {
    global $app_config;
    $this->cacheFile = CasheDataUtil::getInstance();
    if(!empty($app_config['developer_key']))  {
       $this->config['developer_key']=$app_config['developer_key'];
    }
    if(!empty($app_config['use_objects']))  {
       $this->config['use_objects']=$app_config['use_objects'];
    }
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
    if(!empty($app_config['site_name']))  {
       $this->config['site_name']=$app_config['site_name'];
    }
    if(!empty($app_config['proxyarray']))  {
       $this->config['proxyarray']=$app_config['proxyarray'];
    }
    if(!empty($app_config['services']['calendar']['scope']))  {
       $this->config['scope']=$app_config['services']['calendar']['scope'];
    }
    $acessValue=$this->cacheFile->readGoogleAccessTokenValue();
    $accessToken = array();
    if(is_array($acessValue)){
      if(isset($acessValue['accessToken'])){
        $accessToken['access_token'] = $acessValue['accessToken'];
      }  
      if(isset($acessValue['expires'])){   
        $accessToken['expires'] = $acessValue['expires'];
      }
      if(isset($acessValue['tokenType'])){
        $accessToken['token_type'] = $acessValue['tokenType'];
      }
      if(isset($acessValue['refreshToken'])){
        $accessToken['refresh_token'] = $acessValue['refreshToken'];
      }
      if($this->validateToken($accessToken)){
         $this->config['access_token'] = $accessToken;
      }
    }
   
    self::$google_auth = new GoogleOAuth2($this->config);
  }
  
  //Google Calendarサーバへのリクエスト
  public function makeRequest($url, $method = 'GET', $parameters = array(), $returnType = 'json'){
    // 認証されたかどうか
    if(self::$google_auth->isAuthorized()) {
        $response = self::$google_auth->makeRequest($url,$method,$parameters,$returnType);
        return $response;
    }
    else {
       if (isset($_GET['code'])) {
          $this->authenticate($_GET['code']);
          header('Location: http://' . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF']);
       }
     }
  }
  
  protected function validateToken($token){
    if(isset($token) && is_array($token) && strlen($token['access_token']) > TOKEN_MIN_LENGTH && strlen($token['token_type']) > 0){
       return TRUE;
    }
    return FALSE;
  }
  
  public function getAccessToken(){
     return self::$google_auth->getAccessToken();
  }
  
  //ユーザが認証されたかどうか
  public function authenticate($code = null) {
    return self::$google_auth->authenticate($this->config['scope'],array('code' => $code,'grant_type' => 'authorization_code'), 'json', array('access_token','refresh_token','expires_in','token_type') ,$code);
  }

  public function setScopes($scope_array = array()) {
     $this->config['scope'] = array_merge($this->config['scope'],$scope_array);
  }
  
  public function setApplicationName($app_name){
     $this->config['application_name']=$app_name;
  }
  
  public function validateAccessToken(){
     return self::$google_auth->validateAccessToken($this->config['scope']);
  }
  
  public function isAuthorized(){
     $token_data = $this->getAccessToken();
     return $this->validateToken($token_data);
  }
}

?>

