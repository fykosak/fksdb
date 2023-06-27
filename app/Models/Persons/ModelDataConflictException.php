<?php

declare(strict_types=1);

namespace FKSDB\Models\Persons;

use Nette\Http\IResponse;

class ModelDataConflictException extends \RuntimeException
{
    private iterable $conflicts;

    public function __construct(iterable $conflicts, ?\Throwable $previous = null)
    {
        parent::__construct(_('Some fields don\'t match an existing record.'), IResponse::S409_CONFLICT, $previous);
        $this->conflicts = $conflicts;
    }

    public function getConflicts(): iterable
    {
        return $this->conflicts;
    }
}
