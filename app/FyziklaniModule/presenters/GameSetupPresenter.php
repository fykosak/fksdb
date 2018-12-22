<?php

namespace FyziklaniModule;

class GameSetupPresenter extends BasePresenter {

    public function titleDefault() {
        $this->setTitle(_('Fyziklani game setup'));
        $this->setIcon('fa fa-cogs');
    }

    /**
     * @throws \Nette\Application\BadRequestException
     */
    public function renderDefault() {
        $this->template->gameSetup = $this->getGameSetup();
    }
}
