<?php

namespace FKSDB\Components\Controls\Breadcrumbs;

use FKSDB\Components\Controls\BaseComponent;
use FKSDB\Components\Controls\Breadcrumbs\Request as NaviRequest;
use FKSDB\Components\Controls\Navigation\NavigablePresenter;
use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Models\Utils\Utils;
use Nette\Application\IPresenterFactory;
use Nette\Application\Request as AppRequest;
use Nette\Application\UI\ComponentReflection;
use Nette\Application\UI\Presenter;
use Nette\DI\Container;
use Nette\Http\Request as HttpRequest;
use Nette\Http\Session;
use Nette\Http\SessionSection;
use Nette\InvalidArgumentException;
use Nette\Routing\Router;
use Nette\Utils\Random;
use Tracy\Debugger;

/**
 * Monitors user's traversal through the web and build the tree,
 * from which navigation path is then displayed.
 *
 * Only actions/views that render this control are taken into account
 * (this is ensured via call in the beforeRender method).
 *
 * @note Page titles of visited pages are cached in the session.
 */
class BreadcrumbsComponent extends BaseComponent {

    public const SECTION_REQUESTS = self::class . '.main';
    public const SECTION_BACKIDS = self::class . '.backids';
    public const SECTION_REVERSE = self::class . '.reverse';
    public const SECTION_PATH_REVERSE = self::class . '.pathReverse';
    // const EXPIRATION = '+ 10 minutes';
    public const BACKID_LEN = 4;
    public const BACKID_DOMAIN = '0-9a-zA-Z';

    private Session $session;
    private Router $router;
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
     */
    public function __construct(Container $container) {
        parent::__construct($container);
        $expiration = $container->getParameters()['navigation']['expiration'];
        $this->getRequestsSection()->setExpiration($expiration);
        $this->getPathKeyCacheSection()->setExpiration($expiration);
        $this->getBackLinkMapSection()->setExpiration($expiration);
        $this->getReverseBackLinkMapSection()->setExpiration($expiration);
    }

    final public function injectPrimary(Session $session, Router $router, HttpRequest $httpRequest, IPresenterFactory $presenterFactory): void {
        $this->session = $session;
        $this->router = $router;
        $this->httpRequest = $httpRequest;
        $this->presenterFactory = $presenterFactory;
    }

    /*     * **********************
     * Public API
     * ********************** */

    /**
     * @throws \ReflectionException
     * @throws BadTypeException
     */
    public function setBackLink(AppRequest $request): void {
        $presenter = $this->getPresenter();
        if (!$presenter instanceof NavigablePresenter) {
            throw new BadTypeException(NavigablePresenter::class, $presenter);
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

    /* ***********************
     * Rendering
     * ********************** */

    final public function render(): void {
        $request = $this->getPresenter()->getRequest();

        $path = [];
        foreach ($this->getTraversePath($request) as $naviRequest) {
            $url = $this->router->constructUrl((array)$naviRequest->request, $this->httpRequest->getUrl());
            $path[] = (object)[
                'url' => $url,
                'title' => $naviRequest->title,
            ];
        }
        $this->template->path = $path;
        $this->template->render(__DIR__ . DIRECTORY_SEPARATOR . 'layout.breadcrumbs.latte');
    }

    /*     * **********************
     * Path traversal
     * ********************** */

    public function getBackLinkUrl(): ?string {
        // backLink is actually the second, as first is the current request
        $path = $this->getTraversePath($this->getPresenter()->getRequest(), 2);

        if (count($path) > 1) {
            $naviRequest = $path[0];
            $appRequest = $naviRequest->request;

            // workaround to keep reference to flash session
            if ($this->getPresenter()->hasFlashSession()) {
                $appRequest = clone $appRequest;
                $params = $appRequest->getParameters();
                $params[Presenter::FLASH_KEY] = $this->getPresenter()->getParameter(Presenter::FLASH_KEY);
                $appRequest->setParameters($params);
            }
            return $this->router->constructUrl((array)$appRequest, $this->httpRequest->getUrl());
        } else {
            return null;
        }
    }

    /**
     * @return NaviRequest[]
     */
    private function getTraversePath(AppRequest $request, ?int $maxLen = null): array {
        $requests = $this->getRequestsSection();
        $backLinkMap = $this->getBackLinkMapSection();

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
            $requestKey = $backLinkId ? $backLinkMap[$backLinkId] ?? null : null; // assumes null key is not in backIds
            $naviRequest = $requestKey ? $requests[$requestKey] ?? null : null; // assumes null key is not in requests
        } while ($naviRequest && (!$maxLen || (count($path) < $maxLen)));

        return [];//array_reverse($path);
    }

    /**
     *
     * @param AppRequest|string|NaviRequest $request
     * @throws \ReflectionException
     */
    private function getPathKey($request): string {
        if ($request instanceof AppRequest) {
            $parameters = $request->getParameters();
            $presenterName = $request->getPresenterName();
            /** @var Presenter $presenterClassName */
            $presenterClassName = $this->presenterFactory->formatPresenterClass($presenterName);
            $action = $parameters[Presenter::ACTION_KEY];
            $methodName = ($presenterClassName)::publicFormatActionMethod($action);
            $identifyingParameters = [Presenter::ACTION_KEY];
            $rc = ($presenterClassName)::getReflection();
            if ($rc->hasMethod($methodName)) {
                $rm = $rc->getMethod($methodName);
                foreach ($rm->getParameters() as $param) {
                    $identifyingParameters[] = $param->name;
                }
            }
            $reflection = new ComponentReflection($presenterClassName);
            $identifyingParameters += array_keys($reflection->getPersistentParams());

            $filteredParameters = [];
            $backLinkParameter = ($presenterClassName)::getBackLinkParamName();
            foreach ($identifyingParameters as $param) {
                if ($param == $backLinkParameter) {
                    continue; // this parameter can be persistent but never is identifying!
                }
                $filteredParameters[$param] = $parameters[$param] ?? null;
            }

            $paramKey = Utils::getFingerprint($filteredParameters);
            return $presenterName . ':' . $paramKey;
        } elseif ($request instanceof NaviRequest) {
            return $request->pathKey;
        } elseif (is_string($request)) { // caching + recursion
            $pathKeyCache = $this->getPathKeyCacheSection();
            $requests = $this->getRequestsSection();
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
     * @throws \ReflectionException
     * @throws BadTypeException
     */
    private function storeRequest(?string $backLink): void {
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

        $requests = $this->getRequestsSection();
        $requestKey = $this->getRequestKey($request);
        $requests[$requestKey] = $naviRequest;
    }

    /**
     * @param NavigablePresenter|Presenter $presenter
     * @throws BadTypeException
     * @throws \ReflectionException
     */
    protected function createNaviRequest(Presenter $presenter, AppRequest $request, ?string $backLink): NaviRequest {
        $pathKey = $this->getPathKey($request);
        if (!$presenter instanceof NavigablePresenter) {
            throw new BadTypeException(NavigablePresenter::class, $presenter);
        }
        return new NaviRequest($presenter->getUser()->getId(), $request, $presenter->getTitle(), $backLink, $pathKey);
    }

    protected function getRequestKey(AppRequest $request): string {
        $presenterName = $request->getPresenterName();
        $parameters = $this->filterParameters($request->getParameters());
        $paramKey = Utils::getFingerprint($parameters);
        return $presenterName . ':' . $paramKey;
    }

    private function getBackLinkId(string $requestKey): string {
        $reverseBackLinkMap = $this->getReverseBackLinkMapSection();

        if (isset($reverseBackLinkMap[$requestKey])) {
            return $reverseBackLinkMap[$requestKey];
        }

        $backLinkMap = $this->getBackLinkMapSection();

        do {
            $backLinkId = Random::generate(self::BACKID_LEN, self::BACKID_DOMAIN);
        } while (isset($backLinkMap[$backLinkId]));

        $backLinkMap[$backLinkId] = $requestKey;
        $reverseBackLinkMap[$requestKey] = $backLinkId;
        return $backLinkId;
    }

    /**
     * Filter only parameters relevant to identify the request.
     */
    protected function filterParameters(iterable $parameters): array {
        $result = [];
        foreach ($parameters as $key => $value) {
            if ($key == Presenter::FLASH_KEY) {
                continue;
            }
            $result[$key] = $value;
        }
        return $result;
    }

    /* ***********************
     * Cache stored in session  *
     * ********************** */

    protected function getRequestsSection(): SessionSection {
        return $this->session->getSection(self::SECTION_REQUESTS);
    }

    protected function getPathKeyCacheSection(): SessionSection {
        return $this->session->getSection(self::SECTION_PATH_REVERSE);
    }

    protected function getBackLinkMapSection(): SessionSection {
        return $this->session->getSection(self::SECTION_BACKIDS);
    }

    protected function getReverseBackLinkMapSection(): SessionSection {
        return $this->session->getSection(self::SECTION_REVERSE);
    }

}
