<?php


namespace FyziklaniModule;

use FKSDB\Components\Controls\FormControl\FormControl;
use JanTvrdik\Components\DatePicker;


class GameSetupPresenter extends BasePresenter {

    public function titleDefault() {
        $this->setTitle(_('Fyziklani game setup'));
        $this->setIcon('fa fa-cogs');
    }

    /**
     * @return FormControl
     */
    protected function createComponentForm(): FormControl {
        $control = new FormControl();
        $form = $control->getForm();
        $form->addComponent(new DatePicker('Game start'), 'game_start');
        $form->addComponent(new DatePicker('Game end'), 'game_end');
        $form->addComponent(new DatePicker('Result display'), 'result_display');
        $form->addComponent(new DatePicker('Result hide'), 'result_hide');
        $form->addText('refresh_delay', 'Refresh delay')->setType('number');

        return $control;
    }

    /**
     * @throws \Nette\Application\AbortException
     * @throws \Nette\Application\BadRequestException
     */
    public function renderDefault() {
        $this->template->gameSetup = $this->getGameSetup();
    }
}
