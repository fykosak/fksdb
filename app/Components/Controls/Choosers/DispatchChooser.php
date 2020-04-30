<?php

namespace FKSDB\Components\Controls\Choosers;

use Authorization\Grant;
use FKSDB\ORM\Models\ModelContest;
use FKSDB\ORM\Models\ModelRole;
use FKSDB\UI\Title;
use FKSDB\YearCalculator;
use Nette\Application\AbortException;
use Nette\Application\BadRequestException;
use Nette\Application\UI\InvalidLinkException;
use Nette\DI\Container;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 *
 * @author Michal Koutný <michal@fykos.cz>
 * @author Michal Červeňák <miso@fykos.cz>
 */
class DispatchChooser extends ContestChooser {

    /**
     * @var YearCalculator
     */
    protected $yearCalculator;

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
     * @return boolean
     * Redirect to correct address according to the resolved values.
     * @throws BadRequestException
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

    /**
     * @return ModelContest
     */
    public function getContest() {
        return $this->contest;
    }

    /**
     * @param object $params
     * @throws BadRequestException
     */
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
            $this->contest = array_shift($contests)['contest'];
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
     * @return array[]
     */
    private function getContests() {
        $login = $this->getLogin();
        if (!$login) {
            return [];
        }
        $result = [];
        $roles = $login->getRoles();
        /**@var Grant $role */
        foreach ($roles as $role) {
            $result[] = [
                'contest' => $this->serviceContest->findByPrimary($role->getContestId()),
                'role' => $role->getRoleId(),
            ];
        }
        return $result;
    }

    /**
     * @param $contestId
     * @param $role
     * @throws AbortException
     */
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

    /**
     * @return Title
     */
    protected function getTitle(): Title {
        return new Title($this->getContest() ? $this->getContest()->name : _('Contest'));
    }

    /**
     * @return array[]
     */
    protected function getItems() {
        return $this->getContests();
    }

    /**
     * @param $item
     * @return bool
     */
    public function isItemActive($item): bool {
        return false;
    }

    /**
     * @param $item
     * @return string
     */
    public function getItemLabel($item): string {
        return $item['contest']->name . ' - ' . $item['role'] === 'org' ? _('Org') : _('Contestant');
    }

    /**
     * @param $item
     * @return string
     * @throws InvalidLinkException
     */
    public function getItemLink($item): string {
        return $this->link('change!', [$item['contest']->contest_id, $item['role']]);
    }
}
