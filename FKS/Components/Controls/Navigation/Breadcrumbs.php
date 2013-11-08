<?php

namespace FKS\Components\Controls\Navigation;

use FKS\Components\Controls\Navigation\Request as NaviRequest;
use Nette\Application\IRouter;
use Nette\Application\PresenterFactory;
use Nette\Application\Request as AppRequest;
use Nette\Application\UI\Control;
use Nette\Application\UI\Presenter;
use Nette\Application\UI\PresenterComponentReflection;
use Nette\Diagnostics\Debugger;
use Nette\Http\Request as HttpRequest;
use Nette\Http\Session;
use Nette\InvalidArgumentException;
use Nette\InvalidStateException;
use Nette\Utils\Strings;
use Utils;

/**
 * Monitors user's traversal through the web and build the tree,
 * from which navigation path is then displayed.
 * 
 * Only actions/views that render this control are taken into account
 * (this is ensured via call in the beforeRender method).
 * 
 * @note Page titles of visited pages are cached in the session.
 * 
 * @author Michal Koutný <michal@fykos.cz>
 */
class Breadcrumbs extends Control {

    const SECTION_REQUESTS = 'FKS\Components\Controls\Navigation\Breadcrumbs.main';
    const SECTION_BACKIDS = 'FKS\Components\Controls\Navigation\Breadcrumbs.backids';
    const SECTION_REVERSE = 'FKS\Components\Controls\Navigation\Breadcrumbs.reverse';
    const SECTION_PATH_REVERSE = 'FKS\Components\Controls\Navigation\Breadcrumbs.pathReverse';
    const EXPIRATION = '+ 10 minutes';
    const BACKID_LEN = 4;
    const BACKID_DOMAIN = '0-9a-zA-Z';

    /** @var Session */
    private $session;

    /**
     * @var IRouter
     */
    private $router;

    /**
     * @var HttpRequest
     */
    private $httpRequest;

    /**
     * @var PresenterFactory
     */
    private $presenterFactory;

    /**
     * Prevents multiple storing the current request.
     * 
     * @var bool
     */
    private $storedRequest = false;

    function __construct($expiration, Session $session, IRouter $router, HttpRequest $httpRequest, PresenterFactory $presenterFactory) {
        $this->session = $session;
        $this->router = $router;
        $this->httpRequest = $httpRequest;
        $this->presenterFactory = $presenterFactory;

        $this->getRequests()->setExpiration($expiration);
        $this->getPathKeyCache()->setExpiration($expiration);
        $this->getBacklinkMap()->setExpiration($expiration);
        $this->getReverseBacklinkMap()->setExpiration($expiration);
    }

    /*     * **********************
     * Public API
     * ********************** */

    public function setBacklink(AppRequest $request) {
        $presenter = $this->getPresenter();
        if (!$presenter instanceof INavigablePresenter) {
            $class = get_class($presenter);
            throw new InvalidStateException("Expected presenter of INavigablePresenter type, got '$class'.");
        }

        $requestKey = $this->getRequestKey($request);
        $backlinkId = $this->getBacklinkId($requestKey);
        $originalBacklink = $presenter->setBacklink($backlinkId);
        $this->storeRequest($originalBacklink);
    }

    public function reset() {
        foreach (array(
    self::SECTION_BACKIDS,
    self::SECTION_REQUESTS,
    self::SECTION_REVERSE,
    self::SECTION_PATH_REVERSE,
        ) as $sectionName) {
            $this->session->getSection($sectionName)->remove();
        }
    }

    /*     * **********************
     * Rendering
     * ********************** */

    public function render() {
        $request = $this->getPresenter()->getRequest();

        $path = array();
        foreach ($this->getTraversePath($request) as $naviRequest) {
            $url = $this->router->constructUrl($naviRequest->request, $this->httpRequest->getUrl());
            $path[] = (object) array(
                        'url' => $url,
                        'title' => $naviRequest->title,
            );
        }

        $template = $this->getTemplate();
        $template->setFile(__DIR__ . DIRECTORY_SEPARATOR . 'Breadcrumbs.latte');
        $template->path = $path;
        $template->render();
        //$this->reset();
    }

    /*     * **********************
     * Path traversal
     * ********************** */

    public function getBacklinkUrl() {
        $presenter = $this->getPresenter();
        $request = $presenter->getRequest();

        // backlink is actually the second, as first is the current request
        $path = $this->getTraversePath($request, 2);

        if (count($path) > 1) {
            $naviRequest = $path[0];
            $appRequest = $naviRequest->request;
            
            // workaround to keep reference to flash session
            if ($presenter->hasFlashSession()) {
                $appRequest = clone $appRequest;
                $params = $appRequest->getParameters();
                $params[Presenter::FLASH_KEY] = $presenter->getParameter(Presenter::FLASH_KEY);
                $appRequest->setParameters($params);
            }
            return $this->router->constructUrl($appRequest, $this->httpRequest->getUrl());
        } else {
            return null;
        }
    }

    private function getTraversePath(AppRequest $request, $maxLen = null) {
        $requests = $this->getRequests();
        $backlinkMap = $this->getBacklinkMap();

        $requestKey = $this->getRequestKey($request);

        if (!isset($requests[$requestKey])) {
            return array();
        }
        $naviRequest = $requests[$requestKey];

        $prevPathKey = null;
        $path = array();
        $userId = $this->getPresenter()->getUser()->getId();

        do {
            if ($naviRequest->user != $userId) {
                break;
            }
            $pathKey = $naviRequest->pathKey;
            if ($prevPathKey != $pathKey) {
                $path[] = $naviRequest;
            }
            $prevPathKey = $pathKey;

            // get parent from the traversal tree (backlinkId -> request)
            $backlinkId = $naviRequest->parent;
            $requestKey = isset($backlinkMap[$backlinkId]) ? $backlinkMap[$backlinkId] : null; // assumes null key is not in backIds
            $naviRequest = isset($requests[$requestKey]) ? $requests[$requestKey] : null; // assumes null key is not in requests
        } while ($naviRequest && (!$maxLen || (count($path) < $maxLen)));

        return array_reverse($path);
    }

    /**
     * 
     * @param AppRequest|string $request
     * @return string
     */
    private function getPathKey($request) {
        if ($request instanceof AppRequest) {
            $parameters = $request->getParameters();
            $presenterName = $request->getPresenterName();
            $presenterClassName = $this->presenterFactory->formatPresenterClass($presenterName);
            $action = $parameters[Presenter::ACTION_KEY];
            $methodName = call_user_func("$presenterClassName::publicFormatActionMethod", $action);
            $identifyingParameters = array(Presenter::ACTION_KEY);

            $rc = call_user_func("$presenterClassName::getReflection");
            if ($rc->hasMethod($methodName)) {
                $rm = $rc->getMethod($methodName);
                foreach ($rm->getParameters() as $param) {
                    $identifyingParameters[] = $param->name;
                }
            }
            $reflection = new PresenterComponentReflection($presenterClassName);
            $identifyingParameters += array_keys($reflection->getPersistentParams());

            $filteredParameters = array();
            $backlinkParameter = call_user_func("$presenterClassName::getBacklinkParamName");
            foreach ($identifyingParameters as $param) {
                if ($param == $backlinkParameter) {
                    continue; // this parameter can be persistent but never is identifying!
                }
                $filteredParameters[$param] = isset($parameters[$param]) ? $parameters[$param] : null;
            }

            $paramKey = Utils::getFingerprint($filteredParameters);
            $key = $presenterName . ':' . $paramKey;
            return $key;
        } else if ($request instanceof NaviRequest) {
            return $request->pathKey;
        } else if (is_string($request)) { // caching + recursion            
            $pathKeyCache = $this->getPathKeyCache();
            $requests = $this->getRequests();
            $requestKey = $request;
            if (!isset($pathKeyCache[$requestKey])) {
                $request = $requests[$requestKey]->request;
                $pathKeyCache[$requestKey] = $this->getPathKey($request);
            }
            return $pathKeyCache[$requestKey];
        } else {
            $class = is_object($request) ? get_class($request) : 'scalar';
            throw new InvalidArgumentException("Expected Request, NaviRequest class or string, got $class.");
        }
    }

    /*     * **********************
     * Storing requests and their IDs
     * ********************** */

    private function storeRequest($backlink) {
        if ($this->storedRequest) {
            return;
        }
        $this->storedRequest = true;

        $presenter = $this->getPresenter();
        $request = $presenter->getRequest();

        if ($request->getMethod() == 'post') {
            Debugger::log('Attempt to store POST request into breadcrumbs.', Debugger::WARNING);
            return;
        }

        $naviRequest = $this->createNaviRequest($presenter, $request, $backlink);

        $requests = $this->getRequests();
        $requestKey = $this->getRequestKey($request);
        $requests[$requestKey] = $naviRequest;
    }

    protected function createNaviRequest(INavigablePresenter $presenter, AppRequest $request, $backlink) {
        $pathKey = $this->getPathKey($request);
        return new NaviRequest($presenter->getUser()->getId(), $request, $presenter->getTitle(), $backlink, $pathKey);
    }

    protected function getRequestKey(AppRequest $request) {
        $presenterName = $request->getPresenterName();
        $parameters = $this->filterParameters($request->getParameters());
        $paramKey = Utils::getFingerprint($parameters);
        $key = $presenterName . ':' . $paramKey;
        return $key;
    }

    private function getBacklinkId($requestKey) {
        $reverseBacklinkMap = $this->getReverseBacklinkMap();

        if (isset($reverseBacklinkMap[$requestKey])) {
            return $reverseBacklinkMap[$requestKey];
        }

        $backlinkMap = $this->getBacklinkMap();

        do {
            $backlinkId = Strings::random(self::BACKID_LEN, self::BACKID_DOMAIN);
        } while (isset($backlinkMap[$backlinkId]));

        $backlinkMap[$backlinkId] = $requestKey;
        $reverseBacklinkMap[$requestKey] = $backlinkId;
        return $backlinkId;
    }

    /**
     * Filter only parameters relevant to identify the request.
     * 
     * @param array $parameters
     * @return array
     */
    protected function filterParameters($parameters) {
        $result = array();
        foreach ($parameters as $key => $value) {
            if ($key == Presenter::FLASH_KEY) {
                continue;
            }
            $result[$key] = $value;
        }
        return $result;
    }

    /*     * **********************
     * Cache stored in session    * 
     * ********************** */

    /**
     * @return array[requestKey] => NaviRequest
     */
    protected function getRequests() {
        return $this->session->getSection(self::SECTION_REQUESTS);
    }

    /**
     * @return array[requestKey] => pathKey
     */
    protected function getPathKeyCache() {
        return $this->session->getSection(self::SECTION_PATH_REVERSE);
    }

    /**
     * @return array[backlingId] => requestKey
     */
    protected function getBacklinkMap() {
        return $this->session->getSection(self::SECTION_BACKIDS);
    }

    /**
     * @return array[requestKey] => backlinkId
     */
    protected function getReverseBacklinkMap() {
        return $this->session->getSection(self::SECTION_REVERSE);
    }

}
