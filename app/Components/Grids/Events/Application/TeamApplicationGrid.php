<?php

namespace FKSDB\Components\Grids\Events\Application;

use FKSDB\Components\Controls\Badges\NotSetBadge;
use FKSDB\Exceptions\BadTypeException;
use FKSDB\Exceptions\NotImplementedException;
use FKSDB\ORM\DbNames;
use FKSDB\ORM\Models\Fyziklani\ModelFyziklaniTeam;
use Nette\Application\UI\InvalidLinkException;
use Nette\Application\UI\Presenter;
use Nette\Database\Table\ActiveRow;
use Nette\Database\Table\GroupedSelection;
use NiftyGrid\DuplicateButtonException;
use NiftyGrid\DuplicateColumnException;
use NiftyGrid\DuplicateGlobalButtonException;

/**
 * Class TeamApplicationGrid
 * @author Michal Červeňák <miso@fykos.cz>
 */
class TeamApplicationGrid extends AbstractApplicationGrid {
    /**
     * @param Presenter $presenter
     * @throws DuplicateButtonException
     * @throws DuplicateColumnException
     * @throws DuplicateGlobalButtonException
     * @throws InvalidLinkException
     * @throws NotImplementedException
     * @throws BadTypeException
     */
    protected function configure(Presenter $presenter): void {
        parent::configure($presenter);
        $this->paginate = false;

        $this->addColumns([
            'e_fyziklani_team.e_fyziklani_team_id',
            'e_fyziklani_team.name',
            'e_fyziklani_team.status',
        ]);
        // TODO to TRF
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
