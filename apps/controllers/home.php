<?php
namespace FragTale\Controller;
use FragTale\Controller;

/**
 * @author fabrice
 * 
 * !!! NB: This class must probably be overrided by the class "FragTale\Controller\CMS\Home"
 * 
 */
class Home extends Controller{
	function main(){
		header('HTTP/1.0 403 Forbidden');
		exit;
	}
}