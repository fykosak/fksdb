<?php

declare(strict_types=1);

namespace FKSDB\Components\Applications\Team;

use FKSDB\Components\Grids\Components\BaseGrid;
use FKSDB\Models\ORM\Models\EventModel;
use FKSDB\Models\ORM\Models\Fyziklani\TeamModel2;
use Fykosak\Utils\UI\Title;
use Nette\DI\Container;

/**
 * @phpstan-extends BaseGrid<TeamModel2,array{
 *     status?:string,
 * }>
 */
final class TeamGrid extends BaseGrid
{
    use TeamTrait;

    public function __construct(EventModel $event, Container $container)
    {
        parent::__construct($container);
        $this->event = $event;
    }

    protected function configure(): void
    {
        $this->filtered = true;
        $this->paginate = false;
        $this->counter = true;
        $this->addSimpleReferencedColumns(
            $this->event->event_type_id === 9
                ? [
                '@fyziklani_team.fyziklani_team_id',
                '@fyziklani_team.name',
                '@fyziklani_team.state',
                '@fyziklani_team.category',
            ]
                : [
                '@fyziklani_team.fyziklani_team_id',
                '@fyziklani_team.name',
                '@fyziklani_team.state',
                '@fyziklani_team.scholarship',
                '@fyziklani_team.game_lang',
                '@fyziklani_team.category',
                '@fyziklani_team.place',
                '@fyziklani_team.phone',
            ]
        );
        $this->addPresenterButton(
            'detail',
            'detail',
            new Title(null, _('button.team.detail')),
            false,
            ['id' => 'fyziklani_team_id']
        );
        $this->addPresenterButton(
            'orgDetail',
            'orgDetail',
            new Title(null, _('button.team.orgDetail')),
            false,
            ['id' => 'fyziklani_team_id']
        );
        $this->addPresenterButton(
            'edit',
            'edit',
            new Title(null, _('button.team.edit')),
            false,
            ['id' => 'fyziklani_team_id']
        );
        $this->addPresenterButton(
            ':Event:Attendance:detail',
            'attendance',
            new Title(null, _('button.team.attendance')),
            false,
            ['id' => 'fyziklani_team_id']
        );
    }
}
