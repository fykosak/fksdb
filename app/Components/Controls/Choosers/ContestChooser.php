<?php

namespace FKSDB\Components\Controls\Choosers;

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
class ContestChooser extends Chooser {

    const SOURCE_SESSION = 0x1;
    const SOURCE_URL = 0x2;
    const CONTEST_SOURCE = 0xffffffff;

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
     * @param $params object
     * @return boolean
     * Redirect to correct address according to the resolved values.
     */
    public function syncRedirect(&$params) {
        $this->init($params);
        $contestId = isset($this->contest) ? $this->contest->contest_id : null;

        /** fix empty presenter contest  */
        /* if (is_null($params->contestId)) {
             return false;
         }*/
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
        if (is_null($this->contest)) {
            $contests = $this->getContests();
            if (count($contests) === 0) {
                return;
            }
            $this->contest = array_shift($contestIds);
        }

        if ($this->contest !== null) {
            $session->contestId = $this->contest->contest_id;
        }
        $this->authorize();

    }

    /**
     * Fix rewrite URL
     * @throws BadRequestException
     *
     */
    private function authorize() {
        $contestIds = array_map(function (\ModelContest $contest) {
            return $contest->contest_id;
        }, $this->getContests());

        if (!in_array($this->contest->contest_id, $contestIds)) {
            throw  new BadRequestException('Tento seminár pre danú rolu niej dostupný', 403);
        }
    }

    /**
     * @return \ModelContest[]
     */
    private function getContests() {
        if ($this->contests === null) {
            $this->contests = $this->getContestsByRole();
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
                    $result = [];
                    foreach ($person->getActiveContestants($this->yearCalculator) as $contestId => $org) {
                        $result[] = $this->serviceContest->findByPrimary($contestId);
                    }

                    return $result;
                }
        }
        return [];
    }

    public function render() {
        $this->template->availableContests = $this->getContests();
        $this->template->currentContest = $this->getContest();
        $this->template->setFile(__DIR__ . DIRECTORY_SEPARATOR.'ContestChooser.latte');
        $this->template->render();
    }

    public function handleChange($contestId) {
        $presenter = $this->getPresenter();
        $presenter->redirect('this', ['contestId' => $contestId, 'year' => -1,]);
    }
}
