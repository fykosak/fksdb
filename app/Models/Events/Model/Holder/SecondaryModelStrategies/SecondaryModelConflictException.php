<?php

declare(strict_types=1);

namespace FKSDB\Models\Events\Model\Holder\SecondaryModelStrategies;

use FKSDB\Models\Events\Model\Holder\BaseHolder;
use Fykosak\NetteORM\Model;

class SecondaryModelConflictException extends \RuntimeException
{
    private BaseHolder $baseHolder;

    private iterable $conflicts;

    public function __construct(
        BaseHolder $baseHolder,
        iterable $conflicts,
        int $code = 0,
        ?\Throwable $previous = null
    ) {
        parent::__construct($this->createMessage($baseHolder->getModel2(), $conflicts), $code, $previous);
        $this->baseHolder = $baseHolder;
        $this->conflicts = $conflicts;
    }

    private function createMessage(?Model $model, iterable $conflicts): string
    {
        $ids = null;
        /** @var Model $conflict */
        foreach ($conflicts as $conflict) {
            $ids = $conflict->getPrimary();
        }
        $id = $model ? ($model->getPrimary(false) ?: 'null') : 'null';
        return sprintf('Model with PK %s conflicts with other models: %s.', $id, $ids);
    }

    public function getBaseHolder(): BaseHolder
    {
        return $this->baseHolder;
    }

    public function getConflicts(): iterable
    {
        return $this->conflicts;
    }
}
