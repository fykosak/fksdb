<?php


namespace FKSDB\Events\Model\Holder\SecondaryModelStrategies;

use FKSDB\Events\Model\Holder\BaseHolder;
use FKSDB\ORM\AbstractModelSingle;
use FKSDB\ORM\IModel;
use RuntimeException;

/**
 * Class SecondaryModelConflictException
 * *
 */
class SecondaryModelConflictException extends RuntimeException {

    private BaseHolder $baseHolder;

    /**
     * @var IModel[]
     */
    private $conflicts;

    /**
     * SecondaryModelConflictException constructor.
     * @param BaseHolder $baseHolder
     * @param $conflicts
     * @param null $code
     * @param null $previous
     */
    public function __construct(BaseHolder $baseHolder, $conflicts, $code = null, $previous = null) {
        parent::__construct($this->createMessage($baseHolder->getModel(), $conflicts), $code, $previous);
        $this->baseHolder = $baseHolder;
        $this->conflicts = $conflicts;
    }

    /**
     * @param IModel $model
     * @param IModel[] $conflicts
     * @return string
     */
    private function createMessage(IModel $model, $conflicts) {
        $ids = null;
        foreach ($conflicts as $conflict) {
            $ids = $conflict->getPrimary();
        }
        $id = $model->getPrimary(false) ?: 'null';
        return sprintf('Model with PK %s conflicts with other models: %s.', $id, $ids);
    }

    public function getBaseHolder(): BaseHolder {
        return $this->baseHolder;
    }

    /**
     * @return IModel[]
     */
    public function getConflicts() {
        return $this->conflicts;
    }
}
