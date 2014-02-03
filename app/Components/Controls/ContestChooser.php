<?php

namespace FKSDB\Components\Controls;

use ModelContest;
use ModelRole;
use Nette\Application\BadRequestException;
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
    const ALL_CONTESTS = '__*';

    /**
     * @var mixed
     */
    private $contestsDefinition;

    /**
     * @var ModelContest[]
     */
    private $contests;

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

    /**
     * 

     * @param Session $session
     * @param YearCalculator $yearCalculator
     * @param ServiceContest $serviceContest
     */
    function __construct(Session $session, YearCalculator $yearCalculator, ServiceContest $serviceContest) {
        $this->session = $session;
        $this->yearCalculator = $yearCalculator;
        $this->serviceContest = $serviceContest;
    }

    /**
     * @param mixed $contestsDefinition role enum|ALL_CONTESTS|array of contests
     */
    public function setContests($contestsDefinition) {
        $this->contestsDefinition = $contestsDefinition;
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

        $session = $this->session->getSection(self::SESSION_PREFIX);
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
            $contestId = reset($contestIds); // by default choose the first
        }
        $this->contest = $this->serviceContest->findByPrimary($contestId);


        /* YEAR */
        $year = $this->calculateYear($session, $this->contest);
        $this->year = $year;


        // remember
        $session->contestId = $this->contest->contest_id;
        $session->year = $this->year;
    }

    /**
     * @return array of contests where user is either ORG or CONTESTANT
     */
    private function getContests() {
        if ($this->contests === null) {
            if (is_array($this->contestsDefinition)) { // explicit
                $contests = array_map(function($contest) {
                            return ($contest instanceof ModelContest) ? $contest->contest_id : $contest;
                        }, $this->contestsDefinition);
            } else if ($this->contestsDefinition === self::ALL_CONTESTS) { // all
                $pk = $this->serviceContest->getPrimary();
                $contests = $this->serviceContest->fetchPairs($pk, $pk);
            } else { // implicity -- by role
                $login = $this->getLogin();

                if (!$login) {
                    $contests = array();
                }

                $contests = array();
                if ($this->contestsDefinition == ModelRole::ORG) {
                    $contests = array_keys($this->getLogin()->getActiveOrgs($this->yearCalculator));
                } else if ($this->contestsDefinition == ModelRole::CONTESTANT) {
                    $person = $this->getLogin()->getPerson();
                    if ($person) {
                        $contests = array_keys($person->getActiveContestants($this->yearCalculator));
                    }
                }
            }
            $this->contests = array();
            foreach ($contests as $id) {
                $contest = $this->serviceContest->findByPrimary($id);
                $min = $this->yearCalculator->getFirstYear($contest);
                $max = $this->yearCalculator->getCurrentYear($contest);
                $this->contests[$id] = (object) array(
                            'contest' => $contest,
                            'years' => array_reverse(range($min, $max)),
                            'currentYear' => $max,
                );
            }
        }
        return $this->contests;
    }

    private function isAllowedYear() {
        return $this->contestsDefinition == ModelRole::ORG;
    }

    private function getLogin() {
        return $this->getPresenter()->getUser()->getIdentity();
    }

    public function render() {
        if (!$this->isValid()) {
            throw new BadRequestException('No contests available.', 403);
        }
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
            if (isset($session->year)) {
                $year = $session->year;
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
