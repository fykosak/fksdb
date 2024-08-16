<?php

declare(strict_types=1);

namespace FKSDB\Components\Application\Team\Processing\SchoolRequirement;

use FKSDB\Models\ORM\Models\PersonModel;
use FKSDB\Models\ORM\Models\StudyYear;
use Nette\InvalidStateException;

class SchoolRequirementProcessingException extends InvalidStateException
{
    public function __construct(StudyYear $studyYear, PersonModel $person, \Throwable $previous = null)
    {
        parent::__construct(
            sprintf(
                _('The school is required for person "%s" and study year "%s".'),
                $person->getFullName(),
                $studyYear->label()
            ),
            0,
            $previous
        );
    }
}
