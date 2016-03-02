<?php
namespace FragTale;
/**
 * @author Fabrice Dant <fabricedant@gmail.com>
 * @copyright	2014 Fabrice Dant
 * @license		http://www.cecill.info/licences/Licence_CeCILL_V2.1-fr.txt CeCILL Licence 2.1 (French version)
 * @license		http://www.cecill.info/licences/Licence_CeCILL_V2.1-en.txt CeCILL Licence 2.1 (English version)
 * 
 */
class Debug{
	/**
	 * 
	 * @param array $var
	 * @param bool	$exit
	 */
	static public function print_r($var, $exit=false){
		echo '<pre style="white-space:pre">';
		print_r($var);
		echo '</pre>';
		if ($exit) exit;
	}
	/**
	 * 
	 * @param string $text
	 */
	static public function vars($text=''){
		static $alreadyThrown = false;
		if (!(defined('ENV') && ENV=='devel') || $alreadyThrown) return;
		$id = 'debug_'.rand();
		echo '
		<style type="text/css">
			.debug_button, .debug_displayer{position:fixed;bottom:0;background:white;border:1px solid grey;font-size:12px;}
			.debug_button{display:block;font-style:italic;padding:3px 10px;text-decoration:none;opacity:0.2;color:black;font-weight:bold;cursor:pointer;max-width:80px;}
			.debug_displayer{display:none;padding:5px 20px 15px 10px;z-index:10000;max-width:800px;max-height:600px;overflow:auto;opacity:0.9;}
		</style>
		<a class="debug_button" onclick="showHide'.$id.'(this);">Debug vars</a>
		<div id="'.$id.'" class="debug_displayer">
			<div style="font-weight:bold;font-size:14px;padding:7px;">'.$_SERVER['REQUEST_URI'].'</div>'.$text;
		self::print_r(array(
			'Params'	=>Application::getIniParams(),
			'Globals'	=>$GLOBALS,
			'Constants'	=>get_defined_constants()
		)
		);
		echo '</div>
		<script type="text/javascript">
			function showHide'.$id.'(obj){
				var debugDisplayer = document.getElementById("'.$id.'");
				if (obj.innerHTML == "Debug vars"){
					debugDisplayer.style.display = "block";
					obj.innerHTML = "Hide";
					obj.style.border = "none";
					obj.style.background = "none";
					obj.style.zIndex = "10001";
					obj.style.width = debugDisplayer.offsetWidth + "px";
				}
				else{
					debugDisplayer.style.display = "none";
					obj.innerHTML = "Debug vars";
					obj.style.border = "1px solid grey";
					obj.style.background = "white";
					obj.style.zIndex = "9999";
					obj.style.width = "auto";
				}
			}
		</script>';
		$alreadyThrown = true;
	}
}