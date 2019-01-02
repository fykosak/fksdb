<?php


namespace FyziklaniModule;

class GameSetupPresenter extends BasePresenter {

    public function titleDefault() {
        $this->setTitle(_('Fyziklani game setup'));
        $this->setIcon('fa fa-cogs');
    }

    /**
     * @throws \Nette\Application\AbortException
     * @throws \Nette\Application\BadRequestException
     */
    public function renderDefault() {
        $this->template->gameSetup = $this->getGameSetup();
    }
    /**
     * @throws \Nette\Application\BadRequestException
     * @throws \Nette\Application\AbortException
     */
    public function authorizedDefault() {
        if (!$this->isEventFyziklani()) {
            return $this->setAuthorized(false);
        }
        return $this->setAuthorized($this->eventIsAllowed('fyziklani.gameSetup', 'default'));
    }
}
