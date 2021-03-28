<?php

namespace FKSDB\Models\Events\Model\Holder\SecondaryModelStrategies;

use FKSDB\Models\Events\Model\Holder\BaseHolder;
use FKSDB\Models\ORM\IService;
use Nette\Database\Table\ActiveRow;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
class CarefulRewrite extends SecondaryModelStrategy {

    private array $safeKeys;

    public function __construct(array $safeKeys = []) {
        $this->safeKeys = $safeKeys;
    }

    protected function resolveMultipleSecondaries(BaseHolder $holder, array $secondaries, array $joinData): void {
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

    private function getConflicts(ActiveRow $currentModel, ActiveRow $foundModel, array $joinData, IService $service): array {
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

    private function updateFoundModel(ActiveRow $currentModel, ActiveRow $foundModel, array $joinData, IService $service): void {
        $currentArray = $currentModel->toArray();
        $data = [];
        foreach ($currentArray as $key => $value) {
            if ($key === $service->getTable()->getPrimary() || array_key_exists($key, $joinData)) {
                continue;
            }
            $data[$key] = $value;
        }
        $service->updateModelLegacy($foundModel, $data);
    }

}
