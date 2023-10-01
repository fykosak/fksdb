<?php

declare(strict_types=1);

namespace FKSDB\Components\Game\Diplomas;

use FKSDB\Components\Grids\Components\BaseGrid;
use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Models\ORM\Models\EventModel;
use FKSDB\Models\ORM\Models\Fyziklani\TeamModel2;
use Fykosak\NetteORM\TypedGroupedSelection;
use Nette\DI\Container;

/**
 * @phpstan-extends BaseGrid<TeamModel2,array{}>
 */
class ResultsTotalGrid extends BaseGrid
{
    private EventModel $event;

    public function __construct(EventModel $event, Container $container)
    {
        parent::__construct($container);
        $this->event = $event;
    }

    /**
     * @phpstan-return TypedGroupedSelection<TeamModel2>
     */
    protected function getModels(): TypedGroupedSelection
    {
        return $this->event->getParticipatingTeams()->order('name');
    }

    /**
     * @throws BadTypeException
     * @throws \ReflectionException
     */
    protected function configure(): void
    {
        $this->paginate = false;
        $this->addSimpleReferencedColumns([
            '@fyziklani_team.fyziklani_team_id',
            '@fyziklani_team.name',
            '@fyziklani_team.rank_total',
        ]);
    }
}
