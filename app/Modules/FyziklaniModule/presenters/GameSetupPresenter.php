<?php

namespace FKSDB\Modules\FyziklaniModule;

use FKSDB\Events\EventNotFoundException;
use FKSDB\Exceptions\NotFoundException;
use FKSDB\Fyziklani\NotSetGameParametersException;
use FKSDB\ORM\Models\Fyziklani\ModelFyziklaniGameSetup;
use FKSDB\UI\PageTitle;

/**
 * Class GameSetupPresenter
 * @author Michal Červeňák <miso@fykos.cz>
 */
class GameSetupPresenter extends BasePresenter {

    private ModelFyziklaniGameSetup $gameSetup;

    /**
     * @return void
     * @throws EventNotFoundException
     */
    public function titleDefault(): void {
        $this->setPageTitle(new PageTitle(_('Fyziklani game setup'), 'fa fa-cogs'));
    }

    /**
     * @return void
     * @throws EventNotFoundException
     * @throws NotFoundException
     * @throws NotSetGameParametersException
     */
    public function renderDefault(): void {
        $this->template->gameSetup = $this->getGameSetup();
    }

    /**
     * @return void
     * @throws EventNotFoundException
     */
    public function authorizedDefault(): void {
        $this->setAuthorized($this->isContestsOrgAuthorized('fyziklani.gameSetup', 'default'));
    }

    /**
     * @return ModelFyziklaniGameSetup
     * @throws NotFoundException
     * @throws NotSetGameParametersException
     * @throws EventNotFoundException
     */
    protected function getGameSetup(): ModelFyziklaniGameSetup {
        if (!isset($this->gameSetup)) {
            $gameSetup = $this->getEvent()->getFyziklaniGameSetup();
            if (!$gameSetup) {
                throw new NotFoundException(_('Game is not set up!'));
            }
            $this->gameSetup = $gameSetup;
        }
        return $this->gameSetup;
    }
}
