<?php
namespace FragTale\Controller\Salesforce;
use FragTale\Controller\Salesforce;
use FragTale\Application as App;

class Oauth extends Salesforce {
  function main(){
    $auth_url = getenv('LOGIN_URI')
                  . "/services/oauth2/authorize?response_type=code&client_id="
                  . getenv('CONSUMER_KEY') .'&redirect_uri='. getenv('REDIRECT_URI');
    header('Location: '. $auth_url);
  }
}
