<?php

declare(strict_types=1);

namespace FKSDB\Components\EntityForms\Fyziklani\Processing;

use FKSDB\Models\ORM\Models\EventModel;
use Nette\Forms\Form;

class UniqueNameProcessing extends FormProcessing
{
    public function __invoke(array $values, Form $form, EventModel $event): array
    {
        $name = $values['team']['name'];
        $query = $event->getTeams()->where('name', $name);
        if (isset($this->model)) {
            $query->where('fyziklani_team_id != ?', $this->model->fyziklani_team_id);
        }
        if ($query->fetch()) {
            throw new DuplicateTeamNameException($name);
        }
        return $values;
    }
}
