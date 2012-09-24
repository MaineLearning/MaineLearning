<?php

/* All credits for this go to BuddyStream (http://buddystream.net),
 * I merely changed class names to avoid redefinition errors i
 * BuddyStream and Bebop are used at the same time, and made a few
 * small changes.
 */

include_once( 'bebop-oauth-class.php' );

/**
 * This class handles all OAuth requests for multiple networks
 */

class bebop_oauth {
	protected $consumerKey;
	protected $consumerSecret;
	protected $requestTokenUrl;
	protected $authorizeUrl;
	protected $accessTokenUrl;
	protected $callbackUrl = NULL;
	protected $requestToken;
	protected $requestTokenSecret;
	protected $accessToken;
	protected $accessTokenSecret;
	protected $requestType = 'GET';
	protected $requestUrl;
	protected $postData   = NULL;
	protected $paramaters = NULL;
	
	/*
	* Setter and getter for consumerKey
	* 
	*/
	public function set_consumer_key( $consumerKey ) {
		$this->consumerKey = $consumerKey;
	}

	public function get_consumer_key() {
		if ( ! $this->consumerKey ) {
			//throw new Exception("ConsumerKey is not set."); 
		}
		return $this->consumerKey;
	}
	/*
	* Setter and getter for consumerSecret
	* 
	*/
	public function set_consumer_secret( $consumerSecret ) {
		$this->consumerSecret = $consumerSecret;
	}
	
	public function get_consumer_secret() {
		if ( ! $this->consumerSecret ) {
			//throw new Exception("ConsumerSecret is not set."); 
		}
		return $this->consumerSecret;
	}
	/*
	* Setter and getters for api request urls
	* 
	*/

	public function set_request_token_url( $requestTokenUrl ) {
		$this->requestTokenUrl = $requestTokenUrl;
	}
	
	public function get_request_token_url() {
		if ( ! $this->requestTokenUrl ) {
			throw new Exception( 'requestTokenUrl is not set.' );
		}
		return $this->requestTokenUrl;
	}
	
	public function set_authorize_url( $authorizeUrl ) {
		$this->authorizeUrl = $authorizeUrl;
	}
	
	public function get_authorize_url() {
		if ( ! $this->authorizeUrl ) {
			throw new Exception( 'authorizeUrl is not set.' );
		}
		return $this->authorizeUrl;
	}
	
	public function set_access_token_url( $accessTokenUrl ) {
        $this->accessTokenUrl = $accessTokenUrl;
	}
	
	public function get_access_token_url() {
		if ( ! $this->accessTokenUrl ) {
			throw new Exception( 'accessTokenUrl is not set.' );
		}
		return $this->accessTokenUrl;
	}

	/*
	* Getter for redirect url
	* Return the redirect url where users have to authorize
	* 
	*/
	
	public function get_redirect_url() {
		return $this->get_authorize_url().'?oauth_token='.urldecode( $this->get_request_token() );
	}

	/*
	* Setter and getter for the request type (GET/POST)
	* 
	*/
	
	public function set_request_type( $requestType ) {
		$this->requestType = $requestType;
	}
	
	public function get_request_type(){
		return $this->requestType;
	}

	/*
	* Setter and getter for the parameters 
	* 
	*/
	public function set_parameters( $parameters ) {
		$this->paramaters = $parameters;
	}

	public function get_parameters() {
		return $this->paramaters;
	}

	/*
	* getter and setter for postdata
	* 
	*/
	
	public function set_post_data( $postData ) {
		$this->postData = $postData;
	}
	
	public function get_post_data() {
		if ( ! $this->postData ) {
			throw new Exception( 'postData is not set.' ); 
		}
		return $this->postData;
	}
	/*
	* Setter and getter for callbackUrl
	* 
	*/
	public function set_callback_url( $callbackUrl ) {
		$this->callbackUrl = $callbackUrl;
	}
	public function get_callback_url() {
		if ( ! $this->callbackUrl ) {
			//throw new Exception("callbackUrl is not set."); 
		}
		return $this->callbackUrl;
	}
	
	/*
	* Setter and getter for requestToken
	*
	*/
	public function set_request_token( $requestToken ) {
		$this->requestToken = $requestToken;
	}
	public function get_request_token() {
		if ( ! $this->requestToken ) {
			// throw new Exception("requestToken is not set."); 
		}
		return $this->requestToken;
	}
	/*
	* Setter and getter for requestTokenSecret
	*
	*/

	public function set_request_token_secret( $requestTokenSecret ) {
		$this->requestTokenSecret = $requestTokenSecret;
	}
	
	public function get_request_token_secret() {
		if ( ! $this->requestTokenSecret ) {
			// throw new Exception("requestTokenSecret is not set."); 
		}
		return $this->requestTokenSecret;
	}
	/*
	* Getter and setter for accessToken
	*
	*/
	public function set_access_token( $accessToken ) {
		$this->accessToken = $accessToken;
	}
	
	public function get_access_token() {
		if ( ! $this->accessToken ) {
			//throw new Exception('accessToken is not set.'); 
		}
		return $this->accessToken;
	}
	/*
	* Getter and setter for accessTokenSecret
	*
	*/
	public function set_access_token_secret( $accessTokenSecret ) {
		$this->accessTokenSecret = $accessTokenSecret;
	}
	
	public function get_access_token_secret() {
		if ( ! $this->accessTokenSecret ) {
			// throw new Exception("accessTokenSecret is not set."); 
		}
		return $this->accessTokenSecret;
	}
	/*
	* Getter for consumer
	* returns a validated OAuth consumer object.
	* If no paramters provided it will fallback on defaults.
	* 
	*/
	public function get_consumer( $consumerKey = null, $consumerSecret = null, $callbackUrl = null ) {
		if ( is_null( $consumerKey ) ) {
			$consumerKey = $this->get_consumer_key();
		}
		if ( is_null( $consumerSecret ) ) {
			$consumerSecret = $this->get_consumer_secret();
		}
		if ( is_null( $callbackUrl ) ) {
			$callbackUrl = $this->get_callback_url();
		}
		$consumer = new bebop_oauth_consumer( $consumerKey,$consumerSecret,$callbackUrl );
		return $consumer;
	}

	/*
	* Geter for the requestToken
	* Returns a temporary request token from provider to do oauth calls.
	* 
	*/
	/*modified by Dale Mckeown*/
	public function request_token() {
		if ( $this->get_parameters() ) {
			$parameters = $this->get_parameters();
		}
		else {
			$parameters = null;
		}
		
		$consumer = $this->get_consumer();
		$req = bebop_oauth_request::from_consumer_and_token( $consumer, NULL, 'GET', $this->get_request_token_url(), $parameters );
		$sig = new bebop_signature_method_HMAC_SHA1();
		$req->sign_request( $sig, $consumer, NULL );
		$req_url = $req->to_url();
		$output  = $this->execute_request( $req_url );
		
		/*create tokenarray from output
		$outputArray = explode("&",$output);
		$tokenArray = explode("=",$outputArray[0]);
		$tokenSecretArray = explode("=",$outputArray[1]); */
		
		//parse he $_GET array into an 'real' array
		parse_str( $output, $sanitised_array );
		$token = array( 'oauth_token' => $sanitised_array['oauth_token'], 'oauth_token_secret' => $sanitised_array['oauth_token_secret'] );
		if ( ! $sanitised_array['oauth_token'] ) {
			echo '<hr><pre>'.$output.'</pre><hr>';
			return false;
		}
		return $token;
	}

	/*
	* Getter for accesToken
	* Trade the requestToken for a token that can be used until the user revokes it. (i say do it!)
	*
	*/
	public function access_token() {
		if ( $this->get_parameters() ) {
			$parameters = $this->get_parameters();
		}
		else {
			$parameters = null;
		}
		$consumer = $this->get_consumer();
		$token    = $this->get_consumer( $this->get_request_token(), $this->get_request_token_secret(), $this->get_callback_url() );
		
		$req = bebop_oauth_request::from_consumer_and_token( $consumer, $token, 'GET', $this->get_access_token_url(), $parameters );
		$sig = new bebop_signature_method_HMAC_SHA1();
		$req->sign_request( $sig, $consumer, $token );
		$req_url = $req->to_url();
		
		$output = $this->execute_request( $req_url );
		
		//create tokenarray from output
		$outputArray = explode( '&',$output );
		$tokenArray = explode( '=',$outputArray[0] );
		$tokenSecretArray = explode( '=',$outputArray[1] );
		$token = array( 'oauth_token' => trim( $tokenArray[1] ), 'oauth_token_secret' => trim( $tokenSecretArray[1] ) );
	
		if ( ! $tokenArray[1] ) {
			echo '<hr><pre>' . $output . '</pre><hr>';
			return false;
		}
		return $token;
	}
	/*
	* Make a oAuth validated request to a provider.
	* 
	*/
	function oauth_request( $url ) {
		if ( $this->get_parameters() ) {
			$parameters = $this->get_parameters();
		}
		else {
			$parameters = null;
		}
		$consumer    = $this->get_consumer();
		$accessToken = $this->get_consumer( $this->get_access_token(), $this->get_access_token_secret(), $this->get_callback_url() );
		$req = bebop_oauth_request::from_consumer_and_token( $consumer, $accessToken, $this->get_request_type(), $url, $parameters );
		$sig = new bebop_signature_method_HMAC_SHA1();
		$req->sign_request( $sig, $consumer, $accessToken );
		
		if ( $this->get_request_type() == 'GET' ) {
			return $this->execute_request( $req->to_url() );
		}
		else {
			$this->set_post_data( $req->to_postdata() );
			return $this->execute_request( $req->get_normalized_http_url() );
		}
	}

	/*
	* Curl to do the actual request.
	* Uses the provider url.
	*
	*/
	public function execute_request( $url ) {
		$ci = curl_init();
		curl_setopt( $ci, CURLOPT_CONNECTTIMEOUT, 30 );
		curl_setopt( $ci, CURLOPT_TIMEOUT, 30 );
		curl_setopt( $ci, CURLOPT_RETURNTRANSFER, TRUE );
		curl_setopt( $ci, CURLOPT_SSL_VERIFYPEER, FALSE );
		curl_setopt( $ci, CURLOPT_HEADER, FALSE );

		if ( $this->get_request_type() == 'POST' ) {
			curl_setopt( $ci, CURLOPT_POST, TRUE );
			if ( $this->get_post_data() ) {
				curl_setopt( $ci, CURLOPT_POSTFIELDS, $this->get_post_data() );
			}
		}
		else {
			curl_setopt( $ci, CURLOPT_HTTPGET, TRUE );
		}
		
		curl_setopt( $ci, CURLOPT_URL, $url );
		
		$response = curl_exec( $ci );
		curl_close( $ci );
		return $response;
	}
	
	public function oauth_request_post_xml( $url ) {
		if ( $this->get_parameters() ) {
			$parameters = $this->get_parameters();
		}
		else {
			$parameters = null;
		}
		
		$consumer    = $this->get_consumer();
		$accessToken = $this->get_consumer( $this->get_access_token(), $this->get_access_token_secret(), $this->get_callback_url() );
		
		$req = bebop_oauth_request::from_consumer_and_token( $consumer, $accessToken, 'POST', $url, $this->get_parameters() );
		$sig = new bebop_signature_method_HMAC_SHA1();
		$req->sign_request( $sig, $consumer, $accessToken );
	
		$ci = curl_init();
		curl_setopt( $ci, CURLOPT_CUSTOMREQUEST, 'POST' );
		curl_setopt( $ci, CURLOPT_RETURNTRANSFER, TRUE );
		curl_setopt( $ci, CURLOPT_SSL_VERIFYPEER, FALSE );
		curl_setopt( $ci, CURLOPT_URL, $url );
		curl_setopt( $ci, CURLOPT_VERBOSE, FALSE );
		
		$header   = array( $req->to_header( 'http://api.linkedin.com' ) );
		$header[] = 'Content-Type: text/xml; charset=UTF-8';
		
		curl_setopt( $ci, CURLOPT_POSTFIELDS, $this->getPostData() );
		curl_setopt( $ci, CURLOPT_HTTPHEADER, $header );
		$response = curl_exec( $ci );
		curl_close( $ci );
	}
}