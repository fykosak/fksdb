<?php
/**
 * Created by PhpStorm.
 * User: miso
 * Date: 25.12.2016
 * Time: 22:21
 */

namespace FyziklaniModule;

use Fyziklani\CloseSubmitStragegy;
use Kdyby\BootstrapFormRenderer\BootstrapRenderer;
use \Nette\Application\UI\Form;
use \FKSDB\Components\Grids\Fyziklani\FyziklaniTeamsGrid;

class ClosePresenter extends BasePresenter {

    public function titleTable() {
        $this->setTitle(_('Uzavírání bodování'));
    }

    public function titleTeam() {
        $this->setTitle(_('Uzavírání bodování'));
    }

    public function authorizedTable() {
        $this->setAuthorized($this->getEventAuthorizator()->isAllowed('fyziklani', 'close', $this->getCurrentEvent()));
    }

    public function authorizedTeam() {
        $this->authorizedTable();
    }

// @TODO ORM
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

        if ($this->isOpenSubmit($id)) {
            if (!$this->teamExist($id)) {
                $this->flashMessage('Tým neexistuje', 'danger');
                $this->redirect(':Fyziklani:submit:close');
            }
            $this['closeForm']->setDefaults([
                'e_fyziklani_team_id' => $id,
                'next_task' => $this->getNextTask($id)->nextTask
            ]);
        } else {
            $this->flashMessage('Tento tím má již uzavřeny bodování', 'danger');
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
        $form->addCheckbox('submit_task_correct', _('Úkoly a počty bodů jsou správně.'))->setRequired(_('Zkontrolujte správnost zadání bodů!'));
        $form->addText('next_task', _('Úloha u vydávačů'))->setDisabled();
        $form->addCheckbox('next_task_correct', _('Úloha u vydávaču sa zhaduje.'))->setRequired(_('Skontrolujte prosím zhodnosť úlohy u vydávačov'));
        $form->addSubmit('send', 'Potvrdiť spravnosť');
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
        $form->addSubmit('send', sprintf(_('Uzavrieť kategoriu %s.'),$category));
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
        $closeStrategy = new CloseSubmitStragegy($this->eventID, $this->serviceFyziklaniTeam);
        $closeStrategy->closeByCategory($form->getValues()->category, $msg);
        $this->presenter->flashMessage(Html::el()->add('poradie bolo uložené' . Html::el('ul')->add($msg)), 'success');
    }

    public function createComponentCloseGlobalForm() {
        $form = new Form();
        $form->setRenderer(new BootstrapRenderer());
        $form->addSubmit('send', _('Uzavrieť celé Fyzikláni'));
        $form->onSuccess[] = [$this, 'closeGlobalFormSucceeded'];
        return $form;
    }

    public function closeGlobalFormSucceeded() {
        $closeStrategy = new CloseSubmitStragegy($this->eventID, $this->serviceFyziklaniTeam);
        $closeStrategy->closeGlobal($msg);
        $this->flashMessage(Html::el()->add('poradie bolo uložené' . Html::el('ul')->add($msg)), 'success');
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
        $submits = count($this->serviceFyziklaniTask->findByPrimary($teamID)->getSubmits());
        $allTask = $this->serviceFyziklaniTask->findAll($this->eventID)->order('label');
        $lastID = $submits + $this->container->parameters['fyziklani']['taskOnBoard'] - 1;
        /** @because index start with 0; */
        $nextID = $lastID + 1;
        if (array_key_exists($nextID, $allTask)) {
            $return['nextTask'] = $allTask[$nextID]->label;
        } else {
            $return['nextTask'] = null;
        }
        if (array_key_exists($lastID, $allTask)) {
            $return['lastTask'] = $allTask[$lastID]->label;
        } else {
            $return['lastTask'] = null;
        }
        return (object)$return;
    }
}
