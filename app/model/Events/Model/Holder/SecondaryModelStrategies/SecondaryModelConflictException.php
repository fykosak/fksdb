<?php


namespace FKSDB\Events\Model\Holder\SecondaryModelStrategies;

use FKSDB\Events\Model\Holder\BaseHolder;
use FKSDB\ORM\IModel;
use Nette\Database\Table\ActiveRow;
use RuntimeException;

/**
 * Class SecondaryModelConflictException
 *
 */
class SecondaryModelConflictException extends RuntimeException {

    private BaseHolder $baseHolder;

    private iterable $conflicts;

    public function __construct(BaseHolder $baseHolder, iterable $conflicts, ?int $code = null, ?\Throwable $previous = null) {
        parent::__construct($this->createMessage($baseHolder->getModel(), $conflicts), $code, $previous);
        $this->baseHolder = $baseHolder;
        $this->conflicts = $conflicts;
    }

    private function createMessage(IModel $model, iterable $conflicts): string {
        $ids = null;
        /** @var ActiveRow $conflict */
        foreach ($conflicts as $conflict) {
            $ids = $conflict->getPrimary();
        }
        $id = $model->getPrimary(false) ?: 'null';
        return sprintf('Model with PK %s conflicts with other models: %s.', $id, $ids);
    }

    public function getBaseHolder(): BaseHolder {
        return $this->baseHolder;
    }

    public function getConflicts(): iterable {
        return $this->conflicts;
    }

}
