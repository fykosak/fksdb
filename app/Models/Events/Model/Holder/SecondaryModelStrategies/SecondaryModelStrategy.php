<?php

namespace FKSDB\Models\Events\Model\Holder\SecondaryModelStrategies;

use FKSDB\Models\Events\Model\Holder\BaseHolder;
use FKSDB\Models\ORM\IModel;
use FKSDB\Models\ORM\IService;
use Nette\InvalidStateException;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
abstract class SecondaryModelStrategy {

    /**
     * @param BaseHolder[] $holders
     * @param IModel[] $models
     * @return void
     */
    public function setSecondaryModels(array $holders, $models): void {
        $filledHolders = 0;
        foreach ($models as $secondaryModel) {
            $holders[$filledHolders]->setModel($secondaryModel);
            if (++$filledHolders > count($holders)) {
                throw new InvalidStateException('Supplied more than expected secondary models.');
            }
        }
        for (; $filledHolders < count($holders); ++$filledHolders) {
            $holders[$filledHolders]->setModel(null);
        }
    }

    /**
     * @param IService $service
     * @param string|null $joinOn
     * @param string|null $joinTo
     * @param BaseHolder[] $holders
     * @param IModel|null $primaryModel
     * @return void
     */
    public function loadSecondaryModels(IService $service, $joinOn, $joinTo, array $holders, IModel $primaryModel = null): void {
        $table = $service->getTable();
        if ($primaryModel) {
            $joinValue = $joinTo ? $primaryModel[$joinTo] : $primaryModel->getPrimary();
            $secondary = $table->where($joinOn, $joinValue);
            if ($joinTo) {
                $event = reset($holders)->getEvent();
                $secondary->where(BaseHolder::EVENT_COLUMN, $event->getPrimary());
            }
        } else {
            $secondary = [];
        }
        $this->setSecondaryModels($holders, $secondary);
    }

    /**
     * @param IService $service
     * @param string|null $joinOn
     * @param string|null $joinTo
     * @param BaseHolder[] $holders
     * @param IModel $primaryModel
     * @return void
     */
    public function updateSecondaryModels(IService $service, $joinOn, $joinTo, array $holders, IModel $primaryModel): void {
        $joinValue = $joinTo ? $primaryModel[$joinTo] : $primaryModel->getPrimary();
        foreach ($holders as $baseHolder) {
            $joinData = [$joinOn => $joinValue];
            if ($joinTo) {
                $existing = $service->getTable()->where($joinData)->where(BaseHolder::EVENT_COLUMN, $baseHolder->getEvent()->getPrimary());
                $conflicts = [];
                foreach ($existing as $secondaryModel) {
                    if ($baseHolder->getModel()->getPrimary(false) !== $secondaryModel->getPrimary()) {
                        $conflicts[] = $secondaryModel;
                    }
                }
                if ($conflicts) {
                    // TODO this could be called even for joining via PK
                    $this->resolveMultipleSecondaries($baseHolder, $conflicts, $joinData);
                }
            }
            $service->updateModel($baseHolder->getModel(), $joinData);
        }
    }

    abstract protected function resolveMultipleSecondaries(BaseHolder $holder, array $secondaries, array $joinData): void;
}
