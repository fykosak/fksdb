<?php

namespace FKSDB\Components\Controls\Breadcrumbs;

use FKSDB\Components\Controls\BaseComponent;
use FKSDB\Components\Controls\Breadcrumbs\Request as NaviRequest;
use FKSDB\Components\Controls\Navigation\INavigablePresenter;
use FKSDB\Exceptions\BadTypeException;
use Nette\Application\IPresenterFactory;
use Nette\Application\IRouter;
use Nette\Application\Request as AppRequest;
use Nette\Application\UI\Presenter;
use Nette\Application\UI\ComponentReflection;
use Nette\DI\Container;
use Tracy\Debugger;
use Nette\Http\Request as HttpRequest;
use Nette\Http\Session;
use Nette\Http\SessionSection;
use Nette\InvalidArgumentException;
use Nette\Utils\Random;
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
class Breadcrumbs extends BaseComponent {

    public const SECTION_REQUESTS = 'FKSDB\Components\Controls\Breadcrumbs\Breadcrumbs.main';
    public const SECTION_BACKIDS = 'FKSDB\Components\Controls\Breadcrumbs\Breadcrumbs.backids';
    public const SECTION_REVERSE = 'FKSDB\Components\Controls\Breadcrumbs\Breadcrumbs.reverse';
    public const SECTION_PATH_REVERSE = 'FKSDB\Components\Controls\Breadcrumbs\Breadcrumbs.pathReverse';
    // const EXPIRATION = '+ 10 minutes';
    public const BACKID_LEN = 4;
    public const BACKID_DOMAIN = '0-9a-zA-Z';

    private Session $session;

    private IRouter $router;

    private HttpRequest $httpRequest;

    private IPresenterFactory $presenterFactory;

    /**
     * Prevents multiple storing the current request.
     *
     * @var bool
     */
    private bool $storedRequest = false;

    /**
     * Breadcrumbs constructor.
     * @param $expiration
     * @param Container $container
     */
    public function __construct(string $expiration, Container $container) {
        parent::__construct($container);
        $this->getRequests()->setExpiration($expiration);
        $this->getPathKeyCache()->setExpiration($expiration);
        $this->getBackLinkMap()->setExpiration($expiration);
        $this->getReverseBackLinkMap()->setExpiration($expiration);
    }

    public function injectPrimary(Session $session, IRouter $router, HttpRequest $httpRequest, IPresenterFactory $presenterFactory): void {
        $this->session = $session;
        $this->router = $router;
        $this->httpRequest = $httpRequest;
        $this->presenterFactory = $presenterFactory;
    }

    /*     * **********************
     * Public API
     * ********************** */

    /**
     * @param AppRequest $request
     * @throws \ReflectionException
     * @throws BadTypeException
     */
    public function setBackLink(AppRequest $request): void {
        $presenter = $this->getPresenter();
        if (!$presenter instanceof INavigablePresenter) {
            throw new BadTypeException(INavigablePresenter::class, $presenter);
        }

        $requestKey = $this->getRequestKey($request);
        $backLinkId = $this->getBackLinkId($requestKey);
        $originalBackLink = $presenter->setBackLink($backLinkId);
        $this->storeRequest($originalBackLink);
    }

    public function reset(): void {
        foreach ([
                     self::SECTION_BACKIDS,
                     self::SECTION_REQUESTS,
                     self::SECTION_REVERSE,
                     self::SECTION_PATH_REVERSE,
                 ] as $sectionName) {
            $this->session->getSection($sectionName)->remove();
        }
    }

    /*     * **********************
     * Rendering
     * ********************** */

    public function render(): void {
        $request = $this->getPresenter()->getRequest();

        $path = [];
        foreach ($this->getTraversePath($request) as $naviRequest) {
            $url = $this->router->constructUrl($naviRequest->request, $this->httpRequest->getUrl());
            $path[] = (object)[
                'url' => $url,
                'title' => $naviRequest->title,
            ];
        }
        $this->template->setFile(__DIR__ . DIRECTORY_SEPARATOR . 'Breadcrumbs.latte');
        $this->template->path = $path;
        $this->template->render();
    }

    /*     * **********************
     * Path traversal
     * ********************** */

    public function getBackLinkUrl(): ?string {
        $presenter = $this->getPresenter();
        $request = $presenter->getRequest();

        // backLink is actually the second, as first is the current request
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

    /**
     * @param AppRequest $request
     * @param null $maxLen
     * @return NaviRequest[]
     */
    private function getTraversePath(AppRequest $request, $maxLen = null) {
        $requests = $this->getRequests();
        $backLinkMap = $this->getBackLinkMap();

        $requestKey = $this->getRequestKey($request);

        if (!isset($requests[$requestKey])) {
            return [];
        }
        /** @var NaviRequest $naviRequest */
        $naviRequest = $requests[$requestKey];

        $prevPathKey = null;
        $path = [];
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

            // get parent from the traversal tree (backLinkId -> request)
            $backLinkId = $naviRequest->parent;
            $requestKey = isset($backLinkMap[$backLinkId]) ? $backLinkMap[$backLinkId] : null; // assumes null key is not in backIds
            $naviRequest = isset($requests[$requestKey]) ? $requests[$requestKey] : null; // assumes null key is not in requests
        } while ($naviRequest && (!$maxLen || (count($path) < $maxLen)));

        return array_reverse($path);
    }

    /**
     *
     * @param AppRequest|string $request
     * @return string
     * @throws \ReflectionException
     */
    private function getPathKey($request) {
        if ($request instanceof AppRequest) {
            $parameters = $request->getParameters();
            $presenterName = $request->getPresenterName();
            $presenterClassName = $this->presenterFactory->formatPresenterClass($presenterName);
            $action = $parameters[Presenter::ACTION_KEY];
            $methodName = call_user_func("$presenterClassName::publicFormatActionMethod", $action);
            $identifyingParameters = [Presenter::ACTION_KEY];
            /** @var \ReflectionClass $rc */
            $rc = call_user_func("$presenterClassName::getReflection");
            if ($rc->hasMethod($methodName)) {
                $rm = $rc->getMethod($methodName);
                foreach ($rm->getParameters() as $param) {
                    $identifyingParameters[] = $param->name;
                }
            }
            $reflection = new ComponentReflection($presenterClassName);
            $identifyingParameters += array_keys($reflection->getPersistentParams());

            $filteredParameters = [];
            $backLinkParameter = call_user_func("$presenterClassName::getBackLinkParamName");
            foreach ($identifyingParameters as $param) {
                if ($param == $backLinkParameter) {
                    continue; // this parameter can be persistent but never is identifying!
                }
                $filteredParameters[$param] = isset($parameters[$param]) ? $parameters[$param] : null;
            }

            $paramKey = Utils::getFingerprint($filteredParameters);
            return $presenterName . ':' . $paramKey;
        } elseif ($request instanceof NaviRequest) {
            return $request->pathKey;
        } elseif (is_string($request)) { // caching + recursion
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

    /**
     * @param $backLink
     * @throws \ReflectionException
     * @throws BadTypeException
     */
    private function storeRequest($backLink) {
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

        $naviRequest = $this->createNaviRequest($presenter, $request, $backLink);

        $requests = $this->getRequests();
        $requestKey = $this->getRequestKey($request);
        $requests[$requestKey] = $naviRequest;
    }

    /**
     * @param INavigablePresenter|Presenter $presenter
     * @param AppRequest $request
     * @param $backLink
     * @return Request
     * @throws \ReflectionException
     * @throws BadTypeException
     */
    protected function createNaviRequest(Presenter $presenter, AppRequest $request, $backLink): NaviRequest {
        $pathKey = $this->getPathKey($request);
        if (!$presenter instanceof INavigablePresenter) {
            throw new BadTypeException(INavigablePresenter::class, $presenter);
        }
        return new NaviRequest($presenter->getUser()->getId(), $request, $presenter->getTitle(), $backLink, $pathKey);
    }

    protected function getRequestKey(AppRequest $request): string {
        $presenterName = $request->getPresenterName();
        $parameters = $this->filterParameters($request->getParameters());
        $paramKey = Utils::getFingerprint($parameters);
        return $presenterName . ':' . $paramKey;
    }

    /**
     * @param $requestKey
     * @return string
     */
    private function getBackLinkId($requestKey) {
        $reverseBackLinkMap = $this->getReverseBackLinkMap();

        if (isset($reverseBackLinkMap[$requestKey])) {
            return $reverseBackLinkMap[$requestKey];
        }

        $backLinkMap = $this->getBackLinkMap();

        do {
            $backLinkId = Random::generate(self::BACKID_LEN, self::BACKID_DOMAIN);
        } while (isset($backLinkMap[$backLinkId]));

        $backLinkMap[$backLinkId] = $requestKey;
        $reverseBackLinkMap[$requestKey] = $backLinkId;
        return $backLinkId;
    }

    /**
     * Filter only parameters relevant to identify the request.
     *
     * @param array $parameters
     * @return array
     */
    protected function filterParameters($parameters): array {
        $result = [];
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

    protected function getRequests(): SessionSection {
        return $this->session->getSection(self::SECTION_REQUESTS);
    }

    protected function getPathKeyCache(): SessionSection {
        return $this->session->getSection(self::SECTION_PATH_REVERSE);
    }

    protected function getBackLinkMap(): SessionSection {
        return $this->session->getSection(self::SECTION_BACKIDS);
    }

    protected function getReverseBackLinkMap(): SessionSection {
        return $this->session->getSection(self::SECTION_REVERSE);
    }

}
