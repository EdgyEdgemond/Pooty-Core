<?php
/**
* @package Error
* @author Mel Boyce <mel.boyce@gmail.com>, Damien Wilmann <666@salesforce.com.au>
* @version 1.0
*/
class PootyError {
	/**
	* Builds a basic error string using the supplied title and message.
	*
	* @static
	* @access public
	* @param string $title The title message to include in the error string.
	* @param string $msg The message to include in the error string.
	* @return string
	*/
	public static function GetBasicErrorString($title,$msg) {
		$retval = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">'
		. "<html><body style=\"background:#333;color:#eee;padding:0px;margin:0px;\">"
		. "<div style=\"background:#fff;color:#333;width:50%;margin:100px auto;font-family:Arial,sans-serif;border:5px solid #a13;\">"
		. "<h1 style=\"background:#a13;color:#fff;padding:5px;margin:0px;\">$title</h1>"
		. "<span style=\"display:block;padding:10px;width:100%;\">$msg</span>"
		. "</div></body></html>";
		return $retval;
	}
	
	/**
	/**
	* Returns a string suitable as a complete XHTML document describing an error
	* with the loading a page in cerebro.
	*
	* @static
	* @access public
	* @return mixed
	*/
	public static function Page() {
		return self::GetBasicErrorString("Bad Page Request","The page you have requested does not exist.");
	}	

	/**
	* Logs an title and error to the applications log file.
	*
	* @static
	* @access public
	* @return mixed
	*/
	public static function Log($msg) {
	  $base = '/websites/'.$_SERVER["SERVER_NAME"].'/logs/';
	  
    $head = "\n".date('d-m-Y H:i:s')."  ***********************************************************\n\n";
	  error_log($head, 3, $base.APPLICATION_NAME.date('Ymd').".log");

		error_log($msg, 3, $base.APPLICATION_NAME.date('Ymd').".log");
	}		
}
?>