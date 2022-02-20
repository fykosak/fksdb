<?php

declare(strict_types=1);

namespace FKSDB\Components\Grids\Fyziklani;

use FKSDB\Components\Grids\BaseGrid;
use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Models\ORM\Models\Fyziklani\TeamCategory;
use FKSDB\Models\ORM\Models\Fyziklani\TeamModel;
use FKSDB\Models\ORM\Models\ModelEvent;
use Nette\Application\UI\Presenter;
use Nette\DI\Container;
use NiftyGrid\DataSource\IDataSource;
use NiftyGrid\DataSource\NDataSource;
use NiftyGrid\DuplicateColumnException;

class ResultsCategoryGrid extends BaseGrid
{

    private ModelEvent $event;
    private TeamCategory $category;

    public function __construct(ModelEvent $event, TeamCategory $category, Container $container)
    {
        parent::__construct($container);
        $this->event = $event;
        $this->category = $category;
    }

    protected function getData(): IDataSource
    {
        $teams = $this->event->getParticipatingTeams()
            ->where('category', $this->category->value)
            ->order('name');
        return new NDataSource($teams);
    }

    /**
     * @throws BadTypeException
     * @throws DuplicateColumnException
     */
    protected function configure(Presenter $presenter): void
    {
        parent::configure($presenter);

        $this->paginate = false;

        $this->addColumns([
            'e_fyziklani_team.e_fyziklani_team_id',
            'e_fyziklani_team.name',
            'e_fyziklani_team.rank_category',
        ]);
    }

    protected function getModelClassName(): string
    {
        return TeamModel::class;
    }
}
