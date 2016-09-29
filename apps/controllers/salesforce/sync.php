<?php
namespace FragTale\Controller\Salesforce;
use FragTale\Controller\Salesforce;
use FragTale\Application as App;

class Oauth extends Salesforce {
  function main(){
    session_start();
    $access_token = $_SESSION['access_token'];
    $instance_url = $_SESSION['instance_url'];

    $content = json_encode(array("LMS_user_id__c" => "some test"));

    $url = "$instance_url/services/services/apexrest/getId/12372";

    $curl = curl_init($url);
    curl_setopt($curl, CURLOPT_HEADER, false);
    curl_setopt($curl, CURLOPT_HTTPHEADER, array("Authorization: OAuth $access_token", "Content-type: application/json"));
    curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "PATCH");
    curl_setopt($curl, CURLOPT_POSTFIELDS, $content);

    curl_exec($curl);

    $status = curl_getinfo($curl, CURLINFO_HTTP_CODE);

    if($status != 204) {
      die("Error: call to $url failed with status $status, curl_error ".curl_error($curl)." curl_errno ".curl_errno($curl));
    }

    curl_close($curl);
  }
}
