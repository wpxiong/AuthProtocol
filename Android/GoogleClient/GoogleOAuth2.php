<?php

require_once '../OAuth/Oauth2.php';

class GoogleTokenStorage  extends TokenStorage
{
	public function setAccessToken($accesstoken){
	   $_SESSION['Google']['token'] = $accesstoken;
	}

    public function getAccessToken() {
       return  $_SESSION['Google']['token'];
    }
    
    public function setAccessState($accessstate) {
       $_SESSION['Google']['state'] = $accessstate;
    }

    public function getAccessState() {
       return  $_SESSION['Google']['state'];
    }
    
    public function clearAccessToken(){
        unset($_SESSION['Google']['token']);
    }

    public function clearAccessState(){
        unset($_SESSION['Google']['state']);
    }
}

class GoogleOAuth2 extends Oauth2{
   
  protected $approvalPrompt = 'force';
    
  protected $assertionCredentials;
      
  private static $returnarray = array("access_token","expires_in","token_type","refresh_token");
   
  const USER_AGENT_SUFFIX = "google-api-php-client/0.6.0";
  
  const USER_API_AGENT = "Google Calendar PHP Starter Application google-api-php-client/0.6.0";
  

  //const OAUTH2_FEDERATED_SIGNON_CERTS_URL = 'https://www.googleapis.com/oauth2/v1/certs';
  
  protected function getOAuth2_Revoke_URL() {
     return 'https://accounts.google.com/o/oauth2/revoke';
  }
  
  protected function getOAuth2_Token_URL() {
    return 'https://accounts.google.com/o/oauth2/token';
  }
   
  protected function getOAuth2_Auth_URL() {
    return 'https://accounts.google.com/o/oauth2/auth';
  }
  
  public function scopeArrayToString($scope_array = array()) {
     $scope_str = implode('+', $scope_array);
     return $scope_str;
  }
  
  public function __construct($config = array()) {
     $this->setTokeStorage(new GoogleTokenStorage());
     parent::__construct($config);
  }
  
  protected function getCreateAuthUrlParameter() {
    return array('code' => $_GET['code'], 'grant_type' => 'authorization_code');
  }
  
  protected function getAuthUrlParameter(){
    return array('response_type=code','approval_prompt=' . urlencode($this->approvalPrompt));
  }
  
  protected function getReturnParameter() {
     return self::$returnarray;
  }
 
  protected function getAPIRequestHeader() {
       $authorization = $this->getAccessToken();
       $authstr = $authorization[self::$returnarray[2]] . ' ' . $authorization[self::$returnarray[0]];
       return array('content-type' => 'application/json; charset=UTF-8' ,'Host' => 'www.googleapis.com','authorization' => $authstr);
  }
    
  protected function getAPIUserAgent() {
      return self::USER_API_AGENT;
  }
  
  protected function getAccessTokenUserAgent() {
      return self::USER_AGENT_SUFFIX;
  }
  
  protected function getAccessTokenRequestHeader() {
      return array('content-type' => 'application/x-www-form-urlencoded');
  }
  
  protected function getCustomReturnValue($params)
  {
       if(is_array($params)){
          foreach($params as $k => $v){
            $returnValue = $k;
            break;
          }
       }
       $returnlist = array();
       if(isset($returnValue)){
           $returnValue = trim(trim($returnValue,'{'),'}');
           $listarray = explode(',',$returnValue);
           foreach($listarray as $v ){
              $keyvalue= explode(':', $v);
              $keyvalue[0]=str_replace("\"","",$keyvalue[0]);
              $keyvalue[1]=str_replace("\"","",$keyvalue[1]);
              $keyvalue[0]=trim($keyvalue[0]);
              $keyvalue[1]=trim($keyvalue[1]);
              $returnlist[$keyvalue[0]] = $keyvalue[1];
           }
           return $returnlist;
       }else{
         return array();
       }
  }
}

?>
