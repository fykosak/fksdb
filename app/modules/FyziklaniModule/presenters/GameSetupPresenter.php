<?php

namespace FyziklaniModule;

use FKSDB\Exceptions\NotFoundException;
use FKSDB\Fyziklani\NotSetGameParametersException;
use FKSDB\ORM\Models\Fyziklani\ModelFyziklaniGameSetup;
use Nette\Application\BadRequestException;

/**
 * Class GameSetupPresenter
 * @author Michal Červeňák <miso@fykos.cz>
 */
class GameSetupPresenter extends BasePresenter {

    private ModelFyziklaniGameSetup $gameSetup;

    /**
     * @return void
     * @throws BadRequestException
     */
    public function titleDefault(): void {
        $this->setTitle(_('Fyziklani game setup'), 'fa fa-cogs');
    }

    /**
     * @throws BadRequestException
     * @throws NotSetGameParametersException
     */
    public function renderDefault(): void {
        $this->template->gameSetup = $this->getGameSetup();
    }

    /**
     * @return void
     * @throws BadRequestException
     */
    public function authorizedDefault(): void {
        $this->setAuthorized($this->isContestsOrgAuthorized('fyziklani.gameSetup', 'default'));
    }

    /**
     * @return ModelFyziklaniGameSetup
     * @throws BadRequestException
     * @throws NotSetGameParametersException
     */
    protected function getGameSetup(): ModelFyziklaniGameSetup {
        if (!$this->gameSetup) {
            $gameSetup = $this->getEvent()->getFyziklaniGameSetup();
            if (!$gameSetup) {
                throw new NotFoundException(_('Game is not set up!'));
            }
            $this->gameSetup = $gameSetup;
        }
        return $this->gameSetup;
    }
}
