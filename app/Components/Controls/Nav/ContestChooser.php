<?php

namespace FKSDB\Components\Controls\Nav;

use ModelContest;
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
class ContestChooser extends Nav {

    const SOURCE_SESSION = 0x1;
    const SOURCE_URL = 0x2;
    const SESSION_PREFIX = 'contestPreset';
    const CONTESTS_ALL = '__*';
    const YEARS_ALL = '__*';
    /** @obsolete (no first contest anymore) */
    const DEFAULT_FIRST = 'first';
    const DEFAULT_SMART_FIRST = 'smfirst';
    const DEFAULT_NULL = 'null';

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
     * @var ServiceContest
     */
    private $serviceContest;

    /**
     * @var ModelContest
     */
    private $contest;

    /**
     * @var boolean
     */
    private $valid;
    private $initialized = false;

    /**
     * @var int bitmask of what "sources" are used to infer selected contest
     */
    private $contestSource = 0xffffffff;
    /**
     * @var YearCalculator
     */
    private $yearCalculator;


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
     * @param mixed $contestsDefinition role enum|CONTESTS_ALL|array of contests
     */
    public function setContests($contestsDefinition) {
        $this->contestsDefinition = $contestsDefinition;
    }

    public function getContestSource() {
        return $this->contestSource;
    }

    public function setContestSource($contestSource) {
        $this->contestSource = $contestSource;
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

        $presenter = $this->getPresenter();

        $contestId = isset($this->contest) ? $this->contest->contest_id : null;
        if ($contestId != $presenter->contestId) {
            $presenter->redirect('this', [
                'contestId' => $contestId,
            ]);
        }
    }

    public function getContest() {
        $this->init();
        return $this->contest;
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
        if (($this->contestSource & self::SOURCE_SESSION) && isset($session->contestId)) {
            $contestId = $session->contestId;
        }
        // URL
        if (($this->contestSource & self::SOURCE_URL) && $presenter->contestId) {
            $contestId = $presenter->contestId;
        }

        $this->contest = $this->serviceContest->findByPrimary($contestId);

        if ($this->contest === null) {
            throw new BadRequestException('Undefined contest');
        } else {
            // remember
            $session->contestId = $this->contest->contest_id;
        }
    }

    /**
     * @return array of contests where user is either ORG or CONTESTANT
     */
    private function getContests() {
        Debugger::barDump($this);
        if ($this->contests === null) {
            if (is_array($this->contestsDefinition)) { // explicit
                $contests = array_map(function ($contest) {
                    return ($contest instanceof ModelContest) ? $contest->contest_id : $contest;
                }, $this->contestsDefinition);
            } else if ($this->contestsDefinition === self::CONTESTS_ALL) { // all
                $pk = $this->serviceContest->getPrimary();
                $contests = $this->serviceContest->fetchPairs($pk, $pk);
            } else { // implicity -- by role
                $contests = [];
                $login = $this->getLogin();
                Debugger::barDump($login);
                if ($login) {
                    switch ($this->role) {
                        case ModelRole::ORG:
                            $contests = array_keys($login->getActiveOrgsContests($this->yearCalculator));
                            break;
                        case ModelRole::CONTESTANT:
                            $person = $login->getPerson();
                            if ($person) {
                                $contests = array_keys($person->getActiveContestants($this->yearCalculator));
                            }
                            break;
                    }
                }
            }
            $this->contests = [];

            foreach ($contests as $id) {
                $class = '';
                switch ($id) {
                    case 1:
                        $class = 'fykos';
                        break;
                    case 2:
                        $class = 'vyfuk';
                        break;
                }
                $contest = $this->serviceContest->findByPrimary($id);
                $this->contests[$id] = (object)[
                    'contest' => $contest,
                    'symbol' => $class,
                ];
            }
        }
        return $this->contests;
    }

    public function render($class = null) {
        if (!$this->isValid()) {
            throw new BadRequestException('No contests available.', 403);
        }
        $this->template->availableContests = $this->getContests();
        $this->template->currentContest = $this->getContest();
        $class = '';
        $this->template->class = $class;

        $this->template->setFile(__DIR__ . DIRECTORY_SEPARATOR . 'ContestChooser.latte');
        $this->template->render();
    }

    public function handleChange($contestId) {
        $presenter = $this->getPresenter();
        $presenter->redirect('this', ['contestId' => $contestId, 'year' => null,]);

    }
}
