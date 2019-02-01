<?php

namespace FKSDB\Components\Controls\Fyziklani;

use FKSDB\Components\Controls\FormControl\FormControl;
use FKSDB\ORM\ModelEvent;
use Nette\Application\BadRequestException;
use Nette\Application\UI\Control;
use Nette\Localization\ITranslator;
use Nette\Templating\FileTemplate;
use ORM\Models\Events\ModelFyziklaniTeam;
use ORM\Services\Events\ServiceFyziklaniTeam;

/**
 * Class CloseTeamControl
 * @package FKSDB\Components\Controls\Fyziklani
 * @property FileTemplate $template
 */
class CloseTeamControl extends Control {
    /**
     * @var ServiceFyziklaniTeam
     */
    private $serviceFyziklaniTeam;
    /**
     * @var ModelEvent
     */
    private $event;
    /**
     * @var ITranslator
     */
    private $translator;
    /**
     * @var ModelFyziklaniTeam
     */
    private $team;
    /**
     * @var \ServiceFyziklaniTask
     */
    private $serviceFyziklaniTask;

    public function __construct(
        ModelEvent $event,
        ServiceFyziklaniTeam $serviceFyziklaniTeam,
        ITranslator $translator,
        \ServiceFyziklaniTask $serviceFyziklaniTask
    ) {
        parent::__construct();
        $this->event = $event;
        $this->serviceFyziklaniTeam = $serviceFyziklaniTeam;
        $this->translator = $translator;
        $this->serviceFyziklaniTask = $serviceFyziklaniTask;
    }

    /**
     * @param ModelFyziklaniTeam $team
     * @throws BadRequestException
     */
    public function setTeam(ModelFyziklaniTeam $team) {
        $this->team = $team;
        if (!$team->hasOpenSubmit()) {
            throw  new BadRequestException(sprintf(_('Tým %s má již uzavřeno bodování'), $this->team->name));
        }
    }

    public function getFormControl(): FormControl {
        return $this->getComponent('closeForm');
    }

    /**
     * @return FormControl
     */
    protected function createComponentCloseForm(): FormControl {
        $control = new FormControl();
        $form = $control->getForm();
        $form->addCheckbox('submit_task_correct', _('Úkoly a počty bodů jsou správně.'))
            ->setRequired(_('Zkontrolujte správnost zadání bodů!'));
        $form->addText('next_task', _('Úloha u vydavačů'))
            ->setDisabled();
        $form->addCheckbox('next_task_correct', _('Úloha u vydavačů se shoduje.'))
            ->setRequired(_('Zkontrolujte prosím shodnost úlohy u vydavačů'));
        $form->addSubmit('send', 'Potvrdit správnost');
        $form->onSuccess[] = function () {
            $this->closeFormSucceeded();
        };
        return $control;
    }

    /**
     *
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
        $this->getPresenter()->flashMessage(\sprintf(_('Tím %s má úspešňe uzatvorené bodovanie s počtom bodov %d'), $this->team->name, $sum), 'success');
    }

    /**
     *
     */
    public function render() {
        $this->getFormControl()->getForm()->setDefaults(['next_task' => $this->getNextTask()]);
        $this->template->submits = $this->team->getSubmits();
        $this->template->setFile(__DIR__ . DIRECTORY_SEPARATOR . 'CloseTeamControl.latte');
        $this->template->setTranslator($this->translator);
        $this->template->render();
    }

    /**
     * @return string
     */
    private function getNextTask(): string {
        $submits = count($this->team->getSubmits());

        $tasksOnBoard = $this->event->getFyziklaniGameSetup()->tasks_on_board;
        /**
         * @var $nextTask \ModelFyziklaniTask
         */
        $nextTask = $this->serviceFyziklaniTask->findAll($this->event)->order('label')->limit(1, $submits + $tasksOnBoard)->fetch();
        return ($nextTask) ? $nextTask->label : '';
    }

}
