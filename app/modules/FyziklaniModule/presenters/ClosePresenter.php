<?php

namespace FyziklaniModule;

use FKSDB\Components\Controls\FormControl\FormControl;
use FKSDB\Components\Controls\Fyziklani\CloseControl;
use FKSDB\Components\Controls\Fyziklani\CloseTeamControl;
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
            $this->backLinkRedirect();
            $this->redirect('list'); // if there's no backlink
        } else {
            $this->getComponent('closeTeamControl')->setTeam($this->getTeam());
        }
    }

    /**
     * @return CloseControl
     * @throws BadRequestException
     * @throws \Nette\Application\AbortException
     */
    protected function createComponentCloseControl(): CloseControl {
        return $this->fyziklaniComponentsFactory->createCloseControl($this->getEvent());
    }

    /**
     * @return CloseTeamControl
     * @throws BadRequestException
     * @throws \Nette\Application\AbortException
     */
    protected function createComponentCloseTeamControl(): CloseTeamControl {
        $control = $this->fyziklaniComponentsFactory->createCloseTeamControl($this->getEvent());
        $control->getFormControl()->getForm()->onSuccess[] = function () {
            $this->getPresenter()->redirect('list');
        };
        return $control;
    }
}
