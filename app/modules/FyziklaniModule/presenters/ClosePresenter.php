<?php

namespace FyziklaniModule;

use FKSDB\Components\Controls\FormControl\FormControl;
use FKSDB\Components\Controls\Fyziklani\CloseTeamControl;
use FKSDB\Components\Grids\Fyziklani\CloseTeamsGrid;
use FKSDB\ORM\Models\Fyziklani\ModelFyziklaniTeam;
use Nette\Application\AbortException;
use Nette\Application\BadRequestException;

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

    /* ******* TITLE ***********/
    public function titleList() {
        $this->setTitle(_('Uzavírání bodování'));
        $this->setIcon('fa fa-check');
    }

    public function titleTeam() {
        $this->setTitle(sprintf(_('Uzavírání bodování týmu "%s"'), $this->getTeam()->name));
        $this->setIcon('fa fa-check-square-o');
    }

    /* ******* authorized methods ***********/
    /**
     * @throws BadRequestException
     * @throws AbortException
     */
    public function authorizedList() {
        $this->setAuthorized($this->eventIsAllowed('fyziklani.close', 'list'));
    }

    /**
     * @throws BadRequestException
     * @throws AbortException
     */
    public function authorizedTeam() {
        $this->setAuthorized($this->eventIsAllowed('fyziklani.close', 'team'));
    }


    /**
     * @param int $id
     * @throws BadRequestException
     * @throws AbortException
     */
    public function actionTeam(int $id) {
        $row = $this->getServiceFyziklaniTeam()->findByPrimary($id);
        if (!$row) {
            throw new BadRequestException(_('Team does not exists'), 404);
        }
        $this->team = ModelFyziklaniTeam::createFromActiveRow($row);
        $control = $this->getComponent('closeTeamControl');
        if (!$control instanceof CloseTeamControl) {
            throw new BadRequestException();
        }

        try {
            $control->setTeam($this->team);
        } catch (BadRequestException $exception) {
            $this->flashMessage($exception->getMessage(), \BasePresenter::FLASH_ERROR);
            $this->redirect('list');
        }
    }


    /* ********* COMPONENTS ************* */

    /**
     * @return CloseTeamControl
     * @throws BadRequestException
     * @throws AbortException
     */
    protected function createComponentCloseTeamControl(): CloseTeamControl {
        $control = $this->fyziklaniComponentsFactory->createCloseTeamControl($this->getEvent());
        $control->getFormControl()->getForm()->onSuccess[] = function () {
            $this->getPresenter()->redirect('list');
        };
        return $control;
    }

    /**
     * @return CloseTeamsGrid
     * @throws BadRequestException
     * @throws AbortException
     */
    protected function createComponentCloseGrid(): CloseTeamsGrid {
        return $this->fyziklaniComponentsFactory->createCloseTeamsGrid($this->getEvent());
    }

    /**
     * @return FormControl
     * @throws BadRequestException
     * @throws AbortException
     */
    public function createComponentCloseAForm(): FormControl {
        $control = $this->fyziklaniComponentsFactory->getCloseFormsFactory()->createCloseCategoryForm('A', $this->getEvent());
        $control->getForm()->onSuccess[] = function () {
            $this->redirect('this');
        };
        return $control;
    }

    /**
     * @return FormControl
     * @throws BadRequestException
     * @throws AbortException
     */
    public function createComponentCloseBForm(): FormControl {
        $control = $this->fyziklaniComponentsFactory->getCloseFormsFactory()->createCloseCategoryForm('B', $this->getEvent());
        $control->getForm()->onSuccess[] = function () {
            $this->redirect('this');
        };
        return $control;
    }

    /**
     * @return FormControl
     * @throws BadRequestException
     * @throws AbortException
     */
    public function createComponentCloseCForm(): FormControl {
        $control = $this->fyziklaniComponentsFactory->getCloseFormsFactory()->createCloseCategoryForm('C', $this->getEvent());
        $control->getForm()->onSuccess[] = function () {
            $this->redirect('this');
        };
        return $control;
    }

    /**
     * @return FormControl
     * @throws BadRequestException
     * @throws AbortException
     */
    public function createComponentCloseFForm(): FormControl {
        $control = $this->fyziklaniComponentsFactory->getCloseFormsFactory()->createCloseCategoryForm('F', $this->getEvent());
        $control->getForm()->onSuccess[] = function () {
            $this->redirect('this');
        };
        return $control;
    }

    /**
     * @return FormControl
     * @throws BadRequestException
     * @throws AbortException
     */
    public function createComponentCloseTotalForm(): FormControl {
        $control = $this->fyziklaniComponentsFactory->getCloseFormsFactory()->createCloseTotalForm($this->getEvent());
        $control->getForm()->onSuccess[] = function () {
            $this->redirect('this');
        };
        return $control;
    }
}
