<?php

namespace FKSDB\Components\Grids\Fyziklani\Submits;

use FKSDB\Exceptions\BadTypeException;
use FKSDB\ORM\Models\Fyziklani\ModelFyziklaniTeam;
use Nette\Application\IPresenter;
use Nette\DI\Container;
use NiftyGrid\DataSource\IDataSource;
use NiftyGrid\DataSource\NDataSource;
use NiftyGrid\DuplicateButtonException;
use NiftyGrid\DuplicateColumnException;

/**
 * Class TeamSubmitsGrid
 * @author Michal Červeňák <miso@fykos.cz>
 * @author Lukáš Timko
 */
class TeamSubmitsGrid extends SubmitsGrid {

    private ModelFyziklaniTeam $team;

    public function __construct(ModelFyziklaniTeam $team, Container $container) {
        $this->team = $team;
        parent::__construct($container);
    }

    protected function getData(): IDataSource {
        $submits = $this->team->getAllSubmits()
            ->order('fyziklani_submit.created');
        return new NDataSource($submits);
    }

    /**
     * @param IPresenter $presenter
     * @return void
     * @throws BadTypeException
     * @throws DuplicateButtonException
     * @throws DuplicateColumnException
     */
    protected function configure(IPresenter $presenter): void {
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
