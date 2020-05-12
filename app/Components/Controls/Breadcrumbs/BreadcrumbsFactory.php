<?php

namespace FKSDB\Components\Controls\Breadcrumbs;

use Nette\Application\IPresenterFactory;
use Nette\Application\IRouter;
use Nette\Http\Request;
use Nette\Http\Session;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
class BreadcrumbsFactory {

    /** @var Session */
    private $session;

    /**
     * @var IRouter
     */
    private $router;

    /**
     * @var Request
     */
    private $httpRequest;

    /**
     * @var IPresenterFactory
     */
    private $presenterFactory;

    /**
     * @var string
     */
    private $expiration;

    /**
     * BreadcrumbsFactory constructor.
     * @param $expiration
     * @param Session $session
     * @param IRouter $router
     * @param Request $httpRequest
     * @param IPresenterFactory $presenterFactory
     */
    function __construct($expiration, Session $session, IRouter $router, Request $httpRequest, IPresenterFactory $presenterFactory) {
        $this->expiration = $expiration;
        $this->session = $session;
        $this->router = $router;
        $this->httpRequest = $httpRequest;
        $this->presenterFactory = $presenterFactory;
    }

    /**
     *
     * @return Breadcrumbs
     */
    public function create(): Breadcrumbs {
        return new Breadcrumbs($this->expiration, $this->session, $this->router, $this->httpRequest, $this->presenterFactory);
    }

}
