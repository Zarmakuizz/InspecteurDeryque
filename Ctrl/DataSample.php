<?php
/**
 * Manages statements.
 * Controler for manipulating statement samples.
 * FIXME a lot of broken code copypasted from Data.php
 */
class DataSample {
    /** Displays statements - copypasta from Data */
    public function index() {
        if(DEBUG){
            error_log('Class DataSample: start index() at '.date('H:i:s').PHP_EOL,3,'log.log');
	    }
	    
        CNavigation::setTitle('Gestion des extraits de relevés');

        $statements = DataMod::getStatementComp($_SESSION['bd_id']);
        $state = DataMod::getStatementCompWhot($_SESSION['bd_id']);
        $statement = DataMod::getStatementCompMulti($_SESSION['bd_id']);
	echo <<<END
		    <b>Extraits de relevés simples</b>
END;
        DataSampleView::showStatementsList($statements);
	echo <<<END
		    <b>Extraits de relevés multiples </b>
END;
        DataSampleView::showStatementsList($state);
	echo <<<END
		    <b>Extraits multiples</b>
END;
        DataSampleView::showStatementsLists($statement);

        DataSampleView::showAddButton();
        if(DEBUG){
            error_log('Class DataSample: end of index() at '.date('H:i:s').PHP_EOL,3,'log.log');
	    }
    }

    public function choose() {
        if(DEBUG){
            error_log('Class DataSample: start of choose() at '.date('H:i:s').PHP_EOL,3,'log.log');
	    }
        CNavigation::setTitle('Nouvel extrait de relevé');
        CNavigation::setDescription('Sélectionnez le relevé que vous souhaitez utiliser');
        DataSampleView::showAddForm();
        DataSampleView::showBackButtons(
            CNavigation::generateUrlToApp('Data'));
        if(DEBUG){
            error_log('Class DataSample: end of choose() at '.date('H:i:s').PHP_EOL,3,'log.log');
	    }
    }

    public function add() {
        if(DEBUG){
            error_log('Class DataSample: start of add() at '.date('H:i:s').PHP_EOL,3,'log.log');
	    }

        if (CNavigation::isValidSubmit(array('name'), $_REQUEST)) {
            if (R::findOne('composition', 'name = ? and user_id = ?', array($_REQUEST['name'], $_SESSION['bd_id']))) {
                new CMessage('Un relevé existe déjà avec le même nom', 'error');
		CNavigation::redirectToApp('DataSample', 'choose');
            }
	    else if(!(isset($_POST['releve'])) and count($_POST['releve']) < 1){
		new CMessage('Vous devez selectionner au moins une sélection', 'error');
		CNavigation::redirectToApp('DataSample', 'choose');

		} else {

		$tab_releve = $_POST['releve'];

                $statement = R::dispense('composition');
                $statement->name = $_REQUEST['name'];
                $statement->releve_id=$_REQUEST['id_rel'];
		$statement->releve_type='releve';

                R::store($statement);

                foreach($tab_releve as $rel){
                	$stat = R::load('selection', $rel);
				$state=R::dispense('selection');
				$state->releve_id=$stat['releve_id'];
				$state->releve_type=$stat['releve_type'];
				$state->name=$stat['name'];
				$state->begin=$stat['begin'];
				$state->end=$stat['end'];
				$state->composition_id=$statement['id'];
				R::store($state);

		}

                new CMessage('Relevé correctement ajouté');

                CNavigation::redirectToApp('Data');

                if(DEBUG){
                    error_log('Class DataSample: end of add() at '.date('H:i:s').PHP_EOL,3,'log.log');
	            }

                return;
            }

        }
        if(DEBUG){
            error_log('Class DataSample: end of add() at '.date('H:i:s').PHP_EOL,3,'log.log');
	    }

    }

    public function addComp() {
        if(DEBUG){
            error_log('Class DataSample: start of addComp() at '.date('H:i:s').PHP_EOL,3,'log.log');
	    }

        if (CNavigation::isValidSubmit(array('name'), $_REQUEST)) {
            if (R::findOne('composition', 'name = ? and user_id = ?', array($_REQUEST['name'], $_SESSION['bd_id']))) {
                new CMessage('Un relevé existe déjà avec le même nom', 'error');
		CNavigation::redirectToApp('DataSample', 'choose');
            }
	    else if(!(isset($POST['releve'])) and count($_POST['releve']) < 1){
		new CMessage('Vous devez selectionner au moins une sélection', 'error');
		CNavigation::redirectToApp('DataSample', 'choose');

		} else {

		$tab_releve = $_POST['releve'];

                $statement = R::dispense('composition');
                $statement->name = $_REQUEST['name'];
                $statement->releve_id=$_REQUEST['id_rel'];
		$statement->releve_type='multi_releve';

                R::store($statement);

                foreach($tab_releve as $rel){
                	$stat = R::load('selection', $rel);
				$state=R::dispense('selection');
				$state->releve_id=$stat['releve_id'];
				$state->releve_type=$stat['releve_type'];
				$state->name=$stat['name'];
				$state->begin=$stat['begin'];
				$state->end=$stat['end'];
				$state->composition_id=$statement['id'];
				R::store($state);

		}

                new CMessage('Relevé correctement ajouté');

                CNavigation::redirectToApp('Data');

                if(DEBUG){
                    error_log('Class DataSample: end of addComp() at '.date('H:i:s').PHP_EOL,3,'log.log');
	            }
                return;
            }

        }

        if(DEBUG){
            error_log('Class DataSample: end of addComp() at '.date('H:i:s').PHP_EOL,3,'log.log');
	    }
    }


    public function addSelect() {
        if(DEBUG){
            error_log('Class DataSample: start of addSelect() at '.date('H:i:s').PHP_EOL,3,'log.log');
	    }
        if (CNavigation::isValidSubmit(array('name'), $_REQUEST)) {
            if (R::findOne('selection', 'name = ? and releve_id = ?', array($_REQUEST['name'], $_REQUEST['id_rel']))) {
                new CMessage('Un relevé existe déjà avec le même nom', 'error');
		CNavigation::redirectToApp('DataSample', 'choose');
            } else {

                $statement = R::dispense('selection');

                $statement->releve_type = 'releve';
                $statement->releve_id = $_REQUEST['id_rel'];
                $statement->begin = $_REQUEST['debut'];
                $statement->end = $_REQUEST['fin'];
                $statement->name = $_REQUEST['name'];

                R::store($statement);

                new CMessage('Sélection correctement ajoutée');

                CNavigation::redirectToApp('DataSample', 'choose');
                if(DEBUG){
                            error_log('Class DataSample: end of addSelect() at '.date('H:i:s').PHP_EOL,3,'log.log');
                }
                return;
            }

        }

        DataSampleView::showStatementsList();
        if(DEBUG){
            error_log('Class DataSample: end of addSelect() at '.date('H:i:s').PHP_EOL,3,'log.log');
	    }

    public function addSelectMul() {
        if(DEBUG){
            error_log('Class DataSample: start of addSelectMul() at '.date('H:i:s').PHP_EOL,3,'log.log');
	    }
        if (CNavigation::isValidSubmit(array('name'), $_REQUEST)) {

            if (R::findOne('selection', 'name = ? and releve_id = ?', array($_REQUEST['name'], $_REQUEST['id_rel']))) {
                new CMessage('Un relevé existe déjà avec le même nom', 'error');
		CNavigation::redirectToApp('DataSample', 'choose');
            } else {

                $statement = R::dispense('selection');

                $statement->releve_type = 'multi_releve';
                $statement->releve_id = $_REQUEST['id_rel'];
                $statement->begin = $_REQUEST['debut'];
                $statement->end = $_REQUEST['fin'];
                $statement->name = $_REQUEST['name'];

                R::store($statement);

                new CMessage('Sélection correctement ajoutée');

                CNavigation::redirectToApp('DataSample', 'choose');

                if(DEBUG){
                    error_log('Class DataSample: end of addSelectMul() at '.date('H:i:s').PHP_EOL,3,'log.log');
	            }
                return;
            }

        }

        DataSampleView::showStatementsList();
        
        if(DEBUG){
            error_log('Class DataSample: end of addSelectMul() at '.date('H:i:s').PHP_EOL,3,'log.log');
	    }
    }


    public function addMulti() {

        if(DEBUG){
            error_log('Class DataSample: start of addMulti() at '.date('H:i:s').PHP_EOL,3,'log.log');
	    }
        if (CNavigation::isValidSubmit(array('name','desc'), $_REQUEST)) {
            if (R::findOne('multi_extrait', 'name = ? and user_id = ?', array($_REQUEST['name'], $_SESSION['bd_id']))) {
                new CMessage('Un multi extrait existe déjà avec le même nom', 'error');
		CNavigation::redirectToApp('DataSample', 'choosemulti');
            }
	    else if(!(isset($POST['releve'])) and count($_POST['releve']) < 1){
		new CMessage('Vous devez sélectionner au moins un extrait', 'error');
		CNavigation::redirectToApp('DataSample', 'choosemulti');

		} else {

                $user = $_SESSION['user'];

                $statement = R::dispense('multi_extrait');
                $statement->user = $user;
                $statement->name = $_REQUEST['name'];
                $statement->description = $_REQUEST['desc'];

                R::store($statement);

		$tab_releve = $_POST['releve'];
                foreach($tab_releve as $rel){
                	$stat = R::dispense('multi_releve_extrait');
                	$stat->multi_releve_id = $statement['id'];
                	$stat->composition_id = $rel;
			R::store($stat);
		}

                new CMessage('Relevé correctement ajouté');

                CNavigation::redirectToApp('Data');
                if(DEBUG){
                     error_log('Class DataSample: end of addMulti() at '.date('H:i:s').PHP_EOL,3,'log.log');
	            }
                return;
            }

        }

        DataSampleView::showStatementsList();
        if(DEBUG){
            error_log('Class DataSample: end of addMulti() at '.date('H:i:s').PHP_EOL,3,'log.log');
	    }
    }
    /**
	 *	View a statement sample.
	 */
	public function view() {
        if(DEBUG){
            error_log('Class DataSample: start of view() at '.date('H:i:s').PHP_EOL,3,'log.log');
	    }
	    
        // Load the statement
	    $statements = isset($_REQUEST['name']) ? DataMod::getStatementComp($_REQUEST['name']) : false;

		// If the statement is not present, show a 404 error
      	if (!$statements) {
            CTools::hackError();
        }
        
        if(DEBUG){
            error_log('$_REQUEST: '.PHP_EOL.print_r($_REQUEST,true).PHP_EOL,3,'log.log');
            error_log('$statements: '.PHP_EOL.print_r($statements,true).PHP_EOL,3,'log.log');
	    }
	    CNavigation::setTitle('Extrait «'.$_REQUEST['name'].'»');
        
        //TODO show statements inside the sample
        echo <<<HTML
<table class='table table-striped display_list'>
    <thead>
        <tr><th class='name'>Name</th>
            <th class='type'>Type</th>
        </tr>
    </thead>
    <tbody>
HTML
        ;
        foreach($statements as $st){
            error_log('$st:'.PHP_EOL.print_r($st,true).PHP_EOL,3,'log.log');
            $name = $st['releve_name'];
            $type = $st['modname'];
            echo "
         <tr id=".$name.">
            <td class='name'>".$name."</td>
            <td class='type'>".$type."</td>
        </tr> ";
        }
        echo <<<HTML
    </tbody>
</table>
HTML
        ;
        // Show options
        DataSampleView::showViewButtons(
            CNavigation::generateUrlToApp('Data'),
            CNavigation::generateMergedUrl('DataSample', 'choosechange'),
	    CNavigation::generateUrlToApp('DataSample', 'choosemulti'),
	    CNavigation::generateUrlToApp('DataSample', 'remove', array('name' => $_REQUEST['name'])));

        if(DEBUG){
            error_log('Class DataSample: end of view() at '.date('H:i:s').PHP_EOL,3,'log.log');
	    }
	    //*/
	}

	public function viewmu() {

        if(DEBUG){
            error_log('Class DataSample: start of viewmu() at '.date('H:i:s').PHP_EOL,3,'log.log');
	    }
	$statements = isset($_REQUEST['name']) ? DataMod::getMultiCompo($_REQUEST['name'], $_SESSION['bd_id']) : false;

  	if (!$statements) {
            CTools::hackError();
        }
	CNavigation::setTitle('Extrait «'.$_REQUEST['name'].'»');
        DataSampleView::showViewButtons(
            CNavigation::generateUrlToApp('Data'),
            CNavigation::generateMergedUrl('DataSample', 'choosechange'),
	    CNavigation::generateUrlToApp('DataSample', 'choosemulti'),
	    CNavigation::generateUrlToApp('DataSample', 'removeMulti', array('name' => $_REQUEST['name'])));
	}

	public function choosemulti() {
        if(DEBUG){
            error_log('Class DataSample: start of choosemulti() at '.date('H:i:s').PHP_EOL,3,'log.log');
	    }
        CNavigation::setTitle('Nouveau multi relevé extrait');
        CNavigation::setDescription('Sélectionnez les extraits que vous souhaitez composer');

        DataSampleView::showMultiForm(array(
                                       'name' => '',
					'desc' => ''));
		$url_back = CNavigation::generateUrlToApp('Data');
		//self::showButton($url_back, 'info', _('Return to the list'), 'back');
		
        if(DEBUG){
            error_log('Class DataSample: end of viewmu() at '.date('H:i:s').PHP_EOL,3,'log.log');
	    }
	}

	public function change() {
        if(DEBUG){
            error_log('Class DataSample: start of change() at '.date('H:i:s').PHP_EOL,3,'log.log');
	    }
	    if (CNavigation::isValidSubmit(array('name','desc'), $_REQUEST)) {

	    if(!isset($_POST['releve']) || count($_POST['releve']) < 1){
		new CMessage('Vous devez sélectionner au moins une sélection', 'error');
		CNavigation::redirectToApp('DataSample', 'choosechange', array('name' => $_REQUEST['name']));

	     } else {

		    $state = DataMod::getStatementComp($_REQUEST['name'], $_SESSION['bd_id']);
		    $state = R::load('multi_releve', $state['id']);
		    $state->description = $_REQUEST['desc'];

		    R::store($state);

		    $multi = DataMod::getMultiRelRel($_SESSION['bd_id'], $state['id']);
		    foreach($multi as $mult){
			    $mul = R::load('multi_releve_releve', $mult['id']);
			    R::exec('delete from multi_releve_releve where id = ?', array($mul['id']));
			    R::trash(R::load('multi_releve_releve', $mul['id']));
		    }
		    $tab_releve = $_POST['releve'];
		    foreach($tab_releve as $rel) {
		        $stat = R::dispense('multi_releve_releve');
		        $stat->multi_releve_id = $state['id'];
		        $stat->releve_id = $rel;
		        R::store($stat);
		    }

		    new CMessage('Relevé correctement modifié');

		    CNavigation::redirectToApp('Data');
            if(DEBUG){
                error_log('Class DataSample: end of change() at '.date('H:i:s').PHP_EOL,3,'log.log');
	        }
		    return;
	    }
       }

	    DataSampleView::showStatementsList();
        if(DEBUG){
            error_log('Class DataSample: end of change() at '.date('H:i:s').PHP_EOL,3,'log.log');
	    }
	}

    public function viewRel() {
    if(DEBUG){
            error_log('Class DataSample: start of viewRel() at '.date('H:i:s').PHP_EOL,3,'log.log');
	    }
		$statement = isset($_REQUEST['name']) ? DataMod::getStatement($_REQUEST['name'], $_SESSION['bd_id']) : false;

		if (!$statement) {
			CTools::hackError();
		}

		CNavigation::setTitle('Relevé «'.$statement['name'].'»');
		DataSampleView::showStatement();
		DataSampleView::showRelForm($statement['name'] , array('type' => 'releve'));

		DataSampleView::showBackButtons(
				CNavigation::generateUrlToApp('Data', 'view', array('name' => $statement['name'])));
				
        if(DEBUG){
            error_log('Class DataSample: end of viewRel() at '.date('H:i:s').PHP_EOL,3,'log.log');
	    }
    }

    public function viewRelMulti() {
        if(DEBUG){
            error_log('Class DataSample: start of viewRelMulti() at '.date('H:i:s').PHP_EOL,3,'log.log');
	    }
        	$statements = isset($_REQUEST['name']) ? DataMod::getStatementMulti($_REQUEST['name'], $_SESSION['bd_id']) : false;

		if (!$statements) {
			CTools::hackError();
		}
		CNavigation::setTitle('Relevé «'.$statements['name'].'»');
		DataSampleView::showStatementMulti();
		DataSampleView::showRelMultiForm($statements['name'], array('type'=>'multi_releve'));

		DataSampleView::showBackButtons(
				CNavigation::generateUrlToApp('DataMulti', 'view', array('name' => $statements['name'])));
				
        if(DEBUG){
            error_log('Class DataSample: end of viewRelMulti() at '.date('H:i:s').PHP_EOL,3,'log.log');
	    }
    }


    public function viewSelect() {
        if(DEBUG){
            error_log('Class DataSample: start of viewSelect() at '.date('H:i:s').PHP_EOL,3,'log.log');
	    }
		$statement = isset($_REQUEST['name']) ? DataMod::getStatement($_REQUEST['name'], $_SESSION['bd_id']) : false;

		if (!$statement) {
			CTools::hackError();
		}

		CNavigation::setTitle('Relevé «'.$statement['name'].'»');
		DataSampleView::showStatement();
		DataSampleView::showSelectForm($statement['name']);

		DataSampleView::showBackButtons(
				CNavigation::generateUrlToApp('Data', 'view', array('name' => $statement['name'])));
				
        if(DEBUG){
            error_log('Class DataSample: end of viewSelect() at '.date('H:i:s').PHP_EOL,3,'log.log');
	    }
    }

    public function viewSelectMulti() {
        if(DEBUG){
            error_log('Class DataSample: start of viewSelectMulti() at '.date('H:i:s').PHP_EOL,3,'log.log');
	    }
        	$statements = isset($_REQUEST['name']) ? DataMod::getStatementMulti($_REQUEST['name'], $_SESSION['bd_id']) : false;

		if (!$statements) {
			CTools::hackError();
		}
		CNavigation::setTitle('Relevé «'.$statements['name'].'»');
		DataSampleView::showStatementMulti();
		DataSampleView::showSelectMultiForm($statements['name']);

		DataSampleView::showBackButtons(
				CNavigation::generateUrlToApp('DataMulti', 'view', array('name' => $statements['name'])));
				
        if(DEBUG){
            error_log('Class DataSample: end of viewSelectMulti() at '.date('H:i:s').PHP_EOL,3,'log.log');
	    }
    }


    public function removeMulti() {
        if(DEBUG){
            error_log('Class DataSample: start of removeMulti() at '.date('H:i:s').PHP_EOL,3,'log.log');
	    }
        $statement = DataMod::getCompoMulti($_REQUEST['name'], $_SESSION['bd_id']);
        if (!$statement) {
            CTools::hackError();
        }
        if (isset($_REQUEST['confirm'])) {
            $stat = R::load('multi_extrait', $statement['id']);
            R::exec('delete from multi_extrait where id = ?', array($stat['id']));
            R::trash(R::load('multi_extrait', $stat['id']));
            CNavigation::redirectToApp('Data');
        } else {
            CNavigation::setTitle('Suppression du relevé «'.$statement['name'].'»');
            CNavigation::setDescription('Consequences will never be the same!');

           DataSampleView::showRemoveForm(
                $statement['description'],
                CNavigation::generateMergedUrl('DataSample', 'removeMulti', array('confirm' => 'yes')),
                CNavigation::generateMergedUrl('DataSample', 'viewmu'));
        }
				
        if(DEBUG){
            error_log('Class DataSample: end of removeMulti() at '.date('H:i:s').PHP_EOL,3,'log.log');
	    }

    }

    public function remove() {
        if(DEBUG){
            error_log('Class DataSample: start of remove() at '.date('H:i:s').PHP_EOL,3,'log.log');
	    }
		$statement = DataMod::getStatementCompo($_REQUEST['name'], $_SESSION['bd_id']);
		if (!$statement) {
			CTools::hackError();
		}
		$statements = $statement[0];

		if (isset($_REQUEST['confirm'])) {
			$stat = R::load('composition', $statements['id']);
			R::exec('delete from composition where id = ?', array($stat['id']));
			R::trash(R::load('composition', $statements['id']));
			CNavigation::redirectToApp('Data');
		}
		else
		{
			CNavigation::setTitle('Suppression du relevé «'.$_REQUEST['name'].'»');
			CNavigation::setDescription('Consequences will never be the same!');

           DataSampleView::showRemoveForm(
                '',
                CNavigation::generateMergedUrl('DataSample', 'remove', array('confirm' => 'yes')),
                CNavigation::generateMergedUrl('DataSample', 'index'));
        }
				
        if(DEBUG){
            error_log('Class DataSample: end of remove() at '.date('H:i:s').PHP_EOL,3,'log.log');
	    }

    }

    public function choosechange() {
        if(DEBUG){
            error_log('Class DataSample: start of choosechange() at '.date('H:i:s').PHP_EOL,3,'log.log');
	    }
        CNavigation::setTitle('Modifier l\'extrait');
        CNavigation::setDescription('Sélectionnez les sélections que vous souhaitez ajouter');
	$desc = DataMod::getDescMulti($_REQUEST['name'], $_SESSION['bd_id']);
	DataSampleView::showChangeForm(array(
                                       'name' => $_REQUEST['name'],
                                       'desc' => (isset($desc["description"]) ? $desc["description"] : '' ) ));
	DataSampleView::showBackButtons(CNavigation::generateUrlToApp('Data'));
				
        if(DEBUG){
            error_log('Class DataSample: end of choosechange() at '.date('H:i:s').PHP_EOL,3,'log.log');
	    }

    }


}
?>
