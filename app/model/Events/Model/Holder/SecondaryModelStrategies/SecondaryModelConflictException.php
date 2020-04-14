<?php


namespace Events\Model\Holder\SecondaryModelStrategies;

use Events\Model\Holder\BaseHolder;
use FKSDB\ORM\IModel;
use RuntimeException;

/**
 * Class SecondaryModelConflictException
 * @package Events\Model\Holder\SecondaryModelStrategies
 */
class SecondaryModelConflictException extends RuntimeException {

    /**
     * @var BaseHolder
     */
    private $baseHolder;

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
    function __construct(BaseHolder $baseHolder, $conflicts, $code = null, $previous = null) {
        parent::__construct($this->createMessage($baseHolder->getModel(), $conflicts), $code, $previous);
        $this->baseHolder = $baseHolder;
        $this->conflicts = $conflicts;
    }

    /**
     * @param IModel $model
     * @param $conflicts
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

    /**
     * @return BaseHolder
     */
    public function getBaseHolder() {
        return $this->baseHolder;
    }

    /**
     * @return IModel[]
     */
    public function getConflicts() {
        return $this->conflicts;
    }

}
