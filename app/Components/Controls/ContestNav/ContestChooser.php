<?php

namespace FKSDB\Components\Controls\ContestNav;

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
    const CONTEST_SOURCE = 0xffffffff;

    /**
     * @var integer[]
     */
    private $contestsDefinition;

    /**
     * @var object[]
     */
    private $contests;

    /**
     * @var YearCalculator
     */
    protected $yearCalculator;

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
     * @param integer[] $contestsDefinition
     */
    public function setContests($contestsDefinition) {
        $this->contestsDefinition = $contestsDefinition;
    }

    /**
     * @param $params object
     * @return boolean
     * Redirect to correct address according to the resolved values.
     */
    public function syncRedirect(&$params) {
        $this->init($params);
        $contestId = isset($this->contest) ? $this->contest->contest_id : null;
        /** fix empty presenter contest  */
        if (is_null($params->contestId)) {
            return false;
        }
        if ($contestId !== $params->contestId) {
            $params->contestId = $contestId;
            return true;
        }
        return false;
    }

    public function getContest() {
        return $this->contest;
    }

    protected function init($params) {
        //Debugger::barDump($this->contest);
        if ($this->initialized) {
            return;
        }
        $this->initialized = true;

        if (!$this->role) {
            throw  new BadRequestException('Role is empty');
        }

        $session = $this->session->getSection(self::SESSION_PREFIX);
        if ((self::CONTEST_SOURCE & self::SOURCE_SESSION) && isset($session->contestId)) {
            $this->contest = $this->serviceContest->findByPrimary($session->contestId);
        }

        if ((self::CONTEST_SOURCE & self::SOURCE_URL) && $params->contestId) {
            $this->contest = $this->serviceContest->findByPrimary($params->contestId);
        }

        //Debugger::barDump($this->contest);
        if (is_null($this->contest)) {
            $contestIds = $this->getContests();
            if (count($contestIds) === 0) {
                return;
            }
            $this->contest = array_shift($contestIds);
        }

        if ($this->contest !== null) {
            $session->contestId = $this->contest->contest_id;
        }
    }

    /**
     * @return \ModelContest[]
     */
    private function getContests() {
        if ($this->contests === null) {
            if (is_array($this->contestsDefinition)) {
                $contests = $this->contestsDefinition;
            } else {
                $contests = $this->getContestsByRole();
            }
            $this->contests = $contests;
        }
        return $this->contests;
    }

    /**
     * @return \ModelContest[]
     */
    private function getContestsByRole() {
        $login = $this->getLogin();
        if (!$login) {
            return [];
        }
        switch ($this->role) {
            case ModelRole::ORG:
                $result = [];
                foreach ($login->getActiveOrgsContests($this->yearCalculator) as $contestId => $org) {
                    $result[] = $this->serviceContest->findByPrimary($contestId);
                };
                return $result;
            case ModelRole::CONTESTANT:
                $person = $login->getPerson();
                if ($person) {
                    return $person->getActiveContestants($this->yearCalculator);
                }
        }
        return [];
    }

    public function render() {
        $this->template->availableContests = $this->getContests();
        $this->template->currentContest = $this->getContest();
        $this->template->setFile(__DIR__ . DIRECTORY_SEPARATOR . 'ContestChooser.latte');
        $this->template->render();
    }

    public function handleChange($contestId) {
        $presenter = $this->getPresenter();
        $presenter->redirect('this', ['contestId' => $contestId, 'year' => -1,]);
    }
}
