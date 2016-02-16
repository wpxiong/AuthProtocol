var hostUrl = "../../";

function redirct(url){
	window.location.href = url;
}

function registTwitterTokenInfo(accessToken,accessTokenSecret,requestToken,requestTokenSecret){
   var requestUrl= hostUrl + "Browser/Twitter/registerToken.php";
   requestUrl += ('?accessToken=' + accessToken + '&accessTokenSecret=' + accessTokenSecret +'&requestToken=' + requestToken + '&requestTokenSecret=' + requestTokenSecret);
   $.ajax({
		url		: requestUrl,
		type	: 'POST',
		async	: false,
		dataType: 'jsonp'
	});

}

function registGoogleTokenInfo(accessToken){
   var requestUrl=hostUrl + "Browser/GoogleCalendar/registerToken.php";
   requestUrl += ('?accessToken=' + accessToken.access_token + '&expires=' + accessToken.expires +'&tokenType=' + accessToken.token_type + "&refreshToken=" + accessToken.refresh_token);
   $.ajax({
	  url		: requestUrl,
	  type	: 'POST',
	  async	: false,
	  dataType: 'jsonp'
	});
}

function registFaceBookTokenInfo(accessToken){
   var requestUrl=hostUrl + "Browser/FaceBook/registerToken.php";
   requestUrl += ('?accessToken=' + accessToken.access_token + '&expires=' + accessToken.expires);
   $.ajax({
	  url		: requestUrl,
	  type	: 'POST',
	  async	: false,
	  dataType: 'jsonp'
	});
}



