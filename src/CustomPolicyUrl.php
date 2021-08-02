<?php

namespace EpiphanyInfotech\CustomPolicyUrl;

use Storage;

class CustomPolicyUrl
{
    // Build your next great package.

	public $key_id;
	public $aws_private_key;
	public $client_ip;
	public $aws_url;

    public function __construct($KEY_ID = NULL, $AWS_PRIVATE_KEY=NULL, $client_ip = NULL){
    	$this->key_id = $KEY_ID ?? config('aws-custom-policy-url.KEY_ID');
    	$client_ip = $client_ip ?? config('aws-custom-policy-url.CLIENT_IP');
    	$client_ip = $client_ip ?? $_SERVER['REMOTE_ADDR'];

    	$this->aws_private_key = $AWS_PRIVATE_KEY ??  config('aws-custom-policy-url.AWS_PRIVATE_KEY');
    	$this->client_ip = $client_ip;
    	$this->aws_url = config('aws-custom-policy-url.AWS_URL');
	}

	public function rsa_sha1_sign($policy, $private_key_filename) {
	    $signature = "";

	    // load the private key
	    $fp = fopen($private_key_filename, "r");
	    $priv_key = fread($fp, 8192);
	    fclose($fp);
	    $pkeyid = openssl_get_privatekey($priv_key);

	    // compute signature
	    openssl_sign($policy, $signature, $pkeyid);

	    // free the key from memory
	    openssl_free_key($pkeyid);

	    return $signature;
	}

	public function url_safe_base64_encode($value) {
	    $encoded = base64_encode($value);
	    // replace unsafe characters +, = and / with the safe characters -, _ and ~
	    return str_replace(
	        array('+', '=', '/'),
	        array('-', '_', '~'),
	        $encoded);
	}

	public function create_stream_name($stream, $policy, $signature, $key_pair_id, $expires) {
	    $result = $stream;
	    // if the stream already contains query parameters, attach the new query parameters to the end
	    // otherwise, add the query parameters
	    $path = '';
	    $separator = strpos($stream, '?') == FALSE ? '?' : '&';
	    // the presence of an expires time means we're using a canned policy
	    if($expires) {
	        $result .= $path . $separator . "Expires=" . $expires . "&Signature=" . $signature . "&Key-Pair-Id=" . $key_pair_id;
	    }
	    // not using a canned policy, include the policy itself in the stream name
	    else {
	        $result .= $path . $separator . "Policy=" . $policy . "&Signature=" . $signature . "&Key-Pair-Id=" . $key_pair_id;
	    }

	    // new lines would break us, so remove them
	    return str_replace('\n', '', $result);
	}

	public function encode_query_params($stream_name) {
	    // Adobe Flash Player has trouble with query parameters being passed into it,
	    // so replace the bad characters with their URL-encoded forms
	    return str_replace(
	        array('?', '=', '&'),
	        array('%3F', '%3D', '%26'),
	        $stream_name);
	}

	public function get_custom_policy_stream_name($video_path, $private_key_filename, $key_pair_id, $policy) {
	    // the policy contains characters that cannot be part of a URL, so we base64 encode it
	    $encoded_policy = $this->url_safe_base64_encode($policy);
	    // sign the original policy, not the encoded version
	    $signature = $this->rsa_sha1_sign($policy, $private_key_filename);
	    // make the signature safe to be included in a URL
	    $encoded_signature = $this->url_safe_base64_encode($signature);

	    // combine the above into a stream name
	    $stream_name = $this->create_stream_name($video_path, $encoded_policy, $encoded_signature, $key_pair_id, null);
	    // URL-encode the query string characters to support Flash Player
	    return $this->encode_query_params($stream_name);
	}

	public function customUrl($expiry_time){

		$private_key_filename = $this->aws_private_key;
		$key_pair_id = $this->key_id;
		$video_path = '';

		$expires = time() + $expiry_time; // Default 10 min from now
		
		$client_ip = $this->client_ip;
		$policy =
		'{'.
		    '"Statement":['.
		        '{'.
		            '"Resource":"'. $video_path . '",'.
		            '"Condition":{'.
		                '"IpAddress":{"AWS:SourceIp":"' . $client_ip . '/32"},'.
		                '"DateLessThan":{"AWS:EpochTime":' . $expires . '}'.
		            '}'.
		        '}'.
		    ']' .
		  '}';
		$custom_policy_stream_name = $this->get_custom_policy_stream_name($video_path, $private_key_filename, $key_pair_id, $policy);

		return urldecode($custom_policy_stream_name);
	}


	/*
	 * Function to return the AWS cloudfront encryptrd URL 
	 * Input: (array) | (string) $path_arr, (int) $expiry_time [in seconds] [Default 86400 second or 24 hours]
	 * Output: (array) Policy Appended URL
	 * - By Manpreet Kaur
	 */
	public function getSignedUrls($path_arr, $expiry_time = 86400){
		
		$aws_url = $this->aws_url;

		$url = $this->customUrl($expiry_time);

		$url_arr =  array();
		
		if(is_array($path_arr)){
			
			foreach($path_arr as $path){
	  		
		     	$url_arr[] = $aws_url.'/'. $path . $url;

			}	
		}else{
			
			$url_arr = $aws_url.'/'. $path_arr . $url;
		}
		
		return $url_arr;

	}

	public function test(){
		echo "You have reached the AWS package.";
	}


}
