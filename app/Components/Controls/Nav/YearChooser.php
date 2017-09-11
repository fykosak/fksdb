<?php

namespace FKSDB\Components\Controls\Nav;

use ModelRole;
use Nette\Application\BadRequestException;
use Nette\Diagnostics\Debugger;
use Nette\Http\Session;
use ServiceContest;
use YearCalculator;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 *
 * @author Michal Koutný <michal@fykos.cz>
 * @author Michal Červeňák <miso@fykos.cz>
 */
class YearChooser extends Nav {

    /**
     * @var integer[]
     */
    private $yearDefinition;

    /**
     * @var YearCalculator
     */
    private $yearCalculator;

    /**
     * @var int
     */
    private $year;

    /**
     *
     * @param Session $session
     * @param YearCalculator $yearCalculator
     * @param ServiceContest $serviceContest
     */
    function __construct(Session $session, YearCalculator $yearCalculator, ServiceContest $serviceContest) {
        parent::__construct();
        $this->session = $session;
        $this->yearCalculator = $yearCalculator;
        $this->serviceContest = $serviceContest;
    }

    /**
     * @param integer[] $yearDefinition enum
     */
    public function setYears($yearDefinition) {
        $this->yearDefinition = $yearDefinition;
    }

    public function isValid() {
        $this->init();
        return $this->valid;
    }

    /**
     * Redirect to corrrect address according to the resolved values.
     */
    public function syncRedirect() {
        $this->init();
        /**
         * @var $presenter \OrgModule\BasePresenter|\PublicModule\BasePresenter
         */
        $presenter = $this->getPresenter();
        if ($this->year != $presenter->year) {
            return $this->year;
        }
        return null;
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

        $this->valid = true;
        $session = $this->session->getSection(self::SESSION_PREFIX);
        /**
         * @var $presenter \OrgModule\BasePresenter|\PublicModule\BasePresenter
         */
        $presenter = $this->getPresenter();
        $presentersContest = $presenter->getSelectedContest();
        $contestId = is_null($presentersContest) ? null : $presentersContest->contest_id;

        $this->contest = $this->serviceContest->findByPrimary($contestId);

        if ($this->contest === null) {
            $this->year = null;
        } else {
            /* YEAR */
            $year = $this->calculateYear($session);
            $this->year = $year;
            $session->year = $this->year;
        }
    }

    private function getYears() {

        if ($this->role === ModelRole::ORG) {
            $min = $this->yearCalculator->getFirstYear($this->contest);
            $max = $this->yearCalculator->getLastYear($this->contest);
            return array_reverse(range($min, $max));
        } elseif (count($this->yearDefinition) > 0) {
            return $this->yearDefinition;
        } else {

            /**
             * @var $login \ModelLogin
             */
            $login = $this->getLogin();
            $currentYear = $this->yearCalculator->getCurrentYear($this->contest);
            if (!$login || !$login->getPerson()) {
                return [$currentYear];
            }
            $contestants = $login->getPerson()->getContestants($this->contest->contest_id);
            $years = [];
            foreach ($contestants as $contestant) {
                $years[] = $contestant->year;
            }

            sort($years);
            return $years;
        }
    }

    public function render() {
        if (!$this->isValid()) {
            throw new BadRequestException('No years available.', 403);
        }
        $this->template->years = $this->getYears();
        $this->template->currentYear = $this->getYear();

        $this->template->setFile(__DIR__ . DIRECTORY_SEPARATOR . 'YearChooser.latte');
        $this->template->render();
    }

    /**
     * @param $year integer
     */
    public function handleChange($year) {
        $presenter = $this->getPresenter();
        $presenter->redirect('this', [
                'year' => $year,
            ]
        );
    }

// WTF TODO refacroeing
    private function calculateYear($session, $contest = null, $override = null) {
        $contest = $contest ?: $this->contest;
        $presenter = $this->getPresenter();
        $year = null;
        // session
        if (isset($session->year)) {
            $year = $session->year;
        }
        // URL
        if (isset($presenter->year)) {
            $year = $presenter->year;
        }
        // override
        if ($override) {
            $year = $override;
        }

        $allowedYears = $this->getYears();
        if (!$this->yearCalculator->isValidYear($contest, $year) || !in_array($year, $allowedYears)) {
            $currentYear = $this->yearCalculator->getCurrentYear($contest);
            $forwardYear = $currentYear + $this->yearCalculator->getForwardShift($contest);
            if (in_array($forwardYear, $allowedYears)) {
                $year = $forwardYear;
            } else {
                $year = $currentYear;
            }
        }
        return $year;
    }
}
