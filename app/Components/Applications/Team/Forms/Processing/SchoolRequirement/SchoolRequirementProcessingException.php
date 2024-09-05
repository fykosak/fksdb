<?php

declare(strict_types=1);

namespace FKSDB\Components\Applications\Team\Forms\Processing\SchoolRequirement;

use FKSDB\Components\EntityForms\Processing\ProcessingException;
use FKSDB\Models\ORM\Models\PersonModel;
use FKSDB\Models\ORM\Models\StudyYear;

class SchoolRequirementProcessingException extends ProcessingException
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
