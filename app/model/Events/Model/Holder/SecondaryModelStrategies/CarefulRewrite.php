<?php

namespace FKSDB\Events\Model\Holder\SecondaryModelStrategies;

use FKSDB\Events\Model\Holder\BaseHolder;
use FKSDB\ORM\IModel;
use FKSDB\ORM\IService;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
class CarefulRewrite extends SecondaryModelStrategy {

    private $safeKeys = [];

    /**
     * CarefulRewrite constructor.
     * @param array $safeKeys
     */
    public function __construct($safeKeys = []) {
        $this->safeKeys = $safeKeys;
    }

    /**
     * @param BaseHolder $holder
     * @param $secondaries
     * @param $joinData
     * @return mixed|void
     */
    protected function resolveMultipleSecondaries(BaseHolder $holder, $secondaries, $joinData) {
        if (count($secondaries) > 1) {
            throw new SecondaryModelConflictException($holder, $secondaries);
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

    /**
     * @param IModel $currentModel
     * @param IModel $foundModel
     * @param $joinData
     * @param IService $service
     * @return array
     */
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

    /**
     * @param IModel $currentModel
     * @param IModel $foundModel
     * @param $joinData
     * @param IService $service
     */
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
