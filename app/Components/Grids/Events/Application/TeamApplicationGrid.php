<?php

namespace FKSDB\Components\Grids\Events\Application;

use FKSDB\ORM\DbNames;
use FKSDB\ORM\Models\Fyziklani\ModelFyziklaniTeam;
use Nette\Application\UI\Presenter;
use Nette\Database\Table\Selection;
use SQL\SearchableDataSource;

/**
 * Class TeamApplicationGrid
 * @package FKSDB\Components\Grids\Events
 */
class TeamApplicationGrid extends AbstractApplicationGrid {
    /**
     * @param Presenter $presenter
     * @throws \NiftyGrid\DuplicateColumnException
     * @throws \NiftyGrid\DuplicateButtonException
     */
    protected function configure($presenter) {
        parent::configure($presenter);
        $participants = $this->getSource();
        $this->paginate = false;

        $source = new SearchableDataSource($participants);
        $source->setFilterCallback($this->getFilterCallBack());
        $this->setDataSource($source);

        $this->addColumns(['e_fyziklani_team_id', 'name', 'status']);

        $this->addButton('detail')->setShow(function ($row) {
            $model = ModelFyziklaniTeam::createFromActiveRow($row);
            return \in_array($model->getEvent()->event_type_id, [1, 9]);
        })->setText(_('Detail'))
            ->setLink(function ($row) {
                $model = ModelFyziklaniTeam::createFromActiveRow($row);
                return $this->getPresenter()->link('detail', [
                    'id' => $model->e_fyziklani_team_id,
                ]);
            });
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
        return ['note',
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
