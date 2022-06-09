<?php

declare(strict_types=1);

namespace FKSDB\Components\Grids\Application;

use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Models\ORM\DbNames;
use FKSDB\Models\ORM\Models\Fyziklani\TeamModel;
use FKSDB\Models\ORM\Models\Fyziklani\TeamModel2;
use Nette\Application\UI\InvalidLinkException;
use Nette\Application\UI\Presenter;
use Nette\Database\Table\GroupedSelection;
use NiftyGrid\DuplicateButtonException;
use NiftyGrid\DuplicateColumnException;
use NiftyGrid\DuplicateGlobalButtonException;

class TeamApplicationsGrid extends AbstractApplicationsGrid
{

    /**
     * @throws BadTypeException
     * @throws DuplicateButtonException
     * @throws DuplicateColumnException
     * @throws DuplicateGlobalButtonException
     * @throws InvalidLinkException
     */
    protected function configure(Presenter $presenter): void
    {

        $this->paginate = false;

        $this->addColumns([
            'e_fyziklani_team.e_fyziklani_team_id',
            'e_fyziklani_team.name',
            'e_fyziklani_team.status',
        ]);
        $this->addLinkButton('detail', 'detail', _('Detail'), false, ['id' => 'e_fyziklani_team_id']);
        $this->addCSVDownloadButton();
        parent::configure($presenter);
    }

    protected function getSource(): GroupedSelection
    {
        return $this->event->getFyziklaniTeams();
    }

    protected function getHoldersColumns(): array
    {
        return [
            'note',
            'game_lang',
            'category',
            'force_a',
            'phone',
            'password',
        ];
    }

    protected function getModelClassName(): string
    {
        return TeamModel2::class;
    }

    protected function getTableName(): string
    {
        return DbNames::TAB_FYZIKLANI_TEAM;
    }
}
