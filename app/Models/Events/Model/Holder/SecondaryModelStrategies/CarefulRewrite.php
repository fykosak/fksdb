<?php

namespace FKSDB\Models\Events\Model\Holder\SecondaryModelStrategies;

use FKSDB\Models\Events\Model\Holder\BaseHolder;
use FKSDB\Models\ORM\ServicesMulti\AbstractServiceMulti;
use Fykosak\NetteORM\AbstractService;
use Nette\Database\Table\ActiveRow;

class CarefulRewrite extends SecondaryModelStrategy
{

    private array $safeKeys;

    public function __construct(array $safeKeys = [])
    {
        $this->safeKeys = $safeKeys;
    }

    protected function resolveMultipleSecondaries(BaseHolder $holder, array $secondaries, array $joinData): void
    {
        if (count($secondaries) > 1) {
            throw new SecondaryModelConflictException($holder, $secondaries);
        }

        $foundModel = reset($secondaries);
        $conflicts = $this->getConflicts($foundModel, $joinData, $holder->getService(), $holder);

        if ($conflicts) {
            throw new SecondaryModelDataConflictException($conflicts, $holder, $secondaries);
        }

        $this->updateFoundModel($foundModel, $joinData, $holder->getService(), $holder);
        $holder->setModel($foundModel); // "swap" models
    }

    /**
     * @param ActiveRow $foundModel
     * @param array $joinData
     * @param AbstractService|AbstractServiceMulti $service
     * @param BaseHolder $holder
     * @return array
     */
    private function getConflicts(ActiveRow $foundModel, array $joinData, $service, BaseHolder $holder): array
    {
        $foundArray = $foundModel->toArray();
        $result = [];
        foreach ($holder->data as $key => $value) {
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
     * @param ActiveRow $foundModel
     * @param array $joinData
     * @param AbstractService|AbstractServiceMulti $service
     * @param BaseHolder $holder
     */
    private function updateFoundModel(ActiveRow $foundModel, array $joinData, $service, BaseHolder $holder): void
    {
        $data = [];
        foreach ($holder->data as $key => $value) {
            if ($key === $service->getTable()->getPrimary() || array_key_exists($key, $joinData)) {
                continue;
            }
            $data[$key] = $value;
        }
        $service->updateModel($foundModel, $data);
    }
}
