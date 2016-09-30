<?php
namespace FragTale\Controller\Salesforce;
use FragTale\Controller\Salesforce;
use FragTale\Application as App;

class Oauth_Callback extends Salesforce {
  function main(){
    session_start();

    $token_url = getenv('LOGIN_URI') . "/services/oauth2/token";
    $code = $_GET['code'];

    if(!!isset($code) || $code == ""){
      die("Error - code parameter missing from request!");
    }

    $params = "code=".$code."&grant_type=authorization_code"."&client_id=".getenv('CONSUMER_KEY')."&client_secret=".getenv('CONSUMER_SECRET')."&redirect_uri=".getenv('REDIRECT_URI');
    $curl = curl_init($token_url);
    curl_setopt($curl, CURLOPT_HEADER, false);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_POST, true);
    curl_setopt($curl, CURLOPT_POSTFIELDS, $params);

    $json_response = curl_exec($curl);
    $status = curl_getinfo($curl, CURLINFO_HTTP_CODE);

    if($status != 200){
      die("Error call to token URL ".$token_url" failed du failed with status ".$status.", response ".$json_response.", curl_error ".curl_error($curl).", curl errno ".curl_errno($surl));
    }
    curl_close($curl);
    $response = json_decode($json_response, true);
    $access_token = $response['access_token'];
    $instance_url = $response['instance_url'];
     if(!isset($access_token) || $access_token == ""){
       die("Error - access token missing from response");
     }
     if(!isset($instance_url) || $instance_url == ""){
       die("Error - instance url missing from response");
     }

     $_SESSION['access_token'] = $access_token;
     $_SESSION['instance_url'] = $instance_url;

     header('Location : sync')
  }
}
