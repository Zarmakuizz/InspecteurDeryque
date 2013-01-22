<?php
/* This file is released under the CeCILL-B V1 licence.*/

/**
 * Allows to combine multiple statements into one.
 */
class StatementComposition {

    private $_statementBean; /**< FIXME UNACCEPTABLEEEEEEEEEEEEEEE */
    
    /**
     * Get or store a new multiple statements
     */
    public function __construct($name, $user) {
        if(DEBUG){
            error_log('Class StatementComposition: start of __construct() at '.date('H:i:s').PHP_EOL,3,'log.log');
	    }
	    
        $beans = R::findOrDispense('multi_releve', "name = ?", [$name]);

        foreach ($beans as $bean) {
            $this->_statementBean = $bean;
            break;
        }
        $this->_statementBean->name = $name;
        $this->_statementBean->user = $user;

        R::store($this->_statementBean);
        
        if(DEBUG){
            error_log('Class StatementComposition: end of __construct() at '.date('H:i:s').PHP_EOL,3,'log.log');
	    }
    }

    /**
     * Adds a statement to the current list of statements.
     */
    public function addStatement($rname) {
        $statements = R::find('releve', 'name = ?', [$rname]);
        foreach ($statements as $statement) {
            R::associate($this->_statementBean, $statement);
        }
    }

    
    /**
     * Get a statement by its name.
     * @param $name The name of the statement.
     * @return $statement An array containing the statement data, or
     *                    NULL if not found.
     * FIXME it gets the first statement found regardless of
     *       the owner of that statement.
     */
    public static function getStatement($name) {
        $statementList = R::find('multi_releve', "name = ?", [$name]);
        if(count($statementList) === 0) {
            return NULL;
        } else {
            foreach ($statementList as $statement) {
                return $statement;
            }
            
        }
    }

}
?>
