<?php

namespace FKSDB\Components\Controls\ContestNav;

use ModelRole;
use Nette\Application\BadRequestException;
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
        parent::__construct($session, $serviceContest);
        $this->yearCalculator = $yearCalculator;
    }

    /**
     * @param $params object
     * @return integer
     * Redirect to corrrect address according to the resolved values.
     */
    public function syncRedirect($params) {
        $this->init($params);
        if ($this->year != $params->year) {
            return $this->year;
        }
        return null;
    }

    public function getYear() {
        return $this->year;
    }

    protected function init($params) {
        if ($this->initialized) {
            return;
        }
        $this->initialized = true;

        $session = $this->session->getSection(self::SESSION_PREFIX);
        $this->contest = is_null($params->contestId) ? null : $this->serviceContest->findByPrimary($params->contestId);

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
        } else {

            /**
             * @var $login \ModelLogin
             */
            $login = $this->getLogin();
            if (is_null($this->contest)) {
                return [];
            }
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
