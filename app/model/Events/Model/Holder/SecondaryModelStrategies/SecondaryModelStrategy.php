<?php

namespace Events\Model\Holder\SecondaryModelStrategies;

use Events\Model\Holder\BaseHolder;
use Nette\InvalidStateException;
use ORM\IModel;
use ORM\IService;
use RuntimeException;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
abstract class SecondaryModelStrategy {

    public function setSecondaryModels($holders, $models) {
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

    public function loadSecondaryModels(IService $service, $joinOn, $joinTo, $holders, IModel $primaryModel = null) {
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

    public function updateSecondaryModels(IService $service, $joinOn, $joinTo, $holders, IModel $primaryModel) {
        $joinValue = $joinTo ? $primaryModel[$joinTo] : $primaryModel->getPrimary();
        foreach ($holders as $baseHolder) {
            $joinData = array($joinOn => $joinValue);
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

    abstract protected function resolveMultipleSecondaries(BaseHolder $holder, $secondaries, $joinData);
}

class SecondaryModelConflictException extends RuntimeException {

    /**
     * @var BaseHolder
     */
    private $baseHolder;

    /**
     * @var IModel[]
     */
    private $conflicts;

    function __construct(BaseHolder $baseHolder, $conflicts, $code = null, $previous = null) {
        parent::__construct($this->createMessage($baseHolder->getModel(), $conflicts), $code, $previous);
        $this->baseHolder = $baseHolder;
        $this->conflicts = $conflicts;
    }

    private function createMessage(IModel $model, $conflicts) {
        $ids = null;
        foreach ($conflicts as $conflict) {
            $ids = $conflict->getPrimary();
        }
        $id = $model->getPrimary(false) ?: 'null';
        return sprintf('Model with PK %s conflicts with other models: %s.', $id, $ids);
    }

    public function getBaseHolder() {
        return $this->baseHolder;
    }

    public function getConflicts() {
        return $this->conflicts;
    }

}
