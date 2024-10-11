<?php

declare(strict_types=1);

namespace FKSDB\Models\Persons;

use Nette\Http\IResponse;

/**
 * @phpstan-type Conflicts array<string,scalar|null>|array<string,array<string,scalar|null>>
 */
class ModelDataConflictException extends \RuntimeException
{
    /**
     * @phpstan-var Conflicts
     */
    private array $conflicts;

    /**
     * @phpstan-param Conflicts $conflicts
     */
    public function __construct(array $conflicts, ?\Throwable $previous = null)
    {
        parent::__construct(_('Some fields don\'t match an existing record.'), IResponse::S409_CONFLICT, $previous);
        $this->conflicts = $conflicts;
    }

    /**
     * @phpstan-return Conflicts
     */
    public function getConflicts(): array
    {
        return $this->conflicts;
    }
}
