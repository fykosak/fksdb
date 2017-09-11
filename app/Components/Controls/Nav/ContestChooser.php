<?php

namespace FKSDB\Components\Controls\Nav;

use ModelRole;
use Nette\Application\BadRequestException;
use Nette\Http\Session;
use OrgModule\BasePresenter;
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
     * @param integer[] $contestsDefinition
     */
    public function setContests($contestsDefinition) {
        $this->contestsDefinition = $contestsDefinition;
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
         * @var $presenter BasePresenter|\PublicModule\BasePresenter
         */
        $presenter = $this->getPresenter();
        $contestId = isset($this->contest) ? $this->contest->contest_id : null;
        /** fix empty presenter contest  */
        if (is_null($presenter->contestId)) {
            return null;
        }
        if ($contestId != $presenter->contestId) {
            return $contestId;
        }
        return null;
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

        if (!$this->role) {
            throw  new BadRequestException('Role is empty');
        }

        $contestIds = $this->getContests();
        if (count($contestIds) === 0) {
            $this->valid = false;
            return;
        }

        $this->valid = true;

        $session = $this->session->getSection(self::SESSION_PREFIX);
        /**
         * @var $presenter BasePresenter|\PublicModule\BasePresenter
         */
        $presenter = $this->getPresenter();

        /* CONTEST */

        $contestId = null;
        // session TODO
        if ((self::CONTEST_SOURCE & self::SOURCE_SESSION) && isset($session->contestId)) {
            $contestId = $session->contestId;
        }
        // URL TODO
        if ((self::CONTEST_SOURCE & self::SOURCE_URL) && $presenter->contestId) {
            $contestId = $presenter->contestId;
        }

        $this->contest = $this->serviceContest->findByPrimary($contestId);

        if (is_null($this->contest)) {
            $this->contest = array_shift($contestIds)->contest;
        }

        if ($this->contest === null) {
            throw new BadRequestException('Undefined contest');
        } else {
            // remember
            $session->contestId = $this->contest->contest_id;
        }
    }

    /**
     * @return object[]
     */
    private function getContests() {
        if ($this->contests === null) {
            if (is_array($this->contestsDefinition)) {
                $contests = $this->contestsDefinition;
            } else {
                $contests = $this->getContestsByRole();
            }
            $this->fillContests($contests);
        }
        return $this->contests;
    }

    protected function fillContests($contests) {
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

    /**
     * @return integer[]
     */
    private function getContestsByRole() {
        $login = $this->getLogin();
        if (!$login) {
            return [];
        }
        switch ($this->role) {
            case ModelRole::ORG:
                return array_keys($login->getActiveOrgsContests($this->yearCalculator));
            case ModelRole::CONTESTANT:
                $person = $login->getPerson();
                if ($person) {
                    return array_keys($person->getActiveContestants($this->yearCalculator));
                }
        }
        return [];
    }

    public function render() {
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
