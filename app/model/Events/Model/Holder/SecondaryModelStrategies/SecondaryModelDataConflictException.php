<?php

namespace FKSDB\Events\Model\Holder\SecondaryModelStrategies;

use FKSDB\Events\Model\Holder\BaseHolder;

/**
 * Class SecondaryModelDataConflictException
 * *
 */
class SecondaryModelDataConflictException extends SecondaryModelConflictException {

    private array $conflictData;

    /**
     * SecondaryModelDataConflictException constructor.
     * @param array $conflictData
     * @param BaseHolder $baseHolder
     * @param iterable $conflicts
     * @param null $code
     * @param null $previous
     */
    public function __construct(array $conflictData, BaseHolder $baseHolder, iterable $conflicts, $code = null, $previous = null) {
        parent::__construct($baseHolder, $conflicts, $code, $previous);
        $this->conflictData = $conflictData;
        $this->message .= sprintf(' (%s)', implode(', ', $this->conflictData));
    }

    public function getConflictData(): array {
        return $this->conflictData;
    }
}
