<?php

namespace PublicModule;

use AuthenticatedPresenter;
use AuthenticationPresenter;
use FKSDB\Components\Controls\ContestChooser;
use IContestPresenter;
use ModelContestant;
use ModelRole;
use Nette\Application\BadRequestException;

/**
 * Current year of FYKOS.
 * 
 * @todo Contest should be from URL and year should be current.
 * 
 * @author Michal Koutný <michal@fykos.cz>
 */
class BasePresenter extends AuthenticatedPresenter implements IContestPresenter {

    const PRESETS_KEY = 'publicPresets';

    /**
     * @persistent
     */
    public $contestId;

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
        $control = new ContestChooser(ModelRole::CONTESTANT, $this->session, $this->yearCalculator, $this->serviceContest);
        return $control;
    }

    /** @var ModelContestant|null|false */
    private $contestant = false;

    public function getSelectedContest() {
        return $this['contestChooser']->getContest();
    }

    public function getSelectedYear() {
        return $this['contestChooser']->getYear();
    }

    public function getContestant() {
        if ($this->contestant === false) {
            $person = $this->user->getIdentity()->getPerson();
            $contestants = $person->getActiveContestants($this->yearCalculator);
            $this->contestant = $contestants[$this->getSelectedContest()->contest_id];
        }

        return $this->contestant;
    }

}
