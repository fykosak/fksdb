<?php

namespace FKSDB\Models\Persons;

use FKSDB\Components\Forms\Controls\ReferencedId;
use Nette\Http\Response;

class ModelDataConflictException extends \RuntimeException {

    private iterable $conflicts;

    private ReferencedId $referencedId;

    public function __construct(iterable $conflicts, ?\Throwable $previous = null) {
        parent::__construct(null, Response::S409_CONFLICT, $previous);
        $this->conflicts = $conflicts;
    }

    public function getConflicts(): iterable {
        return $this->conflicts;
    }

    public function getReferencedId(): ReferencedId {
        return $this->referencedId;
    }

    public function setReferencedId(ReferencedId $referencedId): void {
        $this->referencedId = $referencedId;
    }
}
