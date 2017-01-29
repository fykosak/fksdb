<?php

namespace FyziklaniModule;

use FKSDB\model\Fyziklani\CloseSubmitStrategy;
use Kdyby\BootstrapFormRenderer\BootstrapRenderer;
use \Nette\Application\UI\Form;
use \FKSDB\Components\Grids\Fyziklani\FyziklaniTeamsGrid;
use Nette\Utils\Html;

class ClosePresenter extends BasePresenter {

    public function titleTable() {
        $this->setTitle(_('Uzavírání bodování'));
    }

    public function titleTeam($id) {
        $this->setTitle(sprintf(_('Uzavírání bodování týmu "%s"'), $this->serviceFyziklaniTeam->findByPrimary($id)->__toString()));
    }

    public function authorizedTable() {
        $this->setAuthorized($this->eventIsAllowed('fyziklani', 'close'));
    }

    public function authorizedTeam() {
        $this->authorizedTable();
    }

    public function renderTeam($id) {
        $this->template->submits = $this->serviceFyziklaniTeam->findByPrimary($id)->getSubmits();
    }

    public function actionTable() {
        if (!$this->isReadyToClose('A')) {
            $this['closeCategoryAForm']['send']->setDisabled();
        }
        if (!$this->isReadyToClose('B')) {
            $this['closeCategoryBForm']['send']->setDisabled();
        }
        if (!$this->isReadyToClose('C')) {
            $this['closeCategoryCForm']['send']->setDisabled();
        }
        if (!$this->isReadyToClose()) {
            $this['closeGlobalForm']['send']->setDisabled();
        }
    }

    public function actionTeam($id) {
        if ($this->serviceFyziklaniTeam->isOpenSubmit($id)) {
            if (!$this->serviceFyziklaniTeam->teamExist($id, $this->eventID)) {
                $this->flashMessage('Tým neexistuje', 'danger');
                $this->redirect(':Fyziklani:submit:close');
            }
            $this['closeForm']->setDefaults([
                'e_fyziklani_team_id' => $id,
                'next_task' => $this->getNextTask($id)->nextTask
            ]);
        } else {
            $this->flashMessage('Tento tým má již uzavřeno bodování', 'danger');
            $this->redirect(':fyziklani:close:table');
        }
    }

    public function createComponentCloseGrid() {
        $grid = new FyziklaniTeamsGrid($this->eventID, $this->serviceFyziklaniTeam);
        return $grid;
    }

    public function createComponentCloseForm() {
        $form = new Form();
        $form->setRenderer(new BootstrapRenderer());
        $form->addHidden('e_fyziklani_team_id', 0);
        $form->addCheckbox('submit_task_correct', _('Úkoly a počty bodů jsou správně.'))
            ->setRequired(_('Zkontrolujte správnost zadání bodů!'));
        $form->addText('next_task', _('Úloha u vydavačů'))->setDisabled();
        $form->addCheckbox('next_task_correct', _('Úloha u vydavačů se shoduje.'))
            ->setRequired(_('Zkontrolujte prosím shodnost úlohy u vydavačů'));
        $form->addSubmit('send', 'Potvrdit správnost');
        $form->onSuccess[] = [$this, 'closeFormSucceeded'];
        return $form;
    }

    public function closeFormSucceeded(Form $form) {
        $values = $form->getValues();
        $connection = $this->serviceFyziklaniTeam->getConnection();
        $connection->beginTransaction();
        $team = $this->serviceFyziklaniTeam->findByPrimary($values->e_fyziklani_team_id);
        $submits = $team->getSubmits();
        $sum = 0;
        foreach ($submits as $submit) {
            $sum += $submit->points;
        }
        $this->serviceFyziklaniTeam->updateModel($team, ['points' => $sum]);
        $this->serviceFyziklaniTeam->save($team);
        $connection->commit();
        $this->redirect(':Fyziklani:Close:table');
    }

    public function createComponentCloseCategoryForm($category) {
        $form = new Form();
        $form->setRenderer(new BootstrapRenderer());
        $form->addHidden('category', $category);
        $form->addSubmit('send', sprintf(_('Uzavřít kategorii %s.'), $category));
        $form->onSuccess[] = [$this, 'closeCategoryFormSucceeded'];
        return $form;
    }

    public function createComponentCloseCategoryAForm() {
        return $this->createComponentCloseCategoryForm('A');
    }

    public function createComponentCloseCategoryBForm() {
        return $this->createComponentCloseCategoryForm('B');
    }

    public function createComponentCloseCategoryCForm() {
        return $this->createComponentCloseCategoryForm('C');
    }

    public function closeCategoryFormSucceeded(Form $form) {
        $closeStrategy = new CloseSubmitStrategy($this->eventID, $this->serviceFyziklaniTeam);
        $closeStrategy->closeByCategory($form->getValues()->category, $msg);
        $this->flashMessage(Html::el()->add('pořadí bylo uložené' . Html::el('ul')->add($msg)), 'success');
    }

    public function createComponentCloseGlobalForm() {
        $form = new Form();
        $form->setRenderer(new BootstrapRenderer());
        $form->addSubmit('send', _('Uzavřít celé Fyziklání'));
        $form->onSuccess[] = [$this, 'closeGlobalFormSucceeded'];
        return $form;
    }

    public function closeGlobalFormSucceeded() {
        $closeStrategy = new CloseSubmitStrategy($this->eventID, $this->serviceFyziklaniTeam);
        $closeStrategy->closeGlobal($msg);
        $this->flashMessage(Html::el()->add('pořadí bylo uložené' . Html::el('ul')->add($msg)), 'success');
    }

    private function isReadyToClose($category = null) {
        $query = $this->serviceFyziklaniTeam->findParticipating($this->eventID);
        if ($category) {
            $query->where('category', $category);
        }
        $query->where('points', null);
        $count = $query->count();
        return $count == 0;
    }

    private function getNextTask($teamID) {
        $return = [];
        $submits = count($this->serviceFyziklaniTeam->findByPrimary($teamID)->getSubmits());
        $allTask = $this->serviceFyziklaniTask->findAll($this->eventID)->order('label');
        $lastID = $submits + $this->container->parameters[self::EVENT_NAME][$this->eventID]['taskOnBoard'] - 1;
        /** @because index start with 0; */
        $nextID = $lastID + 1;
        if (isset($allTask[$nextID])) {
            $return['nextTask'] = $allTask[$nextID]->label;
        } else {
            $return['nextTask'] = null;
        }
        if (isset($allTask[$lastID])) {
            $return['lastTask'] = $allTask[$lastID]->label;
        } else {
            $return['lastTask'] = null;
        }
        return (object)$return;
    }
}
