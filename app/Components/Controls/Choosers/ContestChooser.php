<?php

namespace FKSDB\Components\Controls\Choosers;

use FKSDB\ORM\Models\ModelContest;
use FKSDB\ORM\Models\ModelLogin;
use FKSDB\ORM\Services\ServiceContest;
use Nette\DI\Container;
use Nette\Http\Session;
use PublicModule\BasePresenter;

/**
 * Class ContestChooser
 * @package FKSDB\Components\Controls\Choosers
 */
abstract class ContestChooser extends Chooser {
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
     * @var ModelContest
     */
    protected $contest;
    /**
     * @var Session
     */
    protected $session;

    /**
     * ContestChooser constructor.
     * @param Container $container
     */
    public function __construct(Container $container) {
        parent::__construct($container);
        $this->session = $container->getByType(Session::class);
        $this->serviceContest = $container->getByType(ServiceContest::class);
    }

    /**
     * @param object $params
     * @return bool
     */
    abstract function syncRedirect(&$params);


    /**
     * @return ModelLogin
     */
    protected function getLogin(): ModelLogin {
        /**@var \OrgModule\BasePresenter|BasePresenter $presenter */
        $presenter = $this->getPresenter();
        /**@var ModelLogin $model */
        $model = $presenter->getUser()->getIdentity();
        return $model;
    }

    /**
     * @param object $params
     * @return void
     */
    abstract protected function init($params);
}
