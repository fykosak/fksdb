<?php

namespace FyziklaniModule;

use FKSDB\Components\Controls\FormControl\FormControl;
use FKSDB\Components\Grids\Fyziklani\FyziklaniTeamsGrid;
use FKSDB\model\Fyziklani\CloseSubmitStrategy;
use Nette\Application\BadRequestException;
use Nette\Application\UI\Form;
use Nette\Forms\Controls\Button;
use Nette\Utils\Html;
use ORM\Models\Events\ModelFyziklaniTeam;

/**
 * Class ClosePresenter
 * @package FyziklaniModule
 * @property FormControl closeCategoryAForm
 */
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
            $button = $this['closeCategoryAForm']->getForm()['send'];
            $button->setDisabled();
        }
        if (!$this->isReadyToClose('B')) {
            $button = $this['closeCategoryBForm']->getForm()['send'];
            $button->setDisabled();
        }
        if (!$this->isReadyToClose('C')) {
            $button = $this['closeCategoryCForm']->getForm()['send'];
            $button->setDisabled();
        }
        if (!$this->isReadyToClose('F')) {
            $button = $this['closeCategoryFForm']->getForm()['send'];
            $button->setDisabled();
        }
        if (!$this->isReadyToClose()) {
            $button = $this['closeGlobalForm']->getForm()['send'];
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

    /**
     * @return FyziklaniTeamsGrid
     * @throws \Nette\Application\AbortException
     */
    public function createComponentCloseGrid(): FyziklaniTeamsGrid {
        return new FyziklaniTeamsGrid($this->getEventId(), $this->serviceFyziklaniTeam);
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

    /**
     * @param $category
     * @return FormControl
     */
    private function createComponentCloseCategoryForm($category): FormControl {
        $control = new FormControl();
        $form = $control->getForm();
        $form->addHidden('category', $category);
        $form->addSubmit('send', sprintf(_('Uzavřít kategorii %s.'), $category));
        $form->onSuccess[] = function (Form $form) {
            $this->closeCategoryFormSucceeded($form);
        };
        return $control;
    }

    /**
     * @return FormControl
     */
    public function createComponentCloseCategoryAForm(): FormControl {
        return $this->createComponentCloseCategoryForm('A');
    }

    /**
     * @return FormControl
     */
    public function createComponentCloseCategoryBForm(): FormControl {
        return $this->createComponentCloseCategoryForm('B');
    }

    /**
     * @return FormControl
     */
    public function createComponentCloseCategoryCForm(): FormControl {
        return $this->createComponentCloseCategoryForm('C');
    }

    /**
     * @return FormControl
     */
    public function createComponentCloseCategoryFForm(): FormControl {
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

    /**
     * @return FormControl
     */
    public function createComponentCloseGlobalForm(): FormControl {
        $control = new FormControl();
        $form = $control->getForm();
        $form->addSubmit('send', _('Uzavřít celé Fyziklání'));
        $form->onSuccess[] = function () {
            $this->closeGlobalFormSucceeded();
        };
        return $control;
    }

    /**
     * @throws \Nette\Application\AbortException
     */
    private function closeGlobalFormSucceeded() {
        $closeStrategy = new CloseSubmitStrategy($this->getEventId(), $this->serviceFyziklaniTeam);
        $closeStrategy->closeGlobal($msg);
        $this->flashMessage(Html::el()->add('pořadí bylo uložené' . Html::el('ul')->add($msg)), 'success');
        $this->redirect('this');
    }

    /**
     * @param null $category
     * @return bool
     * @throws \Nette\Application\AbortException
     */
    private function isReadyToClose($category = null): bool {
        $query = $this->serviceFyziklaniTeam->findParticipating($this->getEventId());
        if ($category) {
            $query->where('category', $category);
        }
        $query->where('points', null);
        $count = $query->count();
        return $count == 0;
    }

    /**
     * @return string
     * @throws BadRequestException
     * @throws \Nette\Application\AbortException
     */
    private function getNextTask(): string {
        $submits = count($this->team->getSubmits());

        $tasksOnBoard = $this->getEvent()->getParameter('gameSetup')['tasksOnBoard'];
        /**
         * @var $nextTask \ModelFyziklaniTask
         */
        $nextTask = $this->serviceFyziklaniTask->findAll($this->getEventId())->order('label')->limit(1, $submits + $tasksOnBoard)->fetch();
        return ($nextTask) ? $nextTask->label : '';
    }

}
