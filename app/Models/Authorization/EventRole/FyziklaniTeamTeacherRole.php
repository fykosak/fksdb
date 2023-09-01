<?php

declare(strict_types=1);

namespace FKSDB\Models\Authorization\EventRole;

use FKSDB\Models\ORM\Models\EventModel;
use FKSDB\Models\ORM\Models\Fyziklani\TeamModel2;
use Nette\Utils\Html;

class FyziklaniTeamTeacherRole extends EventRole
{
    /** @phpstan-var TeamModel2[] */
    public array $teams;

    /**
     * @phpstan-param TeamModel2[] $teams
     */
    public function __construct(EventModel $event, array $teams)
    {
        parent::__construct('event.fyziklaniTeamTeacher', $event);
        $this->teams = $teams;
    }

    public function badge(): Html
    {
        $container = Html::el('span');
        foreach ($this->teams as $team) {
            $container->addHtml(
                Html::el('span')->addAttributes(['class' => 'badge bg-color-5 me-1'])
                    ->addText(sprintf(_('Teacher: %s (%s)'), $team->name, $team->state->label()))
            );
        }
        return $container;
    }
}
