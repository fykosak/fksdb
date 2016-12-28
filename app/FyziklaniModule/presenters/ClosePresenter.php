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
        $this->setTitle(_('Uzavierka bodovania'));
    }
    public function titleTeam() {
        $this->setTitle(_('Uzavierka bodovania'));
    }

    public function authorizedTable() {
        $this->setAuthorized($this->getContestAuthorizator()->isAllowedEvent('fyziklani', 'close', $this->getCurrentEvent(),$this->database));
    }
    public function authorizedTeam() {
       $this->actionTable();
    }

    public function renderTeam($id) {
        $this->template->submits = $this->database->table(\DbNames::TAB_FYZIKLANI_SUBMIT)->where('e_fyziklani_team_id', $id);
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
            $this['closeForm']->setDefaults(['e_fyziklani_team_id' => $id, 'next_task' => $this->getNextTask($id)->nextTask]);
        } else {
            $this->flashMessage('tento tým má už uzavreté bodovanie', 'danger');
            $this->redirect(':fyziklani:close:table');
        }

    }

    public function createComponentCloseGrid() {
        $grid = new FyziklaniTeamsGrid($this->database);
        return $grid;
    }

    public function createComponentCloseForm() {
        $form = new Form();
        $form->setRenderer(new BootstrapRenderer());
        $form->addHidden('e_fyziklani_team_id', 0);
        $form->addCheckbox('submit_task_correct', _('Úlohy a počty bodov sú správne'))->setRequired(_('Skontrolujte prosím správnosť zadania bodov!'));
        $form->addText('next_task', _('Úloha u vydávačov'))->setDisabled();
        $form->addCheckbox('next_task_correct', _('Úloha u vydávačov sa zhaduje'))->setRequired(_('Skontrolujte prosím zhodnosť úlohy u vydávačov'));
        $form->addSubmit('send', 'Potvrdiť spravnosť');
        $form->onSuccess[] = [$this, 'closeFormSucceeded'];
        return $form;
    }

    public function closeFormSucceeded(Form $form) {
        $values = $form->getValues();
        $submits = $this->database->table(\DbNames::TAB_FYZIKLANI_SUBMIT)->where('e_fyziklani_team_id', $values->e_fyziklani_team_id);
        $sum = 0;
        foreach ($submits as $submit) {
            $sum += $submit->points;
        }
        if ($this->database->query('UPDATE ' . \DbNames::TAB_E_FYZIKLANI_TEAM . ' SET ? WHERE e_fyziklani_team_id=? ', ['points' => $sum], $values->e_fyziklani_team_id)) {
            $this->redirect(':Fyziklani:Close:table');
        }
    }

    public function createComponentCloseCategoryForm($category) {
        $form = new Form();
        $form->setRenderer(new BootstrapRenderer());
        $form->addHidden('category', $category);
        $form->addSubmit('send', _('Uzavrieť kategoriu' . $category));
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
        $closeStrategy = new CloseSubmitStragegy($this);
        $closeStrategy->closeByCategory($form->getValues()->category);
    }

    public function createComponentCloseGlobalForm() {
        $form = new Form();
        $form->setRenderer(new BootstrapRenderer());
        $form->addSubmit('send', _('Uzavrieť Fyzikláni'));
        $form->onSuccess[] = [$this, 'closeGlobalFormSucceeded'];
        return $form;
    }

    public function closeGlobalFormSucceeded() {
        $closeStrategy = new CloseSubmitStragegy($this);
        $closeStrategy->closeGlobal();
    }


    private function isReadyToClose($category = null) {
        $database = $this->database;
        $query = $database->table(\DbNames::TAB_E_FYZIKLANI_TEAM)->where('status', 'participated')->where('event_id', $this->eventID);
        if ($category) {
            $query->where('category', $category);
        }
        $query->where('points', null);
        $count = $query->count();
        return $count == 0;

    }
    private function getNextTask($teamID) {

        $return = [];
        $submits = $this->database->table(\DbNames::TAB_FYZIKLANI_SUBMIT)->where('e_fyziklani_team_id=?', $teamID)->count();
        $allTask = $this->database->query('SELECT * FROM ' . \DbNames::TAB_FYZIKLANI_TASK . ' WHERE event_id = ? ORDER BY label', $this->eventID)->fetchAll();
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
