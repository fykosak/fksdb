<?php
/**
 * @author Michal Červeňák <miso@fykos.cz>
 */

namespace FKSDB\Components\Controls\Choosers;

use Nette\Application\UI\Control;
use ModelContest;
use Nette\Http\Session;

use ServiceContest;

abstract class Chooser extends Control {

    const SESSION_PREFIX = 'contestPreset';

    /**
     * @var ServiceContest
     */
    protected $serviceContest;
    /**
     * @var boolean
     */
    protected $initialized = false;

    /**
     * @var string
     */
    protected $role = null;

    /**
     * @param $role string
     */
    public function setRole($role) {
        $this->role = $role;
    }

    /**
     * @var ModelContest
     */
    protected $contest;
    /**
     * @var Session
     */
    protected $session;

    public function __construct(Session $session, ServiceContest $serviceContest) {
        parent::__construct();
        $this->session = $session;
        $this->serviceContest = $serviceContest;
    }

    /**
     * @param $params object
     * @return bool
     */
    abstract function syncRedirect(&$params);


    /**
     * @return \ModelLogin
     */
    protected function getLogin() {
        /**
         * @var $presenter \OrgModule\BasePresenter|\PublicModule\BasePresenter
         */
        $presenter = $this->getPresenter();
        /**
         * @var $model \ModelLogin
         */
        $model = $presenter->getUser()->getIdentity();
        return $model;
    }

    /**
     * @param $params object
     * @return void
     */
    abstract protected function init($params);
}
