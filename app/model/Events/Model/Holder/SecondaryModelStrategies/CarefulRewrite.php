<?php

namespace Events\Model\Holder\SecondaryModelStrategies;

use Events\Model\Holder\BaseHolder;
use ORM\IModel;
use ORM\IService;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
class CarefulRewrite extends SecondaryModelStrategy {

    private $safeKeys = [];

    function __construct($safeKeys = []) {
        $this->safeKeys = $safeKeys;
    }

    protected function resolveMultipleSecondaries(BaseHolder $holder, $secondaries, $joinData) {
        if (count($secondaries) > 1) {
            throw new SecondaryModelConflictException($holder->getModel(), $secondaries);
        }

        $currentModel = $holder->getModel();
        $foundModel = reset($secondaries);
        $conflicts = $this->getConflicts($currentModel, $foundModel, $joinData, $holder->getService());

        if ($conflicts) {
            throw new SecondaryModelDataConflictException($conflicts, $holder, $secondaries);
        }

        $this->updateFoundModel($currentModel, $foundModel, $joinData, $holder->getService());
        $holder->setModel($foundModel); // "swap" models
    }

    private function getConflicts(IModel $currentModel, IModel $foundModel, $joinData, IService $service) {
        $currentArray = $currentModel->toArray();
        $foundArray = $foundModel->toArray();
        $result = [];
        foreach ($currentArray as $key => $value) {
            if ($key === $service->getTable()->getPrimary() || array_key_exists($key, $joinData)) {
                continue;
            }
            if (in_array($key, $this->safeKeys)) {
                continue;
            }

            if (isset($foundArray[$key]) && $foundArray[$key] != $value) {
                $result[] = $key;
            }
        }
        return $result;
    }

    private function updateFoundModel(IModel $currentModel, IModel $foundModel, $joinData, IService $service) {
        $currentArray = $currentModel->toArray();
        $data = [];
        foreach ($currentArray as $key => $value) {
            if ($key === $service->getTable()->getPrimary() || array_key_exists($key, $joinData)) {
                continue;
            }
            $data[$key] = $value;
        }
        $service->updateModel($foundModel, $data);
    }

}

class SecondaryModelDataConflictException extends SecondaryModelConflictException {

    private $conflictData;

    function __construct($conflictData,  BaseHolder $baseHolder, $conflicts, $code = null, $previous = null) {
        parent::__construct($baseHolder, $conflicts, $code, $previous);
        $this->conflictData = $conflictData;
        $this->message .= sprintf(' (%s)', implode(', ', $this->conflictData));
    }

    public function getConflictData() {
        return $this->getConflictData();
    }

}
