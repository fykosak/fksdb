<?php


namespace FyziklaniModule;

use Nette\Application\AbortException;
use Nette\Application\BadRequestException;

/**
 * Class GameSetupPresenter
 * @package FyziklaniModule
 */
class GameSetupPresenter extends BasePresenter {
    /**
     * @return void
     */
    public function titleDefault() {
        $this->setTitle(_('Fyziklani game setup'));
        $this->setIcon('fa fa-cogs');
    }

    /**
     * @throws AbortException
     * @throws BadRequestException
     */
    public function renderDefault() {
        $this->template->gameSetup = $this->getGameSetup();
    }

    /**
     * @throws BadRequestException
     * @throws AbortException
     * @return void
     */
    public function authorizedDefault() {
        if (!$this->isEventFyziklani()) {
            return $this->setAuthorized(false);
        }
        return $this->setAuthorized($this->eventIsAllowed('fyziklani.gameSetup', 'default'));
    }
}
