<?php

namespace BrawlModule;

use FKSDB\Components\Grids\Brawl\BrawlTeamsGrid;
use FKSDB\model\Brawl\CloseSubmitStrategy;
use Kdyby\BootstrapFormRenderer\BootstrapRenderer;
use Nette\Application\BadRequestException;
use Nette\Application\UI\Form;
use Nette\Forms\Controls\SubmitButton;
use Nette\Utils\Html;
use ORM\Models\Events\ModelFyziklaniTeam;

class ClosePresenter extends BasePresenter {

    /** @var ModelFyziklaniTeam */
    private $team;

    public function titleTable() {
        $this->setTitle(_('Uzavírání bodování'));
    }

    public function titleTeam($id) {
        $this->setTitle(sprintf(_('Uzavírání bodování týmu "%s"'), $this->serviceBrawlTeam->findByPrimary($id)->__toString()));
    }

    public function authorizedTable() {
        $this->setAuthorized($this->eventIsAllowed('brawl', 'close'));
    }

    public function authorizedTeam() {
        $this->authorizedTable();
    }

    public function renderTeam() {
        $this->template->submits = $this->team->getSubmits();
    }

    public function actionTable() {
        if (!$this->isReadyToClose('A')) {
            /**
             * @var $component SubmitButton
             */
            $component = $this['closeCategoryAForm']['send'];
            $component->setDisabled();
        }
        if (!$this->isReadyToClose('B')) {
            /**
             * @var $component SubmitButton
             */
            $component = $this['closeCategoryBForm']['send'];
            $component->setDisabled();
        }
        if (!$this->isReadyToClose('C')) {
            /**
             * @var $component SubmitButton
             */
            $component = $this['closeCategoryCForm']['send'];
            $component->setDisabled();
        }
        if (!$this->isReadyToClose()) {
            /**
             * @var $component SubmitButton
             */
            $component = $this['closeGlobalForm']['send'];
            $component->setDisabled();
        }
    }

    public function actionTeam($id) {
        $this->team = $this->serviceBrawlTeam->findByPrimary($id);
        if (!$this->team) {
            throw new BadRequestException('Tým neexistuje', 404);
        }
        //TODO replace isOpenSubmit with method of team object
        if (!$this->serviceBrawlTeam->isOpenSubmit($id)) {
            $this->flashMessage(sprintf(_('Tým %s má již uzavřeno bodování'), $this->team->name), 'danger');
            $this->backlinkRedirect();
            $this->redirect('table'); // if there's no backlink
        }
    }

    public function createComponentCloseGrid() {
        $grid = new BrawlTeamsGrid($this->getEventId(), $this->serviceBrawlTeam);
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

    public function closeFormSucceeded() {
        $connection = $this->serviceBrawlTeam->getConnection();
        $connection->beginTransaction();
        $submits = $this->team->getSubmits();
        $sum = 0;
        foreach ($submits as $submit) {
            $sum += $submit->points;
        }
        $this->serviceBrawlTeam->updateModel($this->team, ['points' => $sum]);
        $this->serviceBrawlTeam->save($this->team);
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

    public function closeCategoryFormSucceeded(Form $form) {
        $closeStrategy = new CloseSubmitStrategy($this->getEventId(), $this->serviceBrawlTeam);
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

    public function closeGlobalFormSucceeded() {
        $closeStrategy = new CloseSubmitStrategy($this->getEventId(), $this->serviceBrawlTeam);
        $closeStrategy->closeGlobal($msg);
        $this->flashMessage(Html::el()->add('pořadí bylo uložené' . Html::el('ul')->add($msg)), 'success');
        $this->redirect('this');
    }

    private function isReadyToClose($category = null) {
        $query = $this->serviceBrawlTeam->findParticipating($this->getEventId());
        if ($category) {
            $query->where('category', $category);
        }
        $query->where('points', null);
        $count = $query->count();
        return $count == 0;
    }

    private function getNextTask() {
        $submits = count($this->team->getSubmits());
        $tasksOnBoard = $this->getCurrentEvent()->getParameter('tasksOnBoard');
        $nextTask = $this->serviceBrawlTask->findAll($this->getEventId())->order('label')->limit(1, $submits + $tasksOnBoard)->fetch();
        return ($nextTask) ? $nextTask->label : '';
    }

}
