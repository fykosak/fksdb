<?php

namespace FKSDB\Components\Grids\Application;

use FKSDB\Components\Controls\Badges\NotSetBadge;
use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Models\ORM\DbNames;
use FKSDB\Models\ORM\Models\Fyziklani\ModelFyziklaniTeam;
use Nette\Application\UI\InvalidLinkException;
use Nette\Application\IPresenter;
use Nette\Database\Table\ActiveRow;
use Nette\Database\Table\GroupedSelection;
use NiftyGrid\DuplicateButtonException;
use NiftyGrid\DuplicateColumnException;
use NiftyGrid\DuplicateGlobalButtonException;

/**
 * Class TeamApplicationGrid
 * @author Michal Červeňák <miso@fykos.cz>
 */
class TeamApplicationsGrid extends AbstractApplicationsGrid {

    /**
     * @param IPresenter $presenter
     * @return void
     * @throws BadTypeException
     * @throws DuplicateButtonException
     * @throws DuplicateColumnException
     * @throws DuplicateGlobalButtonException
     * @throws InvalidLinkException
     */
    protected function configure(IPresenter $presenter): void {

        $this->paginate = false;

        $this->addColumns([
            'e_fyziklani_team.e_fyziklani_team_id',
            'e_fyziklani_team.name',
            'e_fyziklani_team.status',
        ]);
        $this->addColumn('room', _('Room'))->setRenderer(function (ActiveRow $row) {
            $model = ModelFyziklaniTeam::createFromActiveRow($row);
            $position = $model->getPosition();
            if (is_null($position)) {
                return NotSetBadge::getHtml();
            }
            return $position->getRoom()->name;
        });
        $this->addLinkButton('detail', 'detail', _('Detail'), false, ['id' => 'e_fyziklani_team_id']);
        $this->addCSVDownloadButton();
        parent::configure($presenter);
    }

    protected function getSource(): GroupedSelection {
        return $this->event->getTeams();
    }

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

    protected function getModelClassName(): string {
        return ModelFyziklaniTeam::class;
    }

    protected function getTableName(): string {
        return DbNames::TAB_E_FYZIKLANI_TEAM;
    }
}
