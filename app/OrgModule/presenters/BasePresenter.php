<?php

namespace OrgModule;

use AuthenticatedPresenter;
use AuthenticationPresenter;
use FKSDB\Components\Controls\ContestChooser;
use IContestPresenter;
use ModelRole;
use Nette\Application\BadRequestException;

/**
 * Presenter keeps chosen contest and year in session.
 * 
 * @author Michal Koutný <michal@fykos.cz>
 */
abstract class BasePresenter extends AuthenticatedPresenter implements IContestPresenter {

    /**
     * @var int
     * @persistent
     */
    public $contestId;

    /**
     * @var int
     * @persistent
     */
    public $year;

    protected function startup() {
        parent::startup();

        if (!$this['contestChooser']->isValid()) {
            if ($this->getParam(AuthenticationPresenter::PARAM_DISPATCH)) {
                throw new BadRequestException('Neautoriziván pro žádný seminář.', 403);
            } else {
                $this->redirect(':Authentication:login');
            }
        }
    }

    protected function createComponentContestChooser($name) {
        $control = new ContestChooser(ModelRole::ORG, $this->session, $this->yearCalculator, $this->serviceContest);
        $control->setForceRedirect(true);
        return $control;
    }

    public function getSelectedContest() {
        return $this['contestChooser']->getContest();
    }

    public function getSelectedYear() {
        return $this['contestChooser']->getYear();
    }

}
