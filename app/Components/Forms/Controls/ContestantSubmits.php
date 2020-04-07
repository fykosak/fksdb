<?php

namespace FKSDB\Components\Forms\Controls;

use FKSDB\Application\IJavaScriptCollector;
use FKSDB\Components\ClientDataTrait;
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
     * @var \FKSDB\ORM\Services\ServiceSubmit
     */
    private $submitService;
    /**
     * @var \FKSDB\ORM\Models\ModelContestant
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
     * @param \FKSDB\ORM\Models\ModelContestant $contestant
     * @param \FKSDB\ORM\Services\ServiceSubmit $submitService
     * @param $acYear
     * @param string|null $label
     */
    function __construct($tasks, ModelContestant $contestant, ServiceSubmit $submitService, $acYear, $label = null) {
        parent::__construct($label);
        $this->monitor(IJavaScriptCollector::class);

        $this->setTasks($tasks);
        $this->submitService = $submitService;
        $this->contestant = $contestant;
        $this->acYear = $acYear;
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
     * @return \FKSDB\Components\Forms\Controls\ContestantSubmits
     * @throws InvalidArgumentException
     */
    public function setValue($value) {
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
     * @param \FKSDB\ORM\Models\ModelSubmit $submit
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
     * @return \FKSDB\ORM\AbstractModelSingle|\FKSDB\ORM\Models\ModelSubmit|null
     */
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
     * @param bool $parse
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
