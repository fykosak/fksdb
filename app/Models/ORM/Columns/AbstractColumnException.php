<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Columns;

use Nette\Application\BadRequestException;

class AbstractColumnException extends BadRequestException
{
    public function __construct(string $table, string $field, ?\Throwable $previous = null)
    {
        parent::__construct(
            sprintf('Field %s.%s is abstract. Field does not represent any single column.', $table, $field),
            500,
            $previous
        );
    }
}
