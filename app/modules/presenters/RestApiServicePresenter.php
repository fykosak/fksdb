<?php

use Nette\Application\Responses\JsonResponse;
use Tracy\Debugger;

/**
 * Class RestApiServicePresenter
 * @package FKSDB
 */
class RestApiServicePresenter extends AuthenticatedPresenter {
    /**
     * @return null|void
     */
    protected function unauthorizedAccess() {
        return null;
    }

    /**
     * @return bool|int|string
     */
    public function getAllowedAuthMethods() {
        return parent::getAllowedAuthMethods() | self::AUTH_ALLOW_HTTP;
    }

    /**
     * @return bool
     */
    public function requiresLogin() {
        return false;
    }

    /**
     *
     */
    public function renderDefault() {
        Debugger::barDump($this->getRequest());
        $response = new JsonResponse([]);
        // $this->sendResponse($response);
    }

    /**
     *
     */
    public function handleFyziklani() {
        $response = new JsonResponse([]);
        //$this->sendResponse($response);
    }
}
