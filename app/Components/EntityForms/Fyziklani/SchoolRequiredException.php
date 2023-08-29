<?php

declare(strict_types=1);

namespace FKSDB\Components\EntityForms\Fyziklani;

use FKSDB\Models\ORM\Models\PersonModel;
use FKSDB\Models\ORM\Models\StudyYear;
use Nette\InvalidStateException;

class SchoolRequiredException extends InvalidStateException
{
    public function __construct(StudyYear $studyYear, PersonModel $person, \Throwable $previous = null)
    {
        parent::__construct(
            sprintf(_('The school is required for this %s and %s'), $person->getFullName(), $studyYear->label()),
            0,
            $previous
        );
    }
}
