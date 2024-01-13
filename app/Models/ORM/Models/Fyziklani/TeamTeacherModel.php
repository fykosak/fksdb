<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Models\Fyziklani;

use FKSDB\Models\MachineCode\MachineCode;
use FKSDB\Models\ORM\Models\PersonModel;
use Fykosak\NetteORM\Model\Model;

/**
 * @property-read int $fyziklani_team_teacher_id
 * @property-read int $person_id
 * @property-read PersonModel $person
 * @property-read int $fyziklani_team_id
 * @property-read TeamModel2 $fyziklani_team
 */
final class TeamTeacherModel extends Model
{
    public function createMachineCode(): ?string
    {
        try {
            return MachineCode::createHash($this->person, $this->fyziklani_team->event->getSalt());
        } catch (\Throwable $exception) {
            return null;
        }
    }
}
