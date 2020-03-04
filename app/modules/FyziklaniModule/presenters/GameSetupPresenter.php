<?php

namespace FyziklaniModule;

use FKSDB\ORM\Models\Fyziklani\ModelFyziklaniGameSetup;
use Nette\Application\AbortException;
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
     */
    public function titleDefault(): void {
        $this->setTitle(_('Fyziklani game setup'));
        $this->setIcon('fa fa-cogs');
    }

    /**
     * @throws AbortException
     * @throws BadRequestException
     */
    public function renderDefault(): void {
        $this->template->gameSetup = $this->getGameSetup();
    }

    /**
     * @return void
     * @throws AbortException
     * @throws BadRequestException
     */
    public function authorizedDefault(): void {
        $this->setAuthorized($this->eventIsAllowed('fyziklani.gameSetup', 'default'));
    }

    /**
     * @return ModelFyziklaniGameSetup
     * @throws BadRequestException
     * @throws AbortException
     */
    protected function getGameSetup(): ModelFyziklaniGameSetup {
        if (!$this->gameSetup) {
            $gameSetup = $this->getEvent()->getFyziklaniGameSetup();
            if (!$gameSetup) {
                throw new BadRequestException(_('Game is not set up!'), 404);
            }
            $this->gameSetup = $gameSetup;
        }
        return $this->gameSetup;
    }
}
