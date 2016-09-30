<?php
session_start();
$access_token = $_SESSION['access_token'];
$instance_url = $_SESSION['instance_url'];

if(!isset($access_token) || $access_token == "") {
  die('Error - access token missing from session');
}

if(!isset($instance_url) || $instance_url == ""){
  die('Error - instance_url missing from session');
}

$content = json_encode(array("id" => "12372","user_id" => "some test"));

$url = "$instance_url/services/apexrest/getId/";

$curl = curl_init($url);
curl_setopt($curl, CURLOPT_HEADER, false);
curl_setopt($curl, CURLOPT_HTTPHEADER, array("Authorization: OAuth ".$access_token, "Content-type: application/json"));
curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "PATCH");
curl_setopt($curl, CURLOPT_POSTFIELDS, $content);

curl_exec($curl);

$status = curl_getinfo($curl, CURLINFO_HTTP_CODE);

if($status != 204) {
  die("Error: call to $url failed with status $status, curl_error ".curl_error($curl)." curl_errno ".curl_errno($curl));
}

curl_close($curl);
echo 'Has it been updated ?';
