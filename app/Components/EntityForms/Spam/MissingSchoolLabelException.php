<?php

declare(strict_types=1);

namespace FKSDB\Components\EntityForms\Spam;

class MissingSchoolLabelException extends \Exception
{
    public function __construct(?\Throwable $previous = null)
    {
        parent::__construct(_('School label must exist.'), 0, $previous);
    }
}
