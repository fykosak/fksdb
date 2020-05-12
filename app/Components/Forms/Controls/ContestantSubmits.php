<?php

namespace FKSDB\Components\Forms\Controls;

use FKSDB\Application\IJavaScriptCollector;
use FKSDB\Components\ClientDataTrait;
use FKSDB\ORM\AbstractModelSingle;
use FKSDB\ORM\Models\ModelContestant;
use FKSDB\ORM\Models\ModelSubmit;
use FKSDB\ORM\Services\ServiceSubmit;
use FormUtils;
use InvalidArgumentException;
use Nette\Utils\DateTime;
use Nette\Forms\Controls\BaseControl;
use Nette\Utils\Html;
use Traversable;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
class ContestantSubmits extends BaseControl {

    use ClientDataTrait;

    /**
     * @var Traversable|array of FKSDB\ORM\Models\ModelTask
     */
    private $tasks;

    /**
     * @var string
     */
    private $rawValue;

    /**
     * @var ServiceSubmit
     */
    private $serviceSubmit;
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
     * @param ModelContestant $contestant
     * @param ServiceSubmit $submitService
     * @param $acYear
     * @param string|null $label
     */
    function __construct($tasks, ModelContestant $contestant, ServiceSubmit $submitService, $acYear, $label = null) {
        $this->submitService = $submitService;
        $this->contestant = $contestant;
        $this->acYear = $acYear;
        $this->setTasks($tasks);
        parent::__construct($label);
        $this->monitor(IJavaScriptCollector::class);
    }

    /**
     * @param $component
     */
    protected function attached($component) {
        parent::attached($component);
        if ($component instanceof IJavaScriptCollector) {
            $component->registerJSFile('js/submitFields.js');
        }
    }

    /**
     * @return string
     */
    public function getClassName() {
        return $this->className;
    }

    /**
     * @param $className
     */
    public function setClassName($className) {
        $this->className = $className;
    }

    /**
     * @param $tasks
     */
    private function setTasks($tasks) {
        $this->tasks = [];
        foreach ($tasks as $task) {
            $this->tasks[$task->tasknr] = $task;
        }
    }

    /**
     * @param $taskId
     * @return mixed|null
     */
    private function getTask($taskId) {
        foreach ($this->tasks as $task) {
            if ($task->task_id == $taskId) {
                return $task;
            }
        }
        return null;
    }

    /**
     * @return bool
     */
    private function isTaskDisabled() {
        return false;
    }

    /**
     * @return   Html
     */
    public function getControl() {
        $control = parent::getControl();

        $control->addClass($this->getClassName());
        $control->value = $this->rawValue;

        $control->data['contestant'] = $this->contestant->ct_id;
        foreach ($this->getClientData() as $key => $value) {
            $control->data[$key] = $value;
        }

        return $control;
    }

    /**
     * @return string
     */
    public function getRawValue() {
        return $this->rawValue;
    }

    /**
     *
     * @param array|Traversable|string $value of FKSDB\ORM\Models\ModelTask
     * @return ContestantSubmits
     * @throws InvalidArgumentException
     */
    public function setValue($value) {
        if (is_null($value) && is_null($this->rawValue)) {
            return $this;
        }
        if (!$value) {
            $this->rawValue = $this->serializeValue([]);
            $this->value = $this->deserializeValue($this->rawValue);
        } elseif (is_string($value)) {
            $this->rawValue = $value;
            $this->value = $this->deserializeValue($value);
        } else {
            $this->rawValue = $this->serializeValue($value);
            $this->value = $value;
        }

        return $this;
    }

    /**
     * @param $value
     * @return false|string
     */
    private function serializeValue($value) {
        $result = [];

        foreach ($value as $submit) {
            if (!$submit) {
                continue;
            }

            $tasknr = $submit->getTask()->tasknr;

            if (isset($result[$tasknr])) {
                throw new InvalidArgumentException("Task with no. $tasknr is present multiple times in passed value.");
            }
            $result[(int)$tasknr] = $this->serializeSubmit($submit);
        }
        $dummySubmit = $this->submitService->createNew();
        $data = $dummySubmit->toArray();

        foreach ($this->tasks as $tasknr => $task) {
            if (isset($result[$tasknr])) {
                continue;
            }
            $result[$tasknr] = $this->serializeDummySubmit($data, $task->task_id);
        }

        ksort($result);

        return json_encode($result);
    }

    /**
     * @param $value
     * @return array
     */
    private function deserializeValue($value) {
        $value = json_decode($value, true);

        $result = [];

        foreach ($value as $tasknr => $serializedSubmit) {
            if (!$serializedSubmit) {
                continue;
            }
            $result[] = $this->deserializeSubmit($serializedSubmit, $tasknr);
        }
        return $result;
    }

    /**
     * @param $data
     * @param int $taskId
     * @return array
     */
    private function serializeDummySubmit($data, int $taskId) {
        $data['submitted_on'] = null;

        $data['task'] = [
            'label' => $this->getTask($taskId)->label,
            'disabled' => $this->isTaskDisabled(),
        ]; // ORM workaround
        return $data;
    }

    /**
     * @param ModelSubmit $submit
     * @return array
     */
    private function serializeSubmit(ModelSubmit $submit) {
        $data = $submit->toArray();
        $format = $this->sourceToFormat($submit->source);
        $data['submitted_on'] = $data['submitted_on'] ? $data['submitted_on']->format($format) : null;
        $data['task'] = [
            'label' => $this->getTask($submit->task_id)->label,
            'disabled' => $this->isTaskDisabled(),
        ]; // ORM workaround
        return $data;
    }

    /**
     * @param $data
     * @param $tasknr
     * @return AbstractModelSingle|ModelSubmit|null
     */
    private function deserializeSubmit($data, $tasknr) {
        unset($data['submit_id']); // security
        $data['ct_id'] = $this->contestant->ct_id; // security
        $format = $this->sourceToFormat($data['source'], true);
        $data['submitted_on'] = $data['submitted_on'] ? DateTime::createFromFormat($format, $data['submitted_on']) : null;
        $data = FormUtils::emptyStrToNull($data);

        $ctId = $data['ct_id'];
        $taskId = $data['task_id'];

        $submit = $this->serviceSubmit->findByContestant($ctId, $taskId);
        if (!$submit) {
            $this->submitService->createNewModel($data);
        } else {
            $this->submitService->updateModel2($submit, $data);
        }
        return $submit;
    }

    /**
     * Workaround to perform server-side conversion of dates.
     *
     * @param string $source
     * @param bool $parse
     * @return string
     * @todo Improve client side so that this is not needed anymore.
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
