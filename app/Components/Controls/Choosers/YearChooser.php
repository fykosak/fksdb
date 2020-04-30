<?php

namespace FKSDB\Components\Controls\Choosers;

use FKSDB\ORM\Models\ModelContest;
use FKSDB\ORM\Models\ModelContestant;
use FKSDB\ORM\Models\ModelRole;
use FKSDB\UI\Title;
use FKSDB\YearCalculator;
use Nette\Application\UI\InvalidLinkException;
use Nette\Database\Table\ActiveRow;
use Nette\DI\Container;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 *
 * @author Michal Koutný <michal@fykos.cz>
 * @author Michal Červeňák <miso@fykos.cz>
 */
class YearChooser extends ContestChooser {

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
     * @param Container $container
     */
    function __construct(Container $container) {
        parent::__construct($container);
        $this->yearCalculator = $container->getByType(YearCalculator::class);
    }

    /**
     * @param object $params
     * @return integer
     * Redirect to corrrect address according to the resolved values.
     */
    public function syncRedirect(&$params) {
        $this->init($params);
        if ($this->year != $params->year) {
            $params->year = $this->year;
            return true;
        }
        return false;
    }

    /**
     * @return int
     */
    public function getYear() {
        return $this->year;
    }

    /**
     * @param object $params
     */
    protected function init($params) {
        if ($this->initialized) {
            return;
        }
        $this->initialized = true;
        $this->role = $params->role;

        $session = $this->session->getSection(self::SESSION_PREFIX);
        $this->contest = is_null($params->contestId) ? null : $this->serviceContest->findByPrimary($params->contestId);

        if ($this->contest === null) {
            $this->year = null;
        } else {
            /* YEAR */
            $year = $this->calculateYear($session, $params, $this->contest);
            $this->year = $year;
            $session->year = $this->year;
        }
    }

    /**
     * @return array
     */
    private function getYears() {

        if ($this->role === ModelRole::ORG) {
            $min = $this->yearCalculator->getFirstYear($this->contest);
            $max = $this->yearCalculator->getLastYear($this->contest);
            return array_reverse(range($min, $max));
        } else {
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
            /** @var ModelContestant|ActiveRow $contestant */
            foreach ($contestants as $contestant) {
                $years[] = $contestant->year;
            }

            sort($years);
            return $years;
        }
    }

    /**
     * @param $session
     * @param $params
     * @param ModelContest $contest
     * @param null $override
     * @return int|mixed|null
     */
    private function calculateYear($session, $params, ModelContest $contest, $override = null) {
        $year = null;
        // session
        if (isset($session->year)) {
            $year = $session->year;
        }
        // URL
        if (isset($params->year)) {
            $year = $params->year;
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
                $year = count($allowedYears) ? array_pop($allowedYears) : -1;
            }
        }
        return $year;
    }

    /**
     * @return Title
     */
    protected function getTitle(): Title {
        $headline = $this->getYear() ? sprintf(_('Contest year %d'), $this->getYear()) : _('Contest year');
        return new Title($headline);
    }

    /**
     * @return int[]
     */
    protected function getItems() {
        return $this->getYears();
    }

    /**
     * @param int $item
     * @return bool
     */
    public function isItemActive($item): bool {
        return $item === $this->getYear();
    }

    /**
     * @param int $item
     * @return string
     */
    public function getItemLabel($item): string {
        return sprintf(_('Contest year %d'), $item);
    }

    /**
     * @param int $item
     * @return string
     * @throws InvalidLinkException
     */
    public function getItemLink($item): string {
        return $this->getPresenter()->link('this', ['year' => $item]);
    }
}
