<?php

namespace FKSDB\Components\Grids\Events\Application;

use FKSDB\Components\Controls\Helpers\Badges\NotSetBadge;
use FKSDB\NotImplementedException;
use FKSDB\ORM\DbNames;
use FKSDB\ORM\Models\Fyziklani\ModelFyziklaniTeam;
use Nette\Application\AbortException;
use Nette\Application\UI\InvalidLinkException;
use Nette\Application\UI\Presenter;
use Nette\Database\Table\ActiveRow;
use Nette\Database\Table\Selection;
use NiftyGrid\DuplicateButtonException;
use NiftyGrid\DuplicateColumnException;
use NiftyGrid\DuplicateGlobalButtonException;
use SQL\SearchableDataSource;

/**
 * Class TeamApplicationGrid
 * @package FKSDB\Components\Grids\Events
 */
class TeamApplicationGrid extends AbstractApplicationGrid {
    /**
     * @param Presenter $presenter
     * @throws DuplicateButtonException
     * @throws DuplicateColumnException
     * @throws DuplicateGlobalButtonException
     * @throws InvalidLinkException
     * @throws NotImplementedException
     */
    protected function configure($presenter) {
        parent::configure($presenter);
        $participants = $this->getSource();
        $this->paginate = false;

        $source = new SearchableDataSource($participants);
        $source->setFilterCallback($this->getFilterCallBack());
        $this->setDataSource($source);

        $this->addColumns([
            DbNames::TAB_E_FYZIKLANI_TEAM . '.e_fyziklani_team_id',
            DbNames::TAB_E_FYZIKLANI_TEAM . '.name',
            DbNames::TAB_E_FYZIKLANI_TEAM . '.status'
        ]);
        $this->addColumn('room', _('Room'))->setRenderer(function (ActiveRow $row) {
            $model = ModelFyziklaniTeam::createFromActiveRow($row);
            $position = $model->getPosition();
            if (is_null($position)) {
                return NotSetBadge::getHtml();
            }
            return $position->getRoom()->name;
        });
        $this->addLinkButton($presenter, 'detail', 'detail', _('Detail'), false, ['id' => 'e_fyziklani_team_id']);
        $this->addCSVDownloadButton();
    }

    /**
     * @return Selection
     */
    protected function getSource(): Selection {
        return $this->event->getTeams();
    }

    /**
     * @return array
     */
    protected function getHoldersColumns(): array {
        return [
            'note',
            'game_lang',
            'category',
            'force_a',
            'phone',
            'password',
        ];
    }

    /**
     * @return string
     */
    protected function getModelClassName(): string {
        return ModelFyziklaniTeam::class;
    }

    /**
     * @return string
     */
    protected function getTableName(): string {
        return DbNames::TAB_E_FYZIKLANI_TEAM;
    }
}
