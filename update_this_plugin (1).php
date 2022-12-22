<?php 
namespace NexVis\WordPress{
	
	require ( plugin_dir_path( __FILE__ ) ) . 'plugin_updater.php';

	class Update_This_Plugin extends WP_NVT_Plugin_Updater {

		/**
		 * Return the latest version number of the plugin.
		 *
		 * @return string
		 */
		protected function is_latest_version_available()
		{
			return true;
			//Get Last Update time and it should be 12 hours old, 12 hours delay to avoid Github throtle.
			$delay = 12 * 3600; //12 Hours 
			$delay = 2000;
			$last_update_check = get_option( $this->get_slug()."_last_update_check" );
			if( (time() - $last_update_check) > $delay){ //Too early to test.
				$response_body = $this->get_commits_curl();
								
				$response_data = json_decode($response_body, false);
				$sTimeLatest = $response_data[0]->commit->author->date; 
				$iTimeLatest = strtotime($sTimeLatest);
				$last_updated = strtotime("09-25-2021");
				if( $iTimeLatest > $last_updated){ //Plugin has update
					$version = true;
					update_option( $this->get_slug()."_update_available", 'yes' );
				}else {
					$version = $this->get_current_version();
					$version = false;
				}
				//Save this check time
				update_option( $this->get_slug()."_last_update_check", time() );
				
			}else {
				$version = false;
			}
			$update_available = get_option( $this->get_slug()."_update_available" );
			if($update_available == 'yes' )
				$version = true;
			else 
				$version = false;
			
			return $version;
		}
		
		/**
		 * Return the latest version number of the plugin.
		 *
		 * @return string
		 */
		protected function get_latest_version()
		{
			return $this->get_current_version();
		}

		/**
		 * Get the plugin url.
		 *
		 * @return string The URL.
		 */
		protected function get_url()
		{
			return 'https://github.com/'.$this->get_gitusername().'/'.$this->get_gitrepo();
		}

		/**
		 * Get the package url.
		 *
		 * @return string The package URL.
		 */
		protected function get_package_url()
		{
			return 'https://github.com/'.$this->get_gitusername().'/'.$this->get_gitrepo().'/archive/refs/heads/'.$this->get_gitbranch().'.zip';
		}
		
		/**
		 * Get the plugin commits.
		 *
		 * @return string The URL.
		 */
		protected function get_commits_url()
		{
			return 'https://api.github.com/repos/'.$this->get_gitusername().'/'.$this->get_gitrepo().'/commits';
		}
		
		/**
		 * Get the private plugin zip.
		 *
		 * @return string The URL.
		 */
		protected function get_private_package()
		{
			return 'https://api.github.com/repos/'.$this->get_gitusername().'/'.$this->get_gitrepo().'/zipball';
		}
		
		protected function get_commits_curl(){
			$objCurl = curl_init();
			$url = $this->get_commits_url();
			
			curl_setopt($objCurl, CURLOPT_URL, $url);
						
			//To comply with https://developer.github.com/v3/#user-agent-required
			curl_setopt($objCurl, CURLOPT_USERAGENT, "mudassarijaz"); 
			
			//Skip verification (kinda insecure)
			curl_setopt($objCurl, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($objCurl, CURLOPT_RETURNTRANSFER, true);
			
			//Get the response
			$response = curl_exec($objCurl);
			
			return $response;
		}
		
		protected function github_access_token(){
			/**
			 * Replace the values below with your integration's information found on the Github OAuth App.
			 */
			
			/**
			 * Where to redirect to after the OAuth 2 flow was completed.
			 * Make sure this matches the information of your integration settings on the marketplace build page.
			 */
			$redirectUri = 'http://localhost/travelly/wp-admin/plugins.php?';

			$auth_url = "https://github.com/login/oauth/authorize";
			$token_url = "https://github.com/login/oauth/access_token";
			
			$session_code = wp_get_session_token();
			
			/**
				 * Request an access authorization code.
				 */
				$ch = curl_init();
				curl_setopt($ch, CURLOPT_URL, $auth_url);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
				curl_setopt($ch, CURLOPT_POST, false);
				curl_setopt($ch, CURLOPT_POSTFIELDS, [
					'client_id' => $clientId,
					'code' => $session_code,
					'accept' => 'json',
				]);

				$response = curl_exec($ch);
				//print_r($response);
				$data = json_decode($response, true);
				//print_r($data);
				//exit;
				/**
				 * Request an access token based on the received authorization code.
				 */
				$ch = curl_init();
				curl_setopt($ch, CURLOPT_URL, $token_url);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
				curl_setopt($ch, CURLOPT_POST, true);
				curl_setopt($ch, CURLOPT_POSTFIELDS, [
					'client_id' => $clientId,
					'client_secret' => $clientSecret,
					'code' => $session_code,
					'accept' => 'json',
				]);

				$response = curl_exec($ch);
				print_r($response);
				$data = json_decode($response, true);
				print "Access Token Request Response: ";
				print_r($data);
				//$accessToken = $data['access_token'];
		}
	}
}
?>