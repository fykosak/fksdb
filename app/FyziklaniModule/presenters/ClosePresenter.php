<?php

namespace FyziklaniModule;

use FKSDB\Components\Grids\Fyziklani\FyziklaniTeamsGrid;
use FKSDB\model\Fyziklani\CloseSubmitStrategy;
use Kdyby\BootstrapFormRenderer\BootstrapRenderer;
use Nette\Application\BadRequestException;
use Nette\Application\UI\Form;
use Nette\Forms\Controls\Button;
use Nette\Utils\Html;
use ORM\Models\Events\ModelFyziklaniTeam;

class ClosePresenter extends BasePresenter {

    /** @var ModelFyziklaniTeam */
    private $team;

    public function titleTable() {
        $this->setTitle(_('Uzavírání bodování'));
        $this->setIcon('fa fa-check');
    }

    public function titleTeam($id) {
        $this->setTitle(sprintf(_('Uzavírání bodování týmu "%s"'), $this->serviceFyziklaniTeam->findByPrimary($id)->__toString()));
        $this->setIcon('fa fa-check-square-o');
    }

    public function authorizedTable() {
        $this->setAuthorized($this->eventIsAllowed('fyziklani', 'close'));
    }

    public function authorizedTeam() {
        $this->authorizedTable();
    }

    public function renderTeam() {
        $this->template->submits = $this->team->getSubmits();
    }

    public function actionTable() {
        /**
         * @var $button Button
         */
        if (!$this->isReadyToClose('A')) {
            $button = $this['closeCategoryAForm']['send'];
            $button->setDisabled();
        }
        if (!$this->isReadyToClose('B')) {
            $button = $this['closeCategoryBForm']['send'];
            $button->setDisabled();
        }
        if (!$this->isReadyToClose('C')) {
            $button = $this['closeCategoryCForm']['send'];
            $button->setDisabled();
        }
        if (!$this->isReadyToClose('F')) {
            $button = $this['closeCategoryFForm']['send'];
            $button->setDisabled();
        }
        if (!$this->isReadyToClose()) {
            $button = $this['closeGlobalForm']['send'];
            $button->setDisabled();
        }
    }

    /**
     * @param $id
     * @throws BadRequestException
     * @throws \Nette\Application\AbortException
     */
    public function actionTeam($id) {
        $this->team = $this->serviceFyziklaniTeam->findByPrimary($id);
        if (!$this->team) {
            throw new BadRequestException('Tým neexistuje', 404);
        }

        if (!$this->team->hasOpenSubmit()) {
            $this->flashMessage(sprintf(_('Tým %s má již uzavřeno bodování'), $this->team->name), 'danger');
            $this->backlinkRedirect();
            $this->redirect('table'); // if there's no backlink
        }
    }

    public function createComponentCloseGrid() {
        $grid = new FyziklaniTeamsGrid($this->getEventId(), $this->serviceFyziklaniTeam);
        return $grid;
    }

    public function createComponentCloseForm() {
        $form = new Form();
        $form->setRenderer(new BootstrapRenderer());
        $form->addCheckbox('submit_task_correct', _('Úkoly a počty bodů jsou správně.'))
            ->setRequired(_('Zkontrolujte správnost zadání bodů!'));
        $form->addText('next_task', _('Úloha u vydavačů'))
            ->setDisabled()
            ->setDefaultValue($this->getNextTask());
        $form->addCheckbox('next_task_correct', _('Úloha u vydavačů se shoduje.'))
            ->setRequired(_('Zkontrolujte prosím shodnost úlohy u vydavačů'));
        $form->addSubmit('send', 'Potvrdit správnost');
        $form->onSuccess[] = [$this, 'closeFormSucceeded'];
        return $form;
    }

    /**
     * @throws \Nette\Application\AbortException
     */
    public function closeFormSucceeded() {
        $connection = $this->serviceFyziklaniTeam->getConnection();
        $connection->beginTransaction();
        $submits = $this->team->getSubmits();
        $sum = 0;
        foreach ($submits as $submit) {
            $sum += $submit->points;
        }
        $this->serviceFyziklaniTeam->updateModel($this->team, ['points' => $sum]);
        $this->serviceFyziklaniTeam->save($this->team);
        $connection->commit();
        $this->backlinkRedirect();
        $this->redirect('table'); // if there's no backlink
    }

    private function createComponentCloseCategoryForm($category) {
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

    public function createComponentCloseCategoryFForm() {
        return $this->createComponentCloseCategoryForm('F');
    }

    /**
     * @param Form $form
     * @throws \Nette\Application\AbortException
     */
    public function closeCategoryFormSucceeded(Form $form) {
        $closeStrategy = new CloseSubmitStrategy($this->getEventId(), $this->serviceFyziklaniTeam);
        $closeStrategy->closeByCategory($form->getValues()->category, $msg);
        $this->flashMessage(Html::el()->add('pořadí bylo uložené' . Html::el('ul')->add($msg)), 'success');
        $this->redirect('this');
    }

    public function createComponentCloseGlobalForm() {
        $form = new Form();
        $form->setRenderer(new BootstrapRenderer());
        $form->addSubmit('send', _('Uzavřít celé Fyziklání'));
        $form->onSuccess[] = [$this, 'closeGlobalFormSucceeded'];
        return $form;
    }

    /**
     * @throws \Nette\Application\AbortException
     */
    public function closeGlobalFormSucceeded() {
        $closeStrategy = new CloseSubmitStrategy($this->getEventId(), $this->serviceFyziklaniTeam);
        $closeStrategy->closeGlobal($msg);
        $this->flashMessage(Html::el()->add('pořadí bylo uložené' . Html::el('ul')->add($msg)), 'success');
        $this->redirect('this');
    }

    private function isReadyToClose($category = null) {
        $query = $this->serviceFyziklaniTeam->findParticipating($this->getEventId());
        if ($category) {
            $query->where('category', $category);
        }
        $query->where('points', null);
        $count = $query->count();
        return $count == 0;
    }

    private function getNextTask() {
        $submits = count($this->team->getSubmits());

        $tasksOnBoard = $this->getEvent()->getParameter('tasksOnBoard');
        /**
         * @var $nextTask \ModelFyziklaniTask
         */
        $nextTask = $this->serviceFyziklaniTask->findAll($this->getEventId())->order('label')->limit(1, $submits + $tasksOnBoard)->fetch();
        return ($nextTask) ? $nextTask->label : '';
    }

}
