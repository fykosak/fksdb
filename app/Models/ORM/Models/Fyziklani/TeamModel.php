<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Models\Fyziklani;

use FKSDB\Models\ORM\DbNames;
use FKSDB\Models\ORM\Models\ModelPerson;
use Fykosak\NetteORM\Model;
use Nette\Database\Table\ActiveRow;
use Nette\Database\Table\GroupedSelection;
use Nette\Security\Resource;

/**
 * @property-read string category
 * @property-read string name
 * @property-read int e_fyziklani_team_id
 * @property-read int event_id
 * @property-read int points
 * @property-read string status
 * @property-read \DateTimeInterface created
 * @property-read \DateTimeInterface modified
 * @property-read string phone
 * @property-read bool force_a
 * @property-read string password
 * @property-read ActiveRow event
 * @property-read string game_lang
 * @property-read int rank_category
 * @property-read int rank_total
 * @property-read int teacher_id
 * @property-read ActiveRow person
 * @deprecated
 */
class TeamModel extends Model implements Resource
{
    public const RESOURCE_ID = 'fyziklani.team';

    public function __toString(): string
    {
        return $this->name;
    }

    public function getTeacher(): ?ModelPerson
    {
        return isset($this->teacher_id) ? ModelPerson::createFromActiveRow($this->ref('person', 'teacher_id')) : null;
    }

    public function getFyziklaniParticipants(): GroupedSelection
    {
        return $this->related(DbNames::TAB_E_FYZIKLANI_PARTICIPANT, 'e_fyziklani_team_id');
    }

    /**
     * @return ModelPerson[]
     */
    public function getPersons(): array
    {
        $persons = [];
        foreach ($this->getFyziklaniParticipants() as $pRow) {
            $persons[] = ParticipantModel::createFromActiveRow($pRow)->getEventParticipant()->getPerson();
        }
        $teacher = $this->getTeacher();
        if ($teacher) {
            $persons[] = $teacher;
        }
        return $persons;
    }

    public function getResourceId(): string
    {
        return self::RESOURCE_ID;
    }
}
