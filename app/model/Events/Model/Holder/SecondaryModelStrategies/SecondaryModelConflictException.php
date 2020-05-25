<?php


namespace FKSDB\Events\Model\Holder\SecondaryModelStrategies;

use FKSDB\Events\Model\Holder\BaseHolder;
use FKSDB\ORM\IModel;
use Nette\Database\Table\ActiveRow;
use RuntimeException;

/**
 * Class SecondaryModelConflictException
 * *
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
     * @param IModel[] $conflicts
     * @param null $code
     * @param null $previous
     */
    public function __construct(BaseHolder $baseHolder, array $conflicts, $code = null, $previous = null) {
        parent::__construct($this->createMessage($baseHolder->getModel(), $conflicts), $code, $previous);
        $this->baseHolder = $baseHolder;
        $this->conflicts = $conflicts;
    }

    /**
     * @param IModel $model
     * @param ActiveRow[]|IModel[] $conflicts
     * @return string
     */
    private function createMessage(IModel $model, array $conflicts) {
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
    public function getConflicts(): array {
        return $this->conflicts;
    }

}
