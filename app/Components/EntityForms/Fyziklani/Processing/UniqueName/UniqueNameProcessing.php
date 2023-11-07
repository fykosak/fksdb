<?php

declare(strict_types=1);

namespace FKSDB\Components\EntityForms\Fyziklani\Processing\UniqueName;

use FKSDB\Components\EntityForms\Fyziklani\Processing\FormProcessing;
use FKSDB\Models\ORM\Models\EventModel;
use FKSDB\Models\ORM\Models\Fyziklani\TeamModel2;
use Nette\Forms\Form;

class UniqueNameProcessing extends FormProcessing
{
    public function __invoke(array $values, Form $form, EventModel $event, ?TeamModel2 $model): array
    {
        $name = $values['team']['name'];
        $query = $event->getTeams()->where('name', $name);
        if (isset($model)) {
            $query->where('fyziklani_team_id != ?', $model->fyziklani_team_id);
        }
        if ($query->fetch()) {
            throw new UniqueNameException($name);
        }
        return $values;
    }
}
