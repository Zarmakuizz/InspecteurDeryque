<?php
/* This file is released under the CeCILL-B V1 licence.*/

/**
 * Manage data import from GPX file.
 */
class HL7File implements FileType {

	/** Check file's data type using its root element.
	 * @param $file The file.
	 * @param $extension The file extension. NOTE: unused parameter.
	 * @return TRUE if HL7 or FALSE.
	 */
    public static function isOfThisDataType($file, $extension) {
        if(DEBUG){
            error_log('Class HL7File: start isOfThisDataType() at '.date('H:i:s').PHP_EOL,3,'log.log');
	    }
	    
        $xml = new XMLReader();
        if(!strncmp($file,'<?xml version="1.',17))// $file=xml
            $xml->xml($file);
        else // $file = file path
            $xml->open($file);
        
        // Skip comments and such
        while($xml->nodeType != XMLReader::ELEMENT)
            $xml->read();
        // Now we are at the root element
        if ($xml->localName != 'AnnotatedECG') return false;
        return ($xml->getAttribute('xmlns') == 'urn:hl7-org:v3');
        
        if(DEBUG){
            error_log('Class HL7File: end of isOfThisDataType() at '.date('H:i:s').PHP_EOL,3,'log.log');
	    }
    }

    /** Splits words separated by whitespace(s).
     * FIXME WHY BUT WHY??? R U INDIAN OR WAT?
     * @param $str A string to split.
     * @return $table an array of strings without whitespaces.
     */
    private static function table($digit) {
        $table = [];
        $table = preg_split("/[\s]+/", $digit, NULL, PREG_SPLIT_NO_EMPTY);
        return $table;
    }

	/** Display a form listing importable data from the incoming file.
	 * @param $file The file to get the data from.
	 */
    public static function getImportableData($file) {
        if(DEBUG){
            error_log('Class HL7File: start getImportableData() at '.date('H:i:s').PHP_EOL,3,'log.log');
	    }

        echo <<<END
		<table class="table table-bordered">
			<tr>
				<th><input type="checkbox" value="option1" name="optionsCheckboxes"/></th>
				<th>SequenceSet</th>
				<th>Sequences</th>
			</tr>
END;

        $dom = new DOMDocument("1.0", "utf-8");
        $dom->load($file);
        $sequence = $dom->getElementsByTagName('sequenceSet')->item(0);
        $startTime = $sequence->getElementsByTagName('head')->item(0)->getAttribute('value');
        $increment = $sequence->getElementsByTagName('increment')->item(0)->getAttribute('value');
        $digits = $sequence->getElementsByTagName('digits');
        $i = 1;

        echo "<tr>";
        echo '<td><input type="checkbox" value="option1" name="optionsCheckboxes"/></td>';
        echo "<td>SequenceSet</td>";
        echo <<<END
					<td>
						<table class="table table-striped table-bordered">
END;

        /** Extraction of sequences */
        foreach ($digits as $digit) {
            $code = $digit->parentNode->parentNode->getElementsByTagName('code')->item(0)->getAttribute('code');

            echo <<<END
							<tr>
								<td><input type="checkbox" value="$code" name="$code" id="$code"/></td>
								<td><label class="td_label" for="$code">Sequence : $code</label></td>
							<tr>
END;

        }

        echo <<<END
						</table>
					</td>
				</tr>
END;

        echo <<<END
		</table>
END;

        // Data type selection view
        $nameData = "ECG";
        $sum = sha1($nameData);
        echo <<<END
		<p>Vous pouvez choisir de n'importer que certaines données :</p>
		<table class="table table-striped table-bordered">
			<tr>
				<th><input type="checkbox" value="option1" name="optionsCheckboxes"/></th>
				<th>Nom de la donnée</th>
				<th>Associer la donnée à un relevé</th>
			</tr>
			<tr>
				<td><input type="checkbox" value="ECG" name="data_$sum" id="data_$sum"/></td>
				<td><label class="td_label" for="data_$sum">ECG</label></td>
				<td>
END;

        self::displayDataAssociationChoice($nameData);
        echo <<<END
				</td>
			</tr>
END;

        echo "</table>";
        
        if(DEBUG){
            error_log('Class HL7File: end of getImportableData() at '.date('H:i:s').PHP_EOL,3,'log.log');
	    }

    }

    /**
     * Used in the form's table to select the kind of data to import.
     * Every line of that table corresponds to one use of that method.
     * @param $nameData Name of the kind of data.
     */
    private static function displayDataAssociationChoice($nameData) {
        if(DEBUG){
            error_log('Class HL7File: start displayDataAssociationChoice() at '.date('H:i:s').PHP_EOL,3,'log.log');
	    }
        $statements_list = DataMod::getStatements();
        $sum = sha1($nameData);
        $new_url = CNavigation::generateUrlToApp('Data', 'form', ['iframe_mode' => true, 'return' => 'list']);
        echo <<<END
		<label for="assoc_$sum">Selectionnez le relevé</label>
		<div class="controls">
			<select name="assoc_$sum" id="assoc_$sum">
END;
        foreach ($statements_list as $r) {
            echo '<option value="',              htmlspecialchars($r['name']), '">',              htmlspecialchars($r['name']), " (",              htmlspecialchars($r['modname']), ")", "</option>";
        }
        echo <<<END
			</select>

			<a class="btn" href="$new_url">Nouveau relevé</a>
	    </div>
END;
        
        if(DEBUG){
            error_log('Class HL7File: end of displayDataAssociationChoice() at '.date('H:i:s').PHP_EOL,3,'log.log');
	    }
    }

	/** Store selection into the database.
	 * @param $data Data from an xml string.
	 */
    public static function submitSelection($data) {
        if(DEBUG){
            error_log('Class HL7File: start submitSelection() at '.date('H:i:s').PHP_EOL,3,'log.log');
	    }
        $dom = new DOMDocument();

        $dom->loadXML($data);

        $sequence = $dom->getElementsByTagName('sequenceSet')->item(0);
        //$startTime = $sequence->getElementsByTagName('head')->item(0)->getAttribute('value');
        $startTime = 0;
        $increment = $sequence->getElementsByTagName('increment')->item(0)->getAttribute('value');
        $digits = $sequence->getElementsByTagName('digits');
        $tableaux = [];
        $i = 1;

        // Extraction of sequences.
        foreach ($digits as $digit) {
            $code = $digit->parentNode->parentNode->getElementsByTagName('code')->item(0)->getAttribute('code');

            if (isset($_POST[$code])) {
                $tableaux['names'][$i] = $code;
                $tableaux[$i] = self::table($digit->nodeValue);
                $i++;
            }
        }
        // Calculation of the timestamp
        for ($j = 0; $j < count($tableaux[1]); $j++) {
            $tableaux['timestamp'][] = $startTime + $j * $increment;
        }

        //R::begin();
        // storing data per each statement
        foreach ($_POST as $key => $post) {
            if (self::startswith($key, "assoc_")) {
                $sum_assoc = strrchr($key, '_');
                if (isset($_POST['data' . $sum_assoc])) {
                    self::saveData($post, $_POST['data' . $sum_assoc], $tableaux);
                }
            }
        }
        //R::commit();

        
        if(DEBUG){
            error_log('Class HL7File: end of submitSelection() at '.date('H:i:s').PHP_EOL,3,'log.log');
	    }
        new CMessage('Vos relevés ont été ajoutés avec succès ! Vous pouvez en sélectionner d\'autres, ou bien revenir au Tableau de Bord.');
        CNavigation::redirectToApp('Import', 'dataSelection');
    }

    /** Stores data in a given statement
     * @param $name_statement the statement destination
     * @param $data_type The type of the data
     * @param $data An array of data to store.
     */
    private static function saveData($name_statement_prefix, $data_type, $tableaux) {
        if(DEBUG){
            error_log('Class HL7File: start saveData() at '.date('H:i:s').PHP_EOL,3,'log.log');
	    }
        $multi_releve = new StatementComposition($name_statement_prefix,$_SESSION['user']);

        for ($sequence = 1; $sequence < count($tableaux) - 1; $sequence++) {
            $name_statement = $name_statement_prefix . "_" . $tableaux['names'][$sequence] . "_";
            $r = self::create_statement($name_statement);

            $statement = DataMod::getStatement($name_statement);//r);

            $b_statement = R::load('releve', $statement['id']);
            if (!$statement)
                CTools::hackError();

            $n_datamod = DataMod::loadDataType($statement['modname']);
            $variables = $n_datamod->getVariables();

            $datamod = $n_datamod->initialize();

            R::begin();
            for ($i = 0; $i < count($tableaux['timestamp']); $i++) {
                $datamod->timestamp = $tableaux['timestamp'][$i];

                $datamod->voltage = $tableaux[$sequence][$i];

                $n_datamod->save($_SESSION['user'], $b_statement, $datamod);
            }

            $multi_releve->addStatement($name_statement);
            R::commit();

        }

        $rTodelete = R::findOne('releve', 'name = ? and user_id = ?', [$name_statement_prefix, $_SESSION['bd_id']]);
        R::trash($rTodelete);
        
        if(DEBUG){
            error_log('Class HL7File: end of saveData() at '.date('H:i:s').PHP_EOL,3,'log.log');
	    }
    }

    /** Check a string's start.
     * @param $str The string to evaluate.
     * @param $start The string that you ask if it is at the start of $str.
     * @return True or False.
     */
    private static function startswith($str, $start) {
        return substr($str, 0, strlen($start)) === $start;
    }

    /**
     * Creates a new statement into the database.
     * Defines the name, data mod and user.
     * @param $name Name of the statement.
     * @return $id The id of the created statement.
     */
    private static function create_statement($name) {
        if(DEBUG){
            error_log('Class HL7File: start create_statement() at '.date('H:i:s').PHP_EOL,3,'log.log');
	    }
        if (!R::findOne('releve', 'name = ? and user_id = ?', [$name, $_SESSION['bd_id']])) {

            $mode = R::findOne('datamod', 'modname = ?', ['ECG']);//['ElectroCardioGramme']);

            $user = $_SESSION['user'];

            $statement = R::dispense('releve');
            $statement->mod = $mode;
            $statement->user = $user;
            $statement->name = $name;
            $statement->description = "";

            return R::store($statement);
        }
        
        if(DEBUG){
            error_log('Class HL7File: end of create_statement() at '.date('H:i:s').PHP_EOL,3,'log.log');
	    }
    }

}
?>
