<?php

namespace FKSDB\Components\Grids\Fyziklani;

use FKSDB\Exceptions\NotImplementedException;
use FKSDB\ORM\DbNames;
use FKSDB\ORM\Models\Fyziklani\ModelFyziklaniTeam;
use FyziklaniModule\BasePresenter;
use Nette\DI\Container;
use NiftyGrid\DataSource\NDataSource;
use NiftyGrid\DuplicateButtonException;
use NiftyGrid\DuplicateColumnException;

/**
 * Class TeamSubmitsGrid
 * @author Michal Červeňák <miso@fykos.cz>
 * @author Lukáš Timko
 */
class TeamSubmitsGrid extends SubmitsGrid {

    /**
     * @var ModelFyziklaniTeam
     */
    private $team;

    /**
     * FyziklaniSubmitsGrid constructor.
     * @param ModelFyziklaniTeam $team
     * @param Container $container
     */
    public function __construct(ModelFyziklaniTeam $team, Container $container) {
        $this->team = $team;
        parent::__construct($container);
    }

    /**
     * @param BasePresenter $presenter
     * @throws DuplicateColumnException
     * @throws DuplicateButtonException
     * @throws NotImplementedException
     * @throws NotImplementedException
     * @throws NotImplementedException
     * @throws NotImplementedException
     */
    protected function configure($presenter) {
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

        $submits = $this->team->getAllSubmits()
            ->order('fyziklani_submit.created');
        $dataSource = new NDataSource($submits);
        $this->setDataSource($dataSource);
    }
}
