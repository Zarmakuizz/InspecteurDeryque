<?php
/* This file is released under the CeCILL-B V1 licence.*/

/** Class managing Display types */
class DisplayMod extends AbstractMod
{
    /**
     * Get a list of the different kind of display.
     * @return $data Array of String.
     */
	public static function getDisplayTypes() {
        if(DEBUG){
            error_log('Class DisplayMod: start of getDisplayTypes() at '.date('H:i:s').PHP_EOL,3,'log.log');
	    }
	    
		$data = [];

		foreach (scandir('Display') as $folder) {
			$folder = self::secureFolder($folder);
			if (strlen($folder) && $folder[0] !== '.' && is_dir('Display/'.$folder) && file_exists("Display/$folder/D$folder.php")) {
        
				require_once("Display/$folder/D$folder.php");
				$class = "D$folder";
				$data[] = new DisplayMod($class, $folder);
			}
		}
        
        if(DEBUG){
            error_log('Class DisplayMod: end of getDisplayTypes() at '.date('H:i:s').PHP_EOL,3,'log.log');
	    }
		return $data;
	}

	/**
	 * Get a DisplayMod pointing on a given kind of display.
	 * @param $folder the name of the folder containing the files
	 *                relative to the given Display.
	 * @return $displaymod A DisplayMod object pointing on the asked Display.
	 */
	public static function loadDisplayType($folder) {
        if(DEBUG){
            error_log('Class DisplayMod: start of getDisplayTypes() at '.date('H:i:s').PHP_EOL,3,'log.log');
	    }
	    
		$folder = self::secureFolder($folder);
		if (!file_exists("Display/$folder/D$folder.php")) return null;
		require_once("Display/$folder/D$folder.php");
		$class = "D$folder";
		return new DisplayMod($class, $folder);
        
        if(DEBUG){
            error_log('Class DisplayMod: end of loadDisplayType() at '.date('H:i:s').PHP_EOL,3,'log.log');
	    }
	}
}

?>
