<?php
session_start();

$token_url = getenv('LOGIN_URI') . "/services/oauth2/token";
$code = $_GET['code'];
//echo $code.'-----> ';
if(!isset($code) || $code == ""){
  die("Oauth_Callback Error - code parameter missing from request! returned ".$code);
}

$params = "code=".$code."&grant_type=authorization_code&client_id=".getenv('CONSUMER_KEY')."&client_secret=".getenv('CONSUMER_SECRET')."&redirect_uri=".urlencode(getenv('REDIRECT_URI'));
//echo $params.'-----> ';
$curl = curl_init($token_url);
curl_setopt($curl, CURLOPT_HEADER, false);
curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
curl_setopt($curl, CURLOPT_POST, true);
curl_setopt($curl, CURLOPT_POSTFIELDS, $params);

$json_response = curl_exec($curl);
$status = curl_getinfo($curl, CURLINFO_HTTP_CODE);

if($status != 200){
  die("Oauth_Callback Error call to token URL ".$token_url." failed with status ".$status.", response ".$json_response.", curl_error ".curl_error($curl).", curl errno ".curl_errno($surl));
}
curl_close($curl);
$response = json_decode($json_response, true);
$access_token = $response['access_token'];
$instance_url = $response['instance_url'];
 if(!isset($access_token) || $access_token == ""){
   die("Oauth_Callback Error - access token missing from response");
 }
 if(!isset($instance_url) || $instance_url == ""){
   die("Oauth_Callback Error - instance url missing from response");
 }

 $_SESSION['access_token'] = $access_token;
 $_SESSION['instance_url'] = $instance_url;

 if(!isset($access_token) || $access_token == "") {
   die('Error - access token missing from session');
 }

 if(!isset($instance_url) || $instance_url == ""){
   die('Error - instance_url missing from session');
 }

 $content = json_encode(array("user_id" => "12372","lms_user_id" => "some test"));

 $url = "$instance_url/services/apexrest/getId/";

 $curl = curl_init($url);
 curl_setopt($curl, CURLOPT_HEADER, false);
 curl_setopt($curl, CURLOPT_HTTPHEADER, array("Authorization: OAuth ".$access_token, "Content-type: application/json"));
 curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "PUT");
 curl_setopt($curl, CURLOPT_POSTFIELDS, $content);

 curl_exec($curl);

 $status = curl_getinfo($curl, CURLINFO_HTTP_CODE);

 if($status != 204 && $status != 200) {
   die("Error: call to $url failed with status $status, curl_error ".curl_error($curl)." curl_errno ".curl_errno($curl));
 }

 curl_close($curl);
 echo 'Has it been updated ?';
 //header('Location : https://yesdigital-lmshub.herokuapp.com/sync.php');
?>
