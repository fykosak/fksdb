<?php

namespace FKSDB\Models\Events\Model\Holder\SecondaryModelStrategies;

use FKSDB\Models\Events\Model\Holder\BaseHolder;

class SecondaryModelDataConflictException extends SecondaryModelConflictException
{

    private array $conflictData;

    public function __construct(array $conflictData, BaseHolder $baseHolder, iterable $conflicts, ?int $code = null, ?\Throwable $previous = null)
    {
        parent::__construct($baseHolder, $conflicts, $code, $previous);
        $this->conflictData = $conflictData;
        $this->message .= sprintf(' (%s)', implode(', ', $this->conflictData));
    }

    public function getConflictData(): array
    {
        return $this->conflictData;
    }
}
