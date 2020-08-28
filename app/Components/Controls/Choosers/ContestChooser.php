<?php

namespace FKSDB\Components\Controls\Choosers;

use FKSDB\Components\Controls\BaseComponent;
use FKSDB\Modules\Core\ContestPresenter\ContestPresenter;
use FKSDB\ORM\Models\ModelContest;
use FKSDB\ORM\Models\ModelContestant;
use FKSDB\ORM\Models\ModelLogin;
use FKSDB\ORM\Models\ModelRole;
use FKSDB\ORM\Services\ServiceContest;
use FKSDB\YearCalculator;
use Nette\Application\AbortException;
use Nette\Application\ForbiddenRequestException;
use Nette\Http\Session;
use Nette\Http\SessionSection;
use Nette\Security\IIdentity;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 *
 * @author Michal Koutný <michal@fykos.cz>
 * @method ContestPresenter getPresenter($need = true)
 */
class ContestChooser extends BaseComponent {

    public const SOURCE_SESSION = 0x1;
    public const SOURCE_URL = 0x2;
    public const SESSION_PREFIX = 'contestPreset';
    public const CONTESTS_ALL = '__*';
    public const YEARS_ALL = '__*';
    /** @obsolete (no first contest anymore) */
    public const DEFAULT_FIRST = 'first';
    public const DEFAULT_SMART_FIRST = 'smfirst';
    public const DEFAULT_NULL = 'null';

    /** @var string */
    private $contestsDefinition;

    /** @var string */
    private $yearDefinition;

    /** @var ModelContest[] */
    private $contests;

    private Session $session;

    private YearCalculator $yearCalculator;

    private ServiceContest $serviceContest;

    /** @var ModelContest */
    private $contest;

    /** @var int */
    private $year;

    /** @var bool */
    private $valid;

    /** @var bool */
    private $initialized = false;

    /** @var string DEFAULT_* */
    private $defaultContest = self::DEFAULT_SMART_FIRST;

    /** @var int bitmask of what "sources" are used to infer selected contest */
    private $contestSource = 0xffffffff;

    public function injectPrimary(Session $session, YearCalculator $yearCalculator, ServiceContest $serviceContest): void {
        $this->session = $session;
        $this->yearCalculator = $yearCalculator;
        $this->serviceContest = $serviceContest;
    }

    /**
     * @param string|array role enum|CONTESTS_ALL|array of contests
     */
    public function setContests($contestsDefinition): void {
        $this->contestsDefinition = $contestsDefinition;
    }

    /**
     *
     * @param string|array $yearDefinition
     */
    public function setYears($yearDefinition): void {
        $this->yearDefinition = $yearDefinition;
    }

    /**
     * @return string
     */
    public function getDefaultContest() {
        return $this->defaultContest;
    }

    /**
     * @param mixed $defaultContest
     * @return void
     */
    public function setDefaultContest($defaultContest): void {
        $this->defaultContest = $defaultContest;
    }

    /**
     * @return int
     */
    public function getContestSource() {
        return $this->contestSource;
    }

    /**
     * @param mixed $contestSource
     * @return void
     */
    public function setContestSource($contestSource): void {
        $this->contestSource = $contestSource;
    }

    public function isValid(): bool {
        $this->init();
        return $this->valid;
    }

    /**
     * Redirect to corrrect address according to the resolved values.
     * @throws AbortException
     */
    public function syncRedirect(): void {
        $this->init();

        $presenter = $this->getPresenter();

        $contestId = isset($this->contest) ? $this->contest->contest_id : null;
        if ($this->year != $presenter->year || $contestId != $presenter->contestId) {
            $presenter->redirect('this', [
                'contestId' => $contestId,
                'year' => $this->year,
            ]);
        }
    }

    /**
     * @return ModelContest
     */
    public function getContest() {
        $this->init();
        return $this->contest;
    }

    /**
     * @return int
     */
    public function getYear() {
        $this->init();
        return $this->year;
    }

    /**
     * @return void|null
     */
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
        if (($this->contestSource & self::SOURCE_SESSION) && isset($session->contestId)) {
            $contestId = $session->contestId;
        }
        // URL
        if (($this->contestSource & self::SOURCE_URL) && $presenter->contestId) {
            $contestId = $presenter->contestId;
        }

        // final check
        if (!in_array($contestId, $contestIds)) {
            switch ($this->defaultContest) {
                case self::DEFAULT_FIRST:
                    $contestId = reset($contestIds);
                    break;
                case self::DEFAULT_SMART_FIRST:
                    /* No contest is not prioritized when all should be shown.
                     * On the other hand, usually declarative definition leads to only one contest
                     * available, so use the first available.
                     */
                    if ($this->contestsDefinition === self::CONTESTS_ALL) {
                        return null;
                    } else {
                        $contestId = reset($contestIds);
                    }
                    break;
                case self::DEFAULT_NULL:
                    $contestId = null;
                    break;
            }
        }

        $this->contest = $this->serviceContest->findByPrimary($contestId);

        if ($this->contest === null) {
            $this->year = null;
        } else {
            /* YEAR */
            $year = $this->calculateYear($session, $this->contest);
            $this->year = $year;


            // remember
            $session->contestId = $this->contest->contest_id;
            $session->year = $this->year;
        }
    }

    /**
     * @return array of contests where user is either ORG or CONTESTANT
     */
    private function getContests() {
        if ($this->contests === null) {
            if (is_array($this->contestsDefinition)) { // explicit
                $contests = array_map(function ($contest) {
                    return ($contest instanceof ModelContest) ? $contest->contest_id : $contest;
                }, $this->contestsDefinition);
            } elseif ($this->contestsDefinition === self::CONTESTS_ALL) { // all
                $pk = $this->serviceContest->getPrimary();
                $contests = $this->serviceContest->fetchPairs($pk, $pk);
            } else { // implicity -- by role
                $contests = [];
                $login = $this->getLogin();
                if ($login) {
                    if ($this->contestsDefinition == ModelRole::ORG) {
                        $contests = array_keys($login->getActiveOrgs($this->yearCalculator));
                    } elseif ($this->contestsDefinition == ModelRole::CONTESTANT) {
                        $person = $login->getPerson();
                        if ($person) {
                            $contests = array_keys($person->getActiveContestants($this->yearCalculator));
                        }
                    }
                }
            }
            $this->contests = [];
            foreach ($contests as $id) {
                $contest = $this->serviceContest->findByPrimary($id);
                $years = $this->getYears($contest);
                $this->contests[$id] = (object)[
                    'contest' => $contest,
                    'years' => $years,
                    'currentYear' => $this->yearCalculator->getCurrentYear($contest),
                ];
            }
        }
        return $this->contests;
    }

    /**
     * @param ModelContest $contest
     * @return array
     */
    private function getYears(ModelContest $contest) {
        if ($this->yearDefinition === self::YEARS_ALL || $this->contestsDefinition == ModelRole::ORG) {
            $min = $this->yearCalculator->getFirstYear($contest);
            $max = $this->yearCalculator->getLastYear($contest);
            return array_reverse(range($min, $max));
        } else {
            /** @var ModelLogin $login */
            $login = $this->getLogin();
            $currentYear = $this->yearCalculator->getCurrentYear($contest);
            if (!$login || !$login->getPerson()) {
                return [$currentYear];
            }
            $contestants = $login->getPerson()->getContestants($contest);
            $years = [];
            /** @var ModelContestant $contestant */
            foreach ($contestants as $contestant) {
                $years[] = $contestant->year;
            }

            sort($years);
            return $years;
        }
    }

    /**
     * @return IIdentity|ModelLogin|NULL
     */
    private function getLogin() {
        return $this->getPresenter()->getUser()->getIdentity();
    }

    /**
     * @param null $class
     * @return void
     * @throws ForbiddenRequestException
     */
    public function render($class = null): void {
        if (!$this->isValid()) {
            throw new ForbiddenRequestException('No contests available.');
        }
        $this->template->contests = $this->getContests();
        $this->template->currentContest = $this->getContest() ? $this->getContest()->contest_id : null;
        $this->template->currentYear = $this->getYear();
        $this->template->class = ($class !== null) ? $class : "nav navbar-nav navbar-right";

        $this->template->setFile(__DIR__ . DIRECTORY_SEPARATOR . 'layout.contest.latte');
        $this->template->render();
    }

    /**
     * @param int $contestId
     * @throws AbortException
     */
    public function handleChange($contestId): void {
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
            $presenter->redirect('this', ['contestId' => $contestId, 'year' => $year]);
        } else {
            $presenter->redirect('this', ['contestId' => $contestId]);
        }
    }

    /**
     * @param int $contest
     * @param int $year
     * @throws AbortException
     */
    public function handleChangeYear($contest, $year): void {
        $presenter = $this->getPresenter();
        $presenter->redirect('this', [
            'contestId' => $contest, //WHY? contestId should be persistent
            'year' => $year]);
    }

    /**
     * @param Session|SessionSection $session TODO
     * @param ModelContest|null $contest
     * @param null $override
     * @return int|mixed|null
     */
    private function calculateYear($session, $contest, $override = null) {
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


        $allowedYears = $this->getYears($contest);
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
