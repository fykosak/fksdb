<?php

namespace FKSDB\Components\Forms\Controls;

use FKS\Application\IJavaScriptCollector;
use FKSDB\Components\ClientDataTrait;
use FormUtils;
use InvalidArgumentException;
use ModelContestant;
use ModelSubmit;
use Nette\DateTime;
use Nette\Forms\Controls\BaseControl;
use Nette\Utils\Html;
use ServiceSubmit;
use ServiceTaskStudyYear;
use Traversable;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 * 
 * @author Michal Koutný <michal@fykos.cz>
 */
class ContestantSubmits extends BaseControl {

    use ClientDataTrait;

    /**
     * @var Traversable|array of ModelTask
     */
    private $tasks;

    /**
     * @var string
     */
    private $rawValue;

    /**
     * @var ServiceSubmit
     */
    private $submitService;

    /**
     * @var ServiceTaskStudyYear
     */
    private $serviceTaskStudyYear;

    /**
     * @var ModelContestant
     */
    private $contestant;

    /**
     * @var int
     */
    private $acYear;

    /**
     * @var string
     */
    private $className;

    /**
     * 
     * @param Traversable|array $tasks
     * @param \FKSDB\Components\Forms\Controls\ServiceSubmit $submitService
     * @param string|null $label
     */
    function __construct($tasks, ModelContestant $contestant, ServiceSubmit $submitService, ServiceTaskStudyYear $serviceTaskStudyYear, $acYear, $label = null) {
        parent::__construct($label);
        $this->monitor('FKS\Application\IJavaScriptCollector');

        $this->setTasks($tasks);
        $this->submitService = $submitService;
        $this->contestant = $contestant;
        $this->serviceTaskStudyYear = $serviceTaskStudyYear;
        $this->acYear = $acYear;
    }

    protected function attached($component) {
        parent::attached($component);
        if ($component instanceof IJavaScriptCollector) {
            $component->registerJSFile('js/submitFields.js');
        }
    }

    public function getClassName() {
        return $this->className;
    }

    public function setClassName($className) {
        $this->className = $className;
    }

    private function setTasks($tasks) {
        $this->tasks = array();
        foreach ($tasks as $task) {
            $this->tasks[$task->tasknr] = $task;
        }
    }

    private function getTask($taskId) {
        foreach ($this->tasks as $task) {
            if ($task->task_id == $taskId) {
                return $task;
            }
        }
        return null;
    }

    private function isTaskDisabled($taskId) {
        return false;
        // TODO loading person history took too long, implement better study_year detection
//        $history = $this->contestant->getPerson()->getHistory($this->acYear);
//        $studyYear = ($history && isset($history->study_year)) ? $history->study_year : null;
//        if ($studyYear === null) {
//            return false;
//        }
//        $taskStudyYear = $this->serviceTaskStudyYear->findByPrimary(array(
//            'task_id' => $taskId,
//            'study_year' => $studyYear,
//        ));
//        return $taskStudyYear === null;
    }

    /**
     * @return   Html
     */
    public function getControl() {
        $control = parent::getControl();

        $control->addClass($this->getClassName());
        $control->value = $this->rawValue;
        $control->addStyle('width:600px');

        $control->data['contestant'] = $this->contestant->ct_id;
        foreach ($this->getClientData() as $key => $value) {
            $control->data[$key] = $value;
        }

        return $control;
    }

    public function getRawValue() {
        return $this->rawValue;
    }

    /**
     * 
     * @param array|Traversable|string $value of ModelTask
     * @return \FKSDB\Components\Forms\Controls\ContestantSubmits
     * @throws InvalidArgumentException
     */
    public function setValue($value) {
        if (!$value) {
            $this->rawValue = $this->serializeValue(array());
            $this->value = $this->deserializeValue($this->rawValue);
        } else if (is_string($value)) {
            $this->rawValue = $value;
            $this->value = $this->deserializeValue($value);
        } else {
            $this->rawValue = $this->serializeValue($value);
            $this->value = $value;
        }

        return $this;
    }

    private function serializeValue($value) {
        $result = array();

        foreach ($value as $submit) {
            if (!$submit) {
                continue;
            }

            $tasknr = $submit->getTask()->tasknr;

            if (isset($result[$tasknr])) {
                throw new InvalidArgumentException("Task with no. $tasknr is present multiple times in passed value.");
            }
            $result[(int) $tasknr] = $this->serializeSubmit($submit);
        }

        $dummySubmit = $this->submitService->createNew();
        foreach ($this->tasks as $tasknr => $task) {
            if (isset($result[$tasknr])) {
                continue;
            }

            $dummySubmit->task_id = $task->task_id;
            $result[$tasknr] = $this->serializeSubmit($dummySubmit);
        }

        ksort($result);

        return json_encode($result);
    }

    private function deserializeValue($value) {
        $value = json_decode($value, true);

        $result = array();

        foreach ($value as $tasknr => $serializedSubmit) {
            if (!$serializedSubmit) {
                continue;
            }

            $result[] = $this->deserializeSubmit($serializedSubmit, $tasknr);
        }

        return $result;
    }

    private function serializeSubmit(ModelSubmit $submit) {
        $data = $submit->toArray();
        $format = $this->sourceToFormat($submit->source);
        $data['submitted_on'] = $data['submitted_on'] ? $data['submitted_on']->format($format) : null;
        $data['task'] = array(
            'label' => $this->getTask($submit->task_id)->label,
            'disabled' => $this->isTaskDisabled($submit->task_id),
        ); // ORM workaround
        return $data;
    }

    private function deserializeSubmit($data, $tasknr) {
        unset($data['submit_id']); // security
        $data['ct_id'] = $this->contestant->ct_id; // security
        $format = $this->sourceToFormat($data['source'], true);
        $data['submitted_on'] = $data['submitted_on'] ? DateTime::createFromFormat($format, $data['submitted_on']) : null;
        $data = FormUtils::emptyStrToNull($data);

        $ctId = $data['ct_id'];
        $taskId = $data['task_id'];

        $submit = $this->submitService->findByContestant($ctId, $taskId);
        if (!$submit) {
            $submit = $this->submitService->createNew();
        }

        $this->submitService->updateModel($submit, $data);
        return $submit;
    }

    /**
     * Workaround to perform server-side conversion of dates.
     * 
     * @todo Improve client side so that this is not needed anymore.
     * @param string $source
     * @return string
     */
    private function sourceToFormat($source, $parse = false) {
        switch ($source) {
            case ModelSubmit::SOURCE_POST:
                return ($parse ? '!' : '') . 'Y-m-d';
                break;
            default:
                return DateTime::ISO8601;
                break;
        }
    }

}
