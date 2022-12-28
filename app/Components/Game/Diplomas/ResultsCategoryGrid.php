<?php

declare(strict_types=1);

namespace FKSDB\Components\Game\Diplomas;

use FKSDB\Components\Grids\BaseGrid;
use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Models\ORM\Models\EventModel;
use FKSDB\Models\ORM\Models\Fyziklani\TeamCategory;
use Nette\Application\UI\Presenter;
use Nette\DI\Container;

class ResultsCategoryGrid extends BaseGrid
{

    private EventModel $event;
    private TeamCategory $category;

    public function __construct(EventModel $event, TeamCategory $category, Container $container)
    {
        parent::__construct($container);
        $this->event = $event;
        $this->category = $category;
    }

    protected function setData(): void
    {
        $this->data = $this->event->getParticipatingTeams()
            ->where('category', $this->category->value)
            ->order('name');
    }

    /**
     * @throws BadTypeException
     * @throws \ReflectionException
     */
    protected function configure(Presenter $presenter): void
    {
        parent::configure($presenter);

        $this->paginate = false;

        $this->addColumns([
            'fyziklani_team.fyziklani_team_id',
            'fyziklani_team.name',
            'fyziklani_team.rank_category',
        ]);
    }
}
