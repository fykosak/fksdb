<?php

declare(strict_types=1);

namespace FKSDB\Components\Grids\Fyziklani\Submits;

use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Models\ORM\Models\Fyziklani\TeamModel2;
use Nette\Application\UI\Presenter;
use Nette\DI\Container;
use NiftyGrid\DataSource\IDataSource;
use NiftyGrid\DataSource\NDataSource;
use NiftyGrid\DuplicateButtonException;
use NiftyGrid\DuplicateColumnException;

class TeamSubmitsGrid extends SubmitsGrid
{

    private TeamModel2 $team;

    public function __construct(TeamModel2 $team, Container $container)
    {
        $this->team = $team;
        parent::__construct($container);
    }

    protected function getData(): IDataSource
    {
        $submits = $this->team->getAllSubmits()
            ->order('fyziklani_submit.created');
        return new NDataSource($submits);
    }

    /**
     * @throws BadTypeException
     * @throws DuplicateButtonException
     * @throws DuplicateColumnException
     */
    protected function configure(Presenter $presenter): void
    {
        parent::configure($presenter);
        $this->paginate = false;
        $this->addColumnTask();

        $this->addColumns([
            'fyziklani_submit.points',
            'fyziklani_submit.created',
            'fyziklani_submit.state',
        ]);
        $this->addLinkButton(':Fyziklani:Submit:edit', 'edit', _('Edit'), false, ['id' => 'fyziklani_submit_id']);
        $this->addLinkButton(':Fyziklani:Submit:detail', 'detail', _('Detail'), false, ['id' => 'fyziklani_submit_id']);
    }
}
