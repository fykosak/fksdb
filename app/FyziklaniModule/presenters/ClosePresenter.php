<?php

namespace FyziklaniModule;

use FKSDB\Components\Controls\FormControl\FormControl;
use FKSDB\Components\Controls\Fyziklani\CloseControl;
use Nette\Application\BadRequestException;
use ORM\Models\Events\ModelFyziklaniTeam;

/**
 * Class ClosePresenter
 * @package FyziklaniModule
 * @property FormControl closeCategoryAForm
 */
class ClosePresenter extends BasePresenter {

    /** @var ModelFyziklaniTeam */
    private $team;

    /**
     * @return ModelFyziklaniTeam
     */
    private function getTeam(): ModelFyziklaniTeam {
        return $this->team;
    }

    public function titleList() {
        $this->setTitle(_('Uzavírání bodování'));
        $this->setIcon('fa fa-check');
    }

    public function titleTeam() {
        $this->setTitle(sprintf(_('Uzavírání bodování týmu "%s"'), $this->getTeam()->name));
        $this->setIcon('fa fa-check-square-o');
    }

    public function authorizedList() {
        $this->setAuthorized($this->eventIsAllowed('fyziklani.close', 'list'));
    }

    public function authorizedTeam() {
        $this->setAuthorized($this->eventIsAllowed('fyziklani.close', 'team'));
    }

    public function authorizedResults() {
        $this->setAuthorized($this->eventIsAllowed('fyziklani.close', 'results'));
    }

    public function renderTeam() {
        $this->template->submits = $this->getTeam()->getSubmits();
    }

    /**
     * @param $id
     * @throws BadRequestException
     * @throws \Nette\Application\AbortException
     */
    public function actionTeam(int $id) {
        $row = $this->getServiceFyziklaniTeam()->findByPrimary($id);
        if (!$row) {
            throw new BadRequestException(_('Team does not exists'), 404);
        }
        $this->team = ModelFyziklaniTeam::createFromTableRow($row);

        if (!$this->team->hasOpenSubmit()) {
            $this->flashMessage(sprintf(_('Tým %s má již uzavřeno bodování'), $this->getTeam()->name), 'danger');
            $this->backlinkRedirect();
            $this->redirect('list'); // if there's no backlink
        }
    }

    /**
     * @return CloseControl
     * @throws BadRequestException
     * @throws \Nette\Application\AbortException
     */
    public function createComponentCloseControl(): CloseControl {
        return new CloseControl($this->getEvent(), $this->getServiceFyziklaniTeam(), $this->getTranslator());
    }

    /**
     * @return FormControl
     * @throws BadRequestException
     * @throws \Nette\Application\AbortException
     */
    public function createComponentCloseForm(): FormControl {
        $control = new FormControl();
        $form = $control->getForm();
        $form->addCheckbox('submit_task_correct', _('Úkoly a počty bodů jsou správně.'))
            ->setRequired(_('Zkontrolujte správnost zadání bodů!'));
        $form->addText('next_task', _('Úloha u vydavačů'))
            ->setDisabled()
            ->setDefaultValue($this->getNextTask());
        $form->addCheckbox('next_task_correct', _('Úloha u vydavačů se shoduje.'))
            ->setRequired(_('Zkontrolujte prosím shodnost úlohy u vydavačů'));
        $form->addSubmit('send', 'Potvrdit správnost');
        $form->onSuccess[] = function () {
            $this->closeFormSucceeded();
        };
        return $control;
    }

    /**
     * @throws \Nette\Application\AbortException
     */
    private function closeFormSucceeded() {
        $connection = $this->getServiceFyziklaniTeam()->getConnection();
        $connection->beginTransaction();
        $submits = $this->team->getSubmits();
        $sum = 0;
        foreach ($submits as $submit) {
            $sum += $submit->points;
        }
        $this->getServiceFyziklaniTeam()->updateModel($this->team, ['points' => $sum]);
        $this->getServiceFyziklaniTeam()->save($this->team);
        $connection->commit();
        $this->backlinkRedirect();
        $this->redirect('list'); // if there's no backlink
    }


    /**
     * @return string
     * @throws BadRequestException
     * @throws \Nette\Application\AbortException
     */
    private function getNextTask(): string {
        $submits = count($this->team->getSubmits());

        $tasksOnBoard = $this->getGameSetup()->tasks_on_board;
        /**
         * @var $nextTask \ModelFyziklaniTask
         */
        $nextTask = $this->getServiceFyziklaniTask()->findAll($this->getEvent())->order('label')->limit(1, $submits + $tasksOnBoard)->fetch();
        return ($nextTask) ? $nextTask->label : '';
    }

}
