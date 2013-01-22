<?php
/* This file is released under the CeCILL-B V1 licence.*/

/** This class manages the file upload for data import view. */
class Import {

	/**
	 * show the file upload webpage.
	 */
	public function index() {
        if(DEBUG){
            error_log('Class Import: start of index() at '.date('H:i:s').PHP_EOL,3,'log.log');
	    }
	    
		CNavigation::setTitle('Importer des données');
		CNavigation::setDescription('GPX ou TCX ou HL7');
		DataImportView::showFormImport();
				
        if(DEBUG){
            error_log('Class Import: end of index() at '.date('H:i:s').PHP_EOL,3,'log.log');
	    }

	}

	/**
	 * File upload method
	 */
	public function submit() {
        if(DEBUG){
            error_log('Class Import: start of submit() at '.date('H:i:s').PHP_EOL,3,'log.log');
	    }
	    
		$folder = 'Uploaded/';
		$file = $folder . sha1($_FILES['fichierXML']['name']);
		$extension = strrchr($_FILES['fichierXML']['name'], '.');
		// oh lol, c'est epic ce code
		$taille_max = 3000000;
		$taille = filesize($_FILES['fichierXML']['tmp_name']);
		if ($taille > $taille_max) {
			$erreur = 'Ce fichier est trop volumineux';
		}
		if (!isset($erreur)) {
			if (move_uploaded_file($_FILES['fichierXML']['tmp_name'], $file)) {
				$_SESSION['fichierXML'] = $file;
				$_SESSION['extFichierXML'] = $extension;
				CNavigation::redirectToApp('Import', 'dataSelection');
			} else {
				new CMessage('Échec de l\'upload', 'error');
				CNavigation::redirectToApp('Import');
			}
		} else {
			new CMessage($erreur, 'error');
			CNavigation::redirectToApp('Import');
		}
				
        if(DEBUG){
            error_log('Class Import: end of submit() at '.date('H:i:s').PHP_EOL,3,'log.log');
	    }

	}

	/**
	 * Show the "data to import" selection webpage
	 */
	public function dataSelection() {
        if(DEBUG){
            error_log('Class Import: start of dataSelection() at '.date('H:i:s').PHP_EOL,3,'log.log');
	    }
	    
		if (isset($_SESSION['fichierXML'])) {
			$file = $_SESSION['fichierXML'];
			if (file_exists($file)) {
				CNavigation::setTitle('Selectionnez vos données à importer');
				DataImportView::showDataSelection($file, $_SESSION['extFichierXML']);
				
                if(DEBUG){
                    error_log('Class Import: end of dataSelection() at '.date('H:i:s').PHP_EOL,3,'log.log');
	            }

				return;
			}
		}
                if(DEBUG){
                    error_log('Class Import: end of dataSelection() at '.date('H:i:s').PHP_EOL,3,'log.log');
	            }
		CTools::hackError();
	}

	/**
	 * Method to remove every file but the index in a given folder.
	 * Used to clean up the upload directory after an upload.
	 */
	public function deleteDirContent($dir_path) {
        if(DEBUG){
            error_log('Class Import: start of deleteDirContent() at '.date('H:i:s').PHP_EOL,3,'log.log');
	    }
	    
		$dir = opendir($dir_path);
		while (($file = readdir($dir)) !== false) {
			$file_path = $dir_path . "/" . $file;
			if (!is_dir($file_path) && $file != "." && $file != ".." && $file != "index.html") {
				unlink($file_path);
			}
		}
		closedir($dir);
		
        if(DEBUG){
            error_log('Class Import: end of deleteDirContent() at '.date('H:i:s').PHP_EOL,3,'log.log');
        }
	}

	/**
	 * UGLY - this method uses 7 nested loops.
	 * Displays some infos from a TCX file.
	 * FIXME - TCX import leads to errors at the end of the procedure. TCX import IS BROKEN.
	 */
	public function DataDisplay($xml) {
        if(DEBUG){
            error_log('Class Import: start of dataDisplay() at '.date('H:i:s').PHP_EOL,3,'log.log');
	    }
	    
		foreach ($xml->children() as $balise) {
			if ($balise->getName() === "Folders" || $balise->getName() === "Workouts" || $balise->getName() === "Courses" || $balise->getName() === "Author") {
				//rien
			} elseif ($balise->getName() === "Activities") {
				foreach ($balise->children() as $activity) {
					echo "<h1>Activity : ",  htmlspecialchars($activity['Sport']), "</h1>";
					foreach ($activity->children() as $lapsandmore) {
						if ($lapsandmore->getName() === "Id") {
							echo "<h2>Id : ",  htmlspecialchars($lapsandmore), "<h2>";
						} elseif ($lapsandmore->getName() === "Lap") {
							///////////////////////////////////////////////
							//affichage tableau données générales du lap
							echo "<h1>",  htmlspecialchars($lapsandmore->getName()), "</h1>";
							echo '<table class="table table-striped">';
							echo "<tr>";
							echo "<th>",  htmlspecialchars($lapsandmore->getName()), "</th>";
							foreach ($lapsandmore->children() as $datalap) {//titres
								if ($datalap->getName() !== "Track") {
									echo "<th>",  htmlspecialchars($datalap->getName()), "</th>";
								}
							}
							echo "</tr>";
							echo "<tr>";
							echo "<td>",  htmlspecialchars($lapsandmore['StartTime']), "</td>";
							foreach ($lapsandmore->children() as $datalap) {//contenu
								if ($datalap->getName() === "AverageHeartRateBpm" || $datalap->getName() === "MaximumHeartRateBpm") {
									echo "<td>",  htmlspecialchars($datalap->children()), "</td>";
								} elseif ($datalap->getName() === "Extensions") {
									$extension = $datalap->children()->children();
									echo "<td>", $extension->getName(), " : ", $extension, "</td>";
								} elseif ($datalap->getName() !== "Track") {
									echo "<td>",  htmlspecialchars($datalap), "</td>";
								}
							}
							echo "</tr>";
							echo "</table>";

							///////////////////////////////////////////////////////////////
							//affichage données précises : les tracks correspondant au lap
							foreach ($lapsandmore->children() as $datalap) {
								if ($datalap->getName() === "Track") {
									echo "<h1>",  htmlspecialchars($datalap->getName()), "</h1>";
									echo '<table class="table table-striped">';
									echo "<tr>";
									$trackpoint = $datalap->xpath("Trackpoint[1]");
									foreach ($trackpoint[0]->children() as $datatrackpoint) {//titres
										echo "<th>",  htmlspecialchars($datatrackpoint->getName()), "</th>";
									}
									echo "</tr>";
									foreach ($datalap->xpath("Trackpoint") as $trackpoints) {
										echo "<tr>";
										foreach ($trackpoints->children() as $datatrackpoint) {
											if ($datatrackpoint->getName() === "Position") {
												echo "<td>";
												foreach ($datatrackpoint->children() as $positions) {
													echo htmlspecialchars($positions->getName()), " : ";
													echo htmlspecialchars($positions), " ";
												}
												echo "</td>";
											} elseif ($datatrackpoint === "HeartRateBpm") {
												echo "<td>",  htmlspecialchars($datatrackpoint->children()), "</td>";
											} else {
												echo "<td>",  htmlspecialchars($datatrackpoint), "</td>";
											}
										}
										echo "</tr>";
									}
									echo "</table>";
								}
							}
						} elseif ($lapsandmore->getName() === "Creator") {
							//rien
						}
					}
				}
			}
		} // end of foreach
		
        if(DEBUG){
            error_log('Class Import: end of DataDisplay() at '.date('H:i:s').PHP_EOL,3,'log.log');
        }
	}

	/**
	 * Displays the TCX file.
	 * UGLY - this method relies on DataDisplay which has been tagged as UGLY.
	 */
	public function displayXML() {
        if(DEBUG){
            error_log('Class Import: start of displayXML() at '.date('H:i:s').PHP_EOL,3,'log.log');
	    }
	    
		if (file_exists('test.tcx')) {
			$text_xml = file_get_contents('test.tcx');
			$text_xml = preg_replace('/<TrainingCenterDatabase.*?>/', '<TrainingCenterDatabase>', $text_xml, 1);
			$text_xml = preg_replace('/<(.+)xsi.*?".*?"(.*?)>/', '<$1$2>', $text_xml);
			$xml = simplexml_load_string($text_xml);
			$this->DataDisplay($xml);
		} else {
			new CMessage('Echec lors de l\'ouverture du fichier test.tcx.', 'error');
		}
		
        if(DEBUG){
            error_log('Class Import: end of displayXML() at '.date('H:i:s').PHP_EOL,3,'log.log');
        }
	}

	public function submitSelection() {
        if(DEBUG){
            error_log('Class Import: start of submitSelection() at '.date('H:i:s').PHP_EOL,3,'log.log');
	    }
	    
		//pour calculer vitesse et calories :
		$GLOBALS['ancienne_lat'] = null;
		$GLOBALS['ancienne_lon'] = null;
		$GLOBALS['ancienne_date'] = null;
		$GLOBALS['ancienne_latcal'] = null;
		$GLOBALS['ancienne_loncal'] = null;
		$GLOBALS['distance_cumulee'] = 0.0;
		///////////
		$path = $_SESSION['fichierXML'];
		$extension = $_SESSION['extFichierXML'];
		if (file_exists($path)) {
			$data = file_get_contents($path);
			if (GPXFile::isOfThisDataType($data, $extension)) {
				GPXFile::submitSelection($data);
			} elseif (TCXFile::isOfThisDataType($data, $extension)) {
				TCXFile::submitSelection($data);
			} elseif (HL7File::isOfThisDataType($data, $extension)) {
				HL7File::submitSelection($data);
			} else {
				echo "L'inspecteur ne reconnait pas ce type de fichier.";
			}
		}
		
        if(DEBUG){
            error_log('Class Import: end of submitSelection() at '.date('H:i:s').PHP_EOL,3,'log.log');
        }
	}

}
?>
