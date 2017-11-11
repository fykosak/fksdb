<?php

namespace FKSDB\Components\Controls\Choosers;

use Authorization\Grant;
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
class DispatchChooser extends Chooser {

    const SOURCE_SESSION = 0x1;
    const SOURCE_URL = 0x2;
    const CONTEST_SOURCE = 0xffffffff;

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

        if ($this->initialized) {
            return;
        }
        $this->initialized = true;

        if (!$params->role) {
            throw  new BadRequestException('Role is empty');
        }
        $this->role = $params->role;

        $session = $this->session->getSection(self::SESSION_PREFIX);
        if (isset($session->contestId)) {
            $this->contest = $this->serviceContest->findByPrimary($session->contestId);
        }

        if ($params->contestId) {
            $this->contest = $this->serviceContest->findByPrimary($params->contestId);
        }
        if (is_null($this->contest)) {
            $contests = $this->getContests();
            if (count($contests) === 0) {
                return;
            }
            $this->contest = array_shift($contests);
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
        $contestIds = array_map(function ($contest) {
            return $contest['contest']->contest_id;
        }, $this->getContests());

        if (!in_array($this->contest->contest_id, $contestIds)) {
            throw  new BadRequestException('Tento seminár pre danú rolu niej dostupný', 403);
        }
    }

    /**
     * @return \ModelContest[]
     */
    private function getContests() {
        $login = $this->getLogin();
        if (!$login) {
            return [];
        }
        $result = [];
        $roles = $login->getRoles(\ModelLogin::NO_ACL_ROLES);
        /**
         * @var $role Grant
         */
        foreach ($roles as $role) {
            $result[] = [
                'contest' => $this->serviceContest->findByPrimary($role->getContestId()),
                'role' => $role->getRoleId(),
            ];
        }
        return $result;
    }

    public function render() {
        $this->template->availableCombinations = $this->getContests();
        $this->template->currentContest = $this->getContest();
        $this->template->setFile(__DIR__ . DIRECTORY_SEPARATOR . 'DispatchChooser.latte');
        $this->template->render();
    }

    public function handleChange($contestId, $role) {
        $presenter = $this->getPresenter();
        if ($this->role === $role) {
            $presenter->redirect('this', ['contestId' => $contestId, 'year' => -1,]);
        }
        switch ($role) {
            case ModelRole::CONTESTANT:
                $presenter->redirect(':Public:Dashboard:default', ['contestId' => $contestId, 'year' => -1,]);
                break;
            case ModelRole::ORG:
                $presenter->redirect(':Org:Dashboard:default', ['contestId' => $contestId, 'year' => -1,]);
                break;
        }


    }
}
