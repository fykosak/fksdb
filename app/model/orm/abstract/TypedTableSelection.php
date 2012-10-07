<?php

/**
 * @author Michal Koutný <xm.koutny@gmail.com>
 */
class TypedTableSelection extends NTableSelection {

    /**
     * @var string
     */
    protected $modelClassName;

    public function __construct($modelClassName, $table, NConnection $connection) {
        parent::__construct($table, $connection);
        $this->modelClassName = $modelClassName;
    }

    /**
     * This override ensures returned objects are of correct class.
     * 
     * @param array $row
     * @return AbstractModelSingle
     */
    protected function createRow(array $row) {
        $className = $this->modelClassName;
        return new $className($row, $this);
    }

}

?>
