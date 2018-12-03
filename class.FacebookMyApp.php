<?php
	include('Facebook/autoload.php');
	if(!session_id()) {
		session_start();
	}
	
	class facebookMyApp
	{
		private $fb;
		private $app_id;
		private $app_secret;
		private $helper;
		private $accessToken;
		private $oAuth2Client;
		private $tokenMetadata;
		private $endpoint;
		public $loginUrl;
		public $data;
		public $params;
		public $user;
		public function __construct($app_id, $app_secret)
		{	
			$this->app_id = $app_id;	
			$this->fb = new Facebook\Facebook([
			'app_id' => $app_id, // Replace {app-id} with your app id
			'app_secret' => $app_secret,
			'default_graph_version' => 'v3.2',
			]);
		}
		// Redirect Url and Permission of FB login
		
		public function fbLoginReq($redUrl, $permission=array()){
			$this->helper = $this->fb->getRedirectLoginHelper();
			$this->loginUrl = $this->helper->getLoginUrl($redUrl,$permission);
			
			return $this->loginUrl;
		}
		
		public function fbRedirect()
		{
			$this->helper = $this->fb->getRedirectLoginHelper();
			
			try {
				$this->accessToken = $this->helper->getAccessToken();
				} catch(Facebook\Exceptions\FacebookResponseException $e) {
				// When Graph returns an error
				echo 'Graph returned an error: ' . $e->getMessage();
				exit;
				} catch(Facebook\Exceptions\FacebookSDKException $e) {
				// When validation fails or other local issues
				echo 'Facebook SDK returned an error: ' . $e->getMessage();
				exit;
			}
			
			if (! isset($this->accessToken)) {
				if ($this->helper->getError()) {
					header('HTTP/1.0 401 Unauthorized');
					echo "Error: " . $this->helper->getError() . "\n";
					echo "Error Code: " . $this->helper->getErrorCode() . "\n";
					echo "Error Reason: " . $this->helper->getErrorReason() . "\n";
					echo "Error Description: " . $this->helper->getErrorDescription() . "\n";
					} else {
					header('HTTP/1.0 400 Bad Request');
					echo 'Bad request';
				}
				exit;
			}
			
			
			// The OAuth 2.0 client handler helps us manage access tokens
			$this->oAuth2Client = $this->fb->getOAuth2Client();
			
			// Get the access token metadata from /debug_token
			$this->tokenMetadata = $this->oAuth2Client->debugToken($this->accessToken);
			// echo '<h3>Metadata</h3>';
			// var_dump($this->tokenMetadata);
			
			// Validation (these will throw FacebookSDKException's when they fail)
			$this->tokenMetadata->validateAppId($this->app_id); // Replace {app-id} with your app id
			// If you know the user ID this access token belongs to, you can validate it here
			//$this->tokenMetadata->validateUserId('123');
			$this->tokenMetadata->validateExpiration();
			
			if (!$this->accessToken->isLongLived()) {
				// Exchanges a short-lived access token for a long-lived one
				try {
					$this->accessToken = $this->oAuth2Client->getLongLivedAccessToken($this->accessToken);
					} catch (Facebook\Exceptions\FacebookSDKException $e) {
					echo "<p>Error getting long-lived access token: " . $e->getMessage() . "</p>\n\n";
					exit;
				}
				
				echo '<h3>Long-lived</h3>';
				// var_dump($this->accessToken->getValue());
			}
			
			return (string) $this->accessToken;
			
		}
		// $data you want to fetch from user's FB profile data. 
		public function dashboard($data=array())
		{
			$this->data = implode(',',$data);
			$this->accesstoken= $_SESSION['fb_access_token'];
			try {
				// Returns a `Facebook\FacebookResponse` object
				$response = $this->fb->get('/me?fields='.$this->data, $this->accesstoken);
				// $response = $fb->get('/me?fields=id,name,email,gender,photos,age_range,birthday,friends', $accesstoken );
				$requestPicture = $this->fb->get('/me/picture?redirect=false&height=300', $this->accesstoken); 
				} catch(Facebook\Exceptions\FacebookResponseException $e) {
				echo 'Graph returned an error: ' . $e->getMessage();
				exit;
				} catch(Facebook\Exceptions\FacebookSDKException $e) {
				echo 'Facebook SDK returned an error: ' . $e->getMessage();
				exit;
			}
			$user = $response->getGraphUser();
			// var_dump($user);
			// get Pages list from Graph
			$this->user['id'] = $user['id'];
			$this->user['name'] = $user['name'];
			try {
				// Returns a `Facebook\FacebookResponse` object
				$response = $this->fb->get(
				'/'.$this->user['id'].'/accounts',
				$this->accesstoken
				);
				} catch(Facebook\Exceptions\FacebookResponseException $e) {
				echo 'Graph returned an error: ' . $e->getMessage();
				exit;
				} catch(Facebook\Exceptions\FacebookSDKException $e) {
				echo 'Facebook SDK returned an error: ' . $e->getMessage();
				exit;
			}
			$graphEdge = $response->getGraphEdge();
			// var_dump($graphEdge);
			// get the pages you manage in an array
			$pages = array(); 
			foreach($graphEdge as $edge){
				// var_dump($edge);
				$page=array();
				$page['name']= $edge['name'];
				$page['id']= $edge['id'];
				$page['accesstoken']= $edge['access_token'];
				$pages[]=$page;	
			}
			$this->user['pages']=array();
			$this->user['pages']=$pages;
			// print_r($pages);
			return $this->user;
			
		}
		public function uploadPost($content, $photoPath='',$postOn='')
		{	
			$this->accessToken = $_SESSION['fb_access_token'];
			if($postOn=='' && $photoPath!=''){
				$this->params = array(
				"message" => $content,
				"image" => $this->fb->fileToUpload("http://localhost/fb-login-post/" . $photoPath)
				);
				try {
					$postResponse = $this->fb->post("/me/photos", $this->params, $this->accessToken);
					} catch (FacebookResponseException $e) {
					// display error message
					print $e->getMessage();
					exit();
					} catch (FacebookSDKException $e) {
					print $e->getMessage();
					exit();
				}
				unlink("http://localhost/fb-login-post/" . $photoPath);
			}
			elseif($postOn=='' && $photoPath==''){
				$this->params = array(
				'message' => $content,
				);
				try {
					$postResponse = $this->fb->post("/me/feed", $this->params, $this->accessToken);
					} catch (FacebookResponseException $e) {
					// display error message
					print $e->getMessage();
					exit();
					} catch (FacebookSDKException $e) {
					print $e->getMessage();
					exit();
				}
			}
			elseif($postOn!='' && $photoPath=='')
			{	
				$postOn=explode('--',$postOn);
				$this->params = array(
				"message" => $content
				);
				try {
					$postResponse = $this->fb->post($postOn[0] . '/feed/', $this->params, $postOn[1]);
					} catch (FacebookResponseException $e) {
					// display error message
					print $e->getMessage();
					exit();
					} catch (FacebookSDKException $e) {
					print $e->getMessage();
					exit();
				}
			}
			elseif($postOn!='' && $photoPath!='')
			{
				$postOn=explode('--',$postOn);
				$this->params = array(
				'message' => $content,
				"image" => $this->fb->fileToUpload("http://localhost/fb-login-post/" . $photoPath)

				);
				try {
					$postResponse = $this->fb->post($postOn[0] . '/photos/', $this->params, $postOn[1]);
					} catch (FacebookResponseException $e) {
					// display error message
					print $e->getMessage();
					exit();
					} catch (FacebookSDKException $e) {
					print $e->getMessage();
					exit();
				}
				unlink("http://localhost/fb-login-post/" . $photoPath);
			}
			else
			{
				// do nothing
			}
		}
	}

?> 