<?php

require_once '../OAuth/Oauth2.php';

class FaceBookTokenStorage  extends TokenStorage
{
	public function setAccessToken($accesstoken){
	   $_SESSION['FaceBook']['token'] = $accesstoken;
	}

    public function getAccessToken() {
       return  $_SESSION['FaceBook']['token'];
    }
    
    public function setAccessState($accessstate) {
       $_SESSION['FaceBook']['state'] = $accessstate;
    }

    public function getAccessState() {
       return  $_SESSION['FaceBook']['state'];
    }
    
    public function clearAccessToken(){
        unset($_SESSION['FaceBook']['token']);
    }

    public function clearAccessState(){
        unset($_SESSION['FaceBook']['state']);
    }
}


class FaceBookOAuth2 extends Oauth2{
      
  private static $returnarray = array("access_token","expires");
  
  protected function getOAuth2_Revoke_URL() {
     return NULL;
  }
  
  protected function getOAuth2_Auth_URL() {
    return 'https://www.facebook.com/dialog/oauth';
  }
   
  protected function getOAuth2_Token_URL() {
    return 'https://graph.facebook.com/oauth/access_token';
  }
  
  public function scopeArrayToString($scope_array = array()) {
     $scope_str = implode(',', $scope_array);
     return $scope_str;
  }
  
  public function __construct($config = array()) {
     $this->setTokeStorage(new FaceBookTokenStorage());
     parent::__construct($config);
  }
  
  protected function getCreateAuthUrlParameter() {
     return array('code' => $this->code);
  }
  
  protected function getAuthUrlParameter(){
    return array();
  }
  
  public function createURLRequestParameter($scope_array = array()){
    $params = array(
        'client_id=' . $this->clientId,
        'redirect_uri=' . urlencode($this->redirectUri),
        'scope=' . $this->scopeArrayToString($scope_array)
    );
    return $params;
  }
  
  protected function getReturnParameter() {
     return self::$returnarray;
  }
 
  protected function getAPIRequestHeader() {
      return NULL;
  }
    
  protected function getAPIUserAgent() {
      return NULL;
  }
  
  protected function getAccessTokenUserAgent() {
      return NULL;
  }
  
  protected function getAccessTokenRequestHeader() {
      return array('content-type' => 'application/x-www-form-urlencoded');
  }
  
  protected function getCustomReturnValue($params)
  {
     return $params;
  }
}

?>
