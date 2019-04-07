<?php

namespace FKSDB\Components\Forms\Factories\Fyziklani;

use FKSDB\Components\Controls\FormControl\FormControl;
use FKSDB\model\Fyziklani\CloseStrategy;
use FKSDB\ORM\Models\Fyziklani\ModelFyziklaniSubmit;
use FKSDB\ORM\Models\Fyziklani\ModelFyziklaniTeam;
use FKSDB\ORM\Models\ModelEvent;
use FKSDB\ORM\Services\Fyziklani\ServiceFyziklaniTeam;
use Nette\Application\AbortException;
use Nette\Application\BadRequestException;
use Nette\Utils\Html;

/**
 * Class CloseFormsFactory
 */
class CloseFormsFactory {

    /**
     * @var ServiceFyziklaniTeam
     */
    private $serviceFyziklaniTeam;

    /**
     * FyziklaniFactory constructor.
     * @param ServiceFyziklaniTeam $serviceFyziklaniTeam
     */
    public function __construct(ServiceFyziklaniTeam $serviceFyziklaniTeam) {
        $this->serviceFyziklaniTeam = $serviceFyziklaniTeam;
    }

    /* ********************* CLOSE SUBMITS ************************** */

    /**
     * @param string $category
     * @param ModelEvent $event
     * @return FormControl
     * @throws BadRequestException
     */
    public function createCloseCategoryForm(string $category, ModelEvent $event): FormControl {
        $control = new FormControl();
        $form = $control->getForm();
        $form->addSubmit('send', sprintf(_('Close %s category'), $category))->setDisabled(!$this->isReadyToClose($event, $category));
        $form->onSuccess[] = function () use ($control, $event, $category) {
            $this->handleFormSucceeded($control, $event, $category);
        };
        return $control;
    }

    /**
     * @param ModelEvent $event
     * @return FormControl
     * @throws BadRequestException
     */
    public function createCloseTotalForm(ModelEvent $event): FormControl {
        $control = new FormControl();
        $form = $control->getForm();
        $form->addSubmit('send', _('Close global results'))->setDisabled(!$this->isReadyToClose($event));
        $form->onSuccess[] = function () use ($control, $event) {
            $this->handleFormSucceeded($control, $event);
        };
        return $control;
    }

    /**
     * @param ModelFyziklaniTeam $team
     * @return FormControl
     * @throws BadRequestException
     */
    public function createCloseTeamForm(ModelFyziklaniTeam $team): FormControl {
        $control = new FormControl();
        $form = $control->getForm();
        $form->addText('next_task', _('Úloha u vydavačů'))
            ->setDisabled();
        $form->addSubmit('send', 'Potvrdit správnost');
        $form->onSuccess[] = function () use ($team, $control) {
            $this->handleCloseTeamSucceeded($team, $control);
        };
        return $control;
    }

    /**
     * @param ModelEvent $event
     * @param string $category
     * @return bool
     */
    private function isReadyToClose(ModelEvent $event, string $category = null): bool {
        $query = $this->serviceFyziklaniTeam->findParticipating($event);
        if ($category) {
            $query->where('category', $category);
        }
        $query->where('points', null);
        $count = $query->count();
        return $count == 0;
    }

    /**
     * @param FormControl $control
     * @param ModelEvent $event
     * @param string $category
     * @throws AbortException
     * @throws BadRequestException
     */
    private function handleFormSucceeded(FormControl $control, ModelEvent $event, string $category = null) {
        $closeStrategy = new CloseStrategy($event, $this->serviceFyziklaniTeam);
        $log = $closeStrategy($category);
        $control->getPresenter()->flashMessage(Html::el()->addHtml(Html::el('h3')->addHtml('Rankin has been saved.'))->addHtml(Html::el('ul')->addHtml($log)), \BasePresenter::FLASH_SUCCESS);
        $control->getPresenter()->redirect('this');
    }

    /**
     * @param ModelFyziklaniTeam $team
     * @param FormControl $control
     */
    private function handleCloseTeamSucceeded(ModelFyziklaniTeam $team, FormControl $control) {
        $connection = $this->serviceFyziklaniTeam->getConnection();
        $connection->beginTransaction();
        $submits = $team->getSubmits();
        $sum = 0;
        foreach ($submits as $row) {
            $submit = ModelFyziklaniSubmit::createFromTableRow($row);
            $sum += $submit->points;
        }
        $this->serviceFyziklaniTeam->updateModel($team, ['points' => $sum]);
        $this->serviceFyziklaniTeam->save($team);
        $connection->commit();
        $control->getPresenter()->flashMessage(\sprintf(_('Team %s has successfully closed submitting, with total %d points.'), $team->name, $sum), \BasePresenter::FLASH_SUCCESS);
    }
}
