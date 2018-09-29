<?php

namespace Events\Model\Holder\SecondaryModelStrategies;

use Events\Model\Holder\BaseHolder;
use ORM\IModel;
use RuntimeException;

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
        foreach ($conflicts as $conflict) {
            $ids = $conflict->getPrimary();
        }
        $id = $model->getPrimary(false) ? : 'null';
        return sprintf('Model with PK %s conflicts with other models: %s.', $id, $ids);
    }

    public function getBaseHolder() {
        return $this->baseHolder;
    }

    public function getConflicts() {
        return $this->conflicts;
    }
}
