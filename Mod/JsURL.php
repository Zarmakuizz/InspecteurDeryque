<?php
/* This file is released under the CeCILL-B V1 licence.*/

/**
* Generate a textual representation of an object in an url.
*
* Similar to Rison ou JsURL, but simplier.
* And it use json_encode and rawurlencode php functions, it's funny !
*/
class JsURL
{
	public static function stringify($object)
	{
        if(DEBUG){
            error_log('Class JsURL: start of stringify() at '.date('H:i:s').PHP_EOL,3,'log.log');
	    }
	    
		// Start by taking the json object (yes)
		$json = json_encode($object, JSON_UNESCAPED_UNICODE|JSON_HEX_QUOT);

		// A simple regex for rawurlencode strings (with no excaped quote)
		$json = preg_replace_callback('/"(.*?)"/', function($m) {
			return '\''.rawurlencode($m[1]).'\'';
		},$json);

		// And replace bad characteres for urls by wonderful characters
		$json = str_replace(['[', '{', '}', ']'],['!(', '(', ')', ')'],$json);
		return $json;
        
        if(DEBUG){
            error_log('Class JsURL: end of stringify() at '.date('H:i:s').PHP_EOL,3,'log.log');
	    }
	}
}
 ?>
