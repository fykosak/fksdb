<?php

namespace Events\FormAdjustments;

use Events\Machine\BaseMachine;
use Events\Machine\Machine;
use Events\Model\Holder\BaseHolder;
use Events\Model\Holder\Holder;
use Nette\Forms\Form;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
class MultiResourceAvailability extends AbstractAdjustment {

    /**
     * @var array[] fields that specifies amount used (string masks)
     */
    private $fields;

    /**
     * @var string Name of event parameter that hold overall capacity.
     */
    private $paramCapacity;
    private $includeStates;
    private $excludeStates;
    private $message;
    private $database;

    private function setFields($fields) {
        if(!is_array($fields)){
            $fields = array($fields);
        }
        $this->fields = $fields;
    }

    /**
     *
     * @param array|string $fields Fields that contain amount of the resource
     * @param string $paramCapacity Name of the parameter with overall capacity.
     * @param string $message String '%avail' will be substitued for the actual amount of available resource.
     * @param string|array $includeStates any state or array of state
     * @param string|array $excludeStates any state or array of state
     */
    function __construct($fields,$paramCapacity,$message,\Nette\Database\Connection $database,$includeStates = BaseMachine::STATE_ANY,$excludeStates = array('cancelled')) {
        $this->setFields($fields);
        $this->database = $database;
        $this->paramCapacity = $paramCapacity;
        $this->message = $message;
        $this->includeStates = $includeStates;
        $this->excludeStates = $excludeStates;
    }

    protected function _adjust(Form $form,Machine $machine,Holder $holder) {
        $groups = $holder->getGroupedSecondaryHolders();
        $groups[] = array(
            'service' => $holder->getPrimaryHolder()->getService(),
            'holders' => array($holder->getPrimaryHolder()),
        );

        $services = array();
        $controls = array();
        foreach ($groups as $group) {
            $holders = array();
            $field = null;
            foreach ($group['holders'] as $baseHolder) {
                $name = $baseHolder->getName();
                foreach ($this->fields as $fieldMask) {
                    $foundControls = $this->getControl($fieldMask);
                    if(!$foundControls){
                        continue;
                    }
                    if(isset($foundControls[$name])){
                        $holders[] = $baseHolder;
                        $controls[] = $foundControls[$name];
                        $field = $fieldMask;
                    }else if($name == substr($fieldMask,0,strpos($fieldMask,self::DELIMITER))){
                        $holders[] = $baseHolder;
                        $controls[] = reset($foundControls); // assume single result;
                        $field = $fieldMask;
                    }
                }
            }
            if($holders){
                $services[] = array(
                    'service' => $group['service'],
                    'holders' => $holders,
                    'field' => $field,
                );
            }
        }

        $usage = [];
        foreach ($services as $serviceData) {
            $firstHolder = reset($serviceData['holders']);
            $event = $firstHolder->getEvent();
            $tableName = $serviceData['service']->getTable()->getName();
            $table = $this->database->table($tableName);
            //   \Nette\Diagnostics\Debugger::barDump($table);
            $table->where($firstHolder->getEventId(),$event->getPrimary());
            if($this->includeStates !== BaseMachine::STATE_ANY){
                $table->where(BaseHolder::STATE_COLUMN,$this->includeStates);
            }
            if($this->excludeStates !== BaseMachine::STATE_ANY){
                $table->where('NOT '.BaseHolder::STATE_COLUMN,$this->excludeStates);
            }else{
                $table->where('1=0');
            }


            $primaries = array_map(function(BaseHolder $baseHolder) {
                return $baseHolder->getModel()->getPrimary(false);
            },$serviceData['holders']);
            $primaries = array_filter($primaries,function($primary) {
                return (bool) $primary;
            });

            $column = BaseHolder::getBareColumn($serviceData['field']);
            $pk = $table->getName().'.'.$table->getPrimary();
            if($primaries){
                $table->where("NOT $pk IN",$primaries);
            }
            $r = $table->select('count('.$column.') AS count, '.$column)->group($column);

            foreach ($r as $row) {
                $k = $row->{$column};
                if(is_numeric($k) && $k > 0){
                    $usage[$k] = array_key_exists($k,$usage) ? ($usage[$k] + $row->count) : $row->count;
                }
            }

            //$usage += $table->sum($column);
        }
        //  \Nette\Diagnostics\Debugger::barDump($usage);
        $capacities = [];
        $o = is_scalar($this->paramCapacity) ? $holder->getParameter($this->paramCapacity) : $this->paramCapacity;
        foreach ($o as $key => $option) {
            if(is_array($option)){
                $capacities[$option['value']] = $option['capacity'];
            }
        }


        foreach ($controls as $control) {
            $newItems = [];
            $items = $control->getItems();
            foreach ($items as $key => $item) {
                $delta = $capacities[$key] - (array_key_exists($key,$usage) ? $usage[$key] : 0);
                if($delta > 0){
                    $newItems[$key] = \Nette\Utils\Html::el('option')->setText($item.'('.$delta.')');
                }else{
                    $newItems[$key] = \Nette\Utils\Html::el('option')->setText($item)->addAttributes(['disabled' => true]);
                }
            }
            $control->setItems($newItems);
        }

        $form->onValidate[] = function(Form $form) use($capacities,$usage,$controls) {
            $controlsUsages = [];
            foreach ($controls as $control) {
                $k = $control->getValue();
                /** kontrola ak je k null nieje zaujem o ubytovanie*/
                if($k){
                    $controlsUsages[$k] = array_key_exists($k,$controlsUsages) ? ($controlsUsages[$k]+1) : 1;
                }

            }
            \Nette\Diagnostics\Debugger::barDump($controlsUsages);
            foreach ($controlsUsages as $k =>$u ){
                $us = (array_key_exists($k,$usage) ? $usage[$k] : 0)+$u;
                if($capacities[$k]-$us<0){
                    $message = str_replace('%avail',$capacities[$k]-$us,$this->message);
                    $form->addError($message);
                }
            }

        };
    }

}
