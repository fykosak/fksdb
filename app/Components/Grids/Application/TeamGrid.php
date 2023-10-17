<?php

declare(strict_types=1);

namespace FKSDB\Components\Grids\Application;

use FKSDB\Components\Grids\Components\BaseGrid;
use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Models\ORM\Models\EventModel;
use FKSDB\Models\ORM\Models\Fyziklani\TeamModel2;
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

    /**
     * @throws BadTypeException
     * @throws \ReflectionException
     */
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
                '@fyziklani_team.game_lang',
                '@fyziklani_team.category',
                '@fyziklani_team.phone',
            ]
        );
        $this->addPresenterButton('detail', 'detail', _('button.detail'), false, ['id' => 'fyziklani_team_id']);
    }
}
