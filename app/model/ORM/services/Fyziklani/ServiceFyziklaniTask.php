<?php

/**
 * @author Lukáš Timko <lukast@fykos.cz>
 */
class ServiceFyziklaniTask extends AbstractServiceSingle {

    protected $tableName = DbNames::TAB_FYZIKLANI_TASK;
    protected $modelClassName = 'ModelFyziklaniTask';
    
    /**
     * Syntactic sugar.
     * 
     * @return ModelFyziklaniTask|null
     */
    public function findByLabel($label, $eventId) {
        if (!$label || !$eventId) {
            return null;
        }
        $result = $this->getTable()->where(array(
            'label' => $label, 
            'event_id' => $eventId
        ))->fetch();
        return $result ? : null;
    }
    
    /**
     * Syntactic sugar.
     * 
     * @return \Nette\Database\Table\Selection|null
     */
    public function findAll($eventId) {
        $result = $this->getTable();
        if ($eventId) {
            $result->where('event_id', $eventId);
        }        
        return $result ? : null;
    }

}