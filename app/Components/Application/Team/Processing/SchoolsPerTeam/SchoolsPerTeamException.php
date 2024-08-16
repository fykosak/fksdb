<?php

declare(strict_types=1);

namespace FKSDB\Components\Application\Team\Processing\SchoolsPerTeam;

use FKSDB\Models\ORM\Models\SchoolModel;
use Nette\InvalidStateException;

class SchoolsPerTeamException extends InvalidStateException
{
    /**
     * @phpstan-param SchoolModel[] $schools
     */
    public function __construct(array $schools)
    {
        parent::__construct(
            sprintf(
                _('Only 2 different schools can be represented by the team (got %d: %s).'),
                count($schools),
                join(', ', array_map(fn(SchoolModel $school) => '"' . $school->name_abbrev . '"', $schools))
            )
        );
    }
}
