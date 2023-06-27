<?php

declare(strict_types=1);

namespace FKSDB\Models\Authorization\EventRole;

use FKSDB\Models\ORM\Models\Fyziklani\TeamModel2;
use FKSDB\Models\ORM\Models\EventModel;
use Nette\Utils\Html;

class FyziklaniTeamTeacherRole extends EventRole
{
    /** @var TeamModel2[] */
    public array $teams;

    public function __construct(EventModel $event, array $teams)
    {
        parent::__construct('event.fyziklaniTeamTeacher', $event);
        $this->teams = $teams;
    }

    public function badge(): Html
    {
        $container = Html::el('span')->addText(_('Teacher: '))->addAttributes(['class' => 'badge bg-color-5']);
        foreach ($this->teams as $key => $team) {
            $container->addText(($key === 0 ? '' : ', ') . $team->name . ' (' . $team->state->label() . ')');
        }
        return $container;
    }
}
