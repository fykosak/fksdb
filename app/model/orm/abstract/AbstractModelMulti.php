<?php

/**
 * @author Michal Koutný <xm.koutny@gmail.com>
 */
abstract class AbstractModelMulti extends NObject {

    /**
     * @var AbstractModelSingle 
     */
    protected $mainModel;

    /**
     * @var AbstractModelSingle 
     */
    protected $joinedModel;
    protected $joiningColumn;

    public function __construct($mainModel, $joinedModel) {
        $this->setMainModel($mainModel);
        $this->setJoinedModel($joinedModel);
    }

    public function toArray() {
        return $this->getMainModel()->toArray() + $this->getJoinedModel()->toArray();
    }

    public function getMainModel() {
        return $this->mainModel;
    }

    public function setMainModel(AbstractModelSingle $mainModel) {
        $this->mainModel = $mainModel;
        if (!$mainModel->isNew() && $this->getJoinedModel()) { // bind via foreign key
            $joiningColumn = $this->joiningColumn;
            $this->getJoinedModel()->$joiningColumn = $mainModel->getPrimary();
        }
    }

    public function getJoinedModel() {
        return $this->joinedModel;
    }

    public function setJoinedModel(AbstractModelSingle $joinedModel) {
        $this->joinedModel = $joinedModel;
    }

}

?>
