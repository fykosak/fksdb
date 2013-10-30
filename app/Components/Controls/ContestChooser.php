<?php

namespace FKSDB\Components\Controls;

use ModelContest;
use ModelRole;
use Nette\Application\UI\Control;
use Nette\Http\Session;
use ServiceContest;
use YearCalculator;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 * 
 * @author Michal Koutný <michal@fykos.cz>
 */
class ContestChooser extends Control {

    const SESSION_PREFIX = 'contestPreset';

    /**
     * @var string  enum
     */
    private $role;

    /**
     * @var Session
     */
    private $session;

    /**
     * @var YearCalculator
     */
    private $yearCalculator;

    /**
     * @var ServiceContest
     */
    private $serviceContest;

    /**
     * @var ModelContest
     */
    private $contest;

    /**
     * @var int
     */
    private $year;

    /**
     * @var boolean
     */
    private $valid;
    private $initialized = false;

    function __construct($role, Session $session, YearCalculator $yearCalculator, ServiceContest $serviceContest) {
        $this->role = $role;
        $this->session = $session;
        $this->yearCalculator = $yearCalculator;
        $this->serviceContest = $serviceContest;
    }

    public function isValid() {
        $this->init();
        return $this->valid;
    }

    /**
     * Redirect to corrrect address accorging to the resolved values.
     */
    public function syncRedirect() {
        $this->init();

        $presenter = $this->getPresenter();

        $contestId = $this->contest->contest_id;
        if ($this->year != $presenter->year || $contestId != $presenter->contestId) {
            $presenter->redirect('this', array(
                'contestId' => $contestId,
                'year' => $this->year
            ));
        }
    }

    public function getRole() {
        return $this->role;
    }

    public function getContest() {
        $this->init();
        return $this->contest;
    }

    public function getYear() {
        $this->init();
        return $this->year;
    }

    private function init() {
        if ($this->initialized) {
            return;
        }
        $this->initialized = true;

        $contestIds = array_keys($this->getContests());
        if (count($contestIds) == 0) {
            $this->valid = false;
            return;
        }
        $this->valid = true;

        $session = $this->session->getSection(self::SESSION_PREFIX . $this->role);
        $presenter = $this->getPresenter();

        /* CONTEST */

        $contestId = null;
        // session
        if (isset($session->contestId)) {
            $contestId = $session->contestId;
        }
        // URL
        if ($presenter->contestId) {
            $contestId = $presenter->contestId;
        }

        // final check
        if (!in_array($contestId, $contestIds)) {
            $contestId = $contestIds[0]; // by default choose the first
        }
        $this->contest = $this->serviceContest->findByPrimary($contestId);


        /* YEAR */
        $year = $this->calculateYear($session, $this->contest);
        $this->year = $year;


        // remember
        $session->contestId = $this->contest->contest_id;
        $session->{$this->getRole() . 'year'} = $this->year;
    }

    /**
     * @return array of contests where user is either ORG or CONTESTANT
     */
    private function getContests() {
        if (!$this->getLogin()) {
            return array();
        }

        $ids = array();
        if ($this->role == ModelRole::ORG) {
            $ids = array_keys($this->getLogin()->getActiveOrgs($this->yearCalculator));
        } else if ($this->role == ModelRole::CONTESTANT) {
            $person = $this->getLogin()->getPerson();
            if ($person) {
                $ids = array_keys($person->getActiveContestants($this->yearCalculator));
            }
        }
        $result = array();
        foreach ($ids as $id) {
            $contest = $this->serviceContest->findByPrimary($id);
            $min = $this->yearCalculator->getFirstYear($contest);
            $max = $this->yearCalculator->getCurrentYear($contest);
            $result[$id] = (object) array(
                        'contest' => $contest,
                        'years' => array_reverse(range($min, $max)),
                        'currentYear' => $max,
            );
        }
        return $result;
    }

    private function isAllowedYear() {
        return $this->role == ModelRole::ORG;
    }

    private function getLogin() {
        return $this->getPresenter()->getUser()->getIdentity();
    }

    public function render() {
        $this->template->contests = $this->getContests();
        $this->template->isAllowedYear = $this->isAllowedYear();
        $this->template->currentContest = $this->getContest()->contest_id;
        $this->template->currentYear = $this->getYear();

        $this->template->setFile(__DIR__ . DIRECTORY_SEPARATOR . 'ContestChooser.latte');
        $this->template->render();
    }

    public function handleChange($contestId) {
        $presenter = $this->getPresenter();
        $backupYear = null;
        if (isset($presenter->year)) {
            $backupYear = $presenter->year;
            $presenter->year = null;
        }
        $contest = $this->serviceContest->findByPrimary($contestId);

        $year = $this->calculateYear($this->session, $contest);
        if (isset($presenter->year)) {
            $presenter->year = $backupYear;
        }

        if ($backupYear && $backupYear != $year) {
            $presenter->redirect('this', array('contestId' => $contestId, 'year' => $year));
        } else {
            $presenter->redirect('this', array('contestId' => $contestId));
        }
    }

    public function handleChangeYear($contest, $year) {
        $presenter = $this->getPresenter();
        $presenter->redirect('this', array(
            'contestId' => $contest, //WHY? contestId should be persistent
            'year' => $year));
    }

    private function calculateYear($session, $contest, $override = null) {
        $presenter = $this->getPresenter();
        $year = null;
        if ($this->isAllowedYear()) {
            // session
            if (isset($session->{$this->getRole() . 'year'})) {
                $year = $session->{$this->getRole() . 'year'};
            }
            // URL
            if ($presenter->year) {
                $year = $presenter->year;
            }
            // override
            if ($override) {
                $year = $override;
            }
        }

        if (!$this->yearCalculator->isValidYear($contest, $year)) {
            $year = $this->yearCalculator->getCurrentYear($contest);
        }
        return $year;
    }

}
