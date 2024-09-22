<?php

declare(strict_types=1);

namespace FKSDB\Models\Authorization\Roles\Events\Fyziklani;

use FKSDB\Models\Authorization\Roles\Events\EventRole;
use FKSDB\Models\ORM\Models\EventModel;
use FKSDB\Models\ORM\Models\Fyziklani\TeamTeacherModel;
use Nette\Utils\Html;

final class TeamTeacherRole extends EventRole
{
    public const ROLE_ID = 'event.fyziklani.teacher';

    public TeamTeacherModel $teacher;

    public function __construct(EventModel $event, TeamTeacherModel $teacher)
    {
        parent::__construct(self::ROLE_ID, $event);
        $this->teacher = $teacher;
    }

    public function badge(): Html
    {

        return Html::el('span')->addAttributes(['class' => 'badge bg-color-5 me-1'])
                    ->addText(_('Teacher') . ': ')
            ->addHtml(Html::el('i')
                ->addAttributes(['class' => $this->teacher->fyziklani_team->scholarship->getIconName() . ' me-1']))
            ->addText(sprintf(
                '%s (%s)',
                $this->teacher->fyziklani_team->name,
                $this->teacher->fyziklani_team->state->label()
            ));
    }
}
