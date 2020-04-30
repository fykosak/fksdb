<?php

namespace FyziklaniModule;

use FKSDB\Exceptions\NotFoundException;
use FKSDB\model\Fyziklani\NotSetGameParametersException;
use FKSDB\ORM\Models\Fyziklani\ModelFyziklaniGameSetup;
use Nette\Application\BadRequestException;

/**
 * Class GameSetupPresenter
 * @package FyziklaniModule
 */
class GameSetupPresenter extends BasePresenter {
    /**
     * @var ModelFyziklaniGameSetup
     */
    private $gameSetup;

    /**
     * @return void
     * @throws BadRequestException
     */
    public function titleDefault() {
        $this->setTitle(_('Fyziklani game setup'), 'fa fa-cogs');
    }

    /**
     * @throws BadRequestException
     * @throws NotSetGameParametersException
     */
    public function renderDefault() {
        $this->template->gameSetup = $this->getGameSetup();
    }

    /**
     * @return void
     * @throws BadRequestException
     */
    public function authorizedDefault() {
        return $this->setAuthorized($this->isContestsOrgAuthorized('fyziklani.gameSetup', 'default'));
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
