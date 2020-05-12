<?php

namespace FKSDB\Components\Controls\Navigation;

use FKSDB\Components\Controls\BaseControl;
use FKSDB\Components\Controls\PresenterBuilder;
use FKSDB\Exceptions\BadTypeException;
use FKSDB\UI\PageTitle;
use Nette\Application\BadRequestException;
use Nette\Application\UI\InvalidLinkException;
use Nette\Application\UI\Presenter;
use Nette\DI\Container;
use ReflectionClass;
use ReflectionMethod;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
class Navigation extends BaseControl {
    /**
     * @var array
     */
    private $nodes = [];
    /**
     * @var array
     */
    private $nodeChildren = [];

    /**
     * @var PresenterBuilder
     */
    private $presenterBuilder;
    /**
     * @var array
     */
    private $structure;

    /**
     * Navigation constructor.
     * @param PresenterBuilder $presenterBuilder
     * @param Container $container
     */
    function __construct(PresenterBuilder $presenterBuilder, Container $container) {
        parent::__construct($container);
        $this->presenterBuilder = $presenterBuilder;
    }

    /**
     * @param $nodeId
     * @return mixed
     */
    public function getNode($nodeId) {
        return $this->nodes[$nodeId];
    }

    /**
     * @param $node
     * @return bool
     */
    public function isActive(\stdClass $node): bool {
        if (isset($node->linkPresenter)) {
            /**
             * @var \BasePresenter $presenter
             */
            $presenter = $this->getPresenter();
            try {
                $this->createLink($presenter, $node);
            } catch (\Exception $exception) {
                /* empty */
            }
            $result = $presenter->getLastCreatedRequestFlag("current");
        } else {
            $result = false;
        }

        if ($result) {
            return true;
        }
// try children
        if (!isset($this->nodeChildren[$node->nodeId])) {
            return false;
        }
        foreach ($this->nodeChildren[$node->nodeId] as $childId) {
            if ($this->isActive($this->nodes[$childId])) {
                return true;
            }
        }
        return false;
    }

    /**
     * @param \stdClass $node
     * @return bool
     * @throws BadRequestException
     * @throws \ReflectionException
     */
    public function isVisible(\stdClass $node): bool {
        if (isset($node->visible)) {
            return $node->visible;
        }

        if (isset($node->linkPresenter)) {
            return $this->isAllowed($this->getPresenter(), $node);
        }

        return true;
    }

    /**
     * @param $node
     * @return PageTitle
     * @throws BadRequestException
     */
    public function getTitle(\stdClass $node): PageTitle {
        if (isset($node->title)) {
            return new PageTitle($node->title, $node->icon);
        }
        if (isset($node->linkPresenter)) {
            $presenter = $this->preparePresenter($node->linkPresenter, $node->linkAction, $node->linkParams);
            $presenter->setView($presenter->getView()); // to force update the title

            return $presenter->getTitle();
        }
        return new PageTitle('');
    }

    /**
     * @param \stdClass $node
     * @return null|string
     * @throws BadRequestException
     * @throws InvalidLinkException
     * @throws \ReflectionException
     */
    public function getLink(\stdClass $node) {
        if (isset($node->link)) {
            return $node->link;
        }
        if (isset($node->linkPresenter)) {
            $presenter = $this->getPresenter();
            return $this->createLink($presenter, $node);
        }
        return null;
    }

    /**
     * @param $structure
     */
    public function setStructure($structure) {
        $this->structure = $structure;
    }

    /**
     * @param $nodeId
     * @param $arguments
     */
    public function createNode($nodeId, $arguments) {
        $node = (object)$arguments;
        $this->nodes[$nodeId] = $node;
    }

    /**
     * @param $idChild
     * @param $idParent
     */
    public function addParent($idChild, $idParent) {
        if (!isset($this->nodeChildren)) {
            $this->nodeChildren[$idParent] = [];
        }
        $this->nodeChildren[$idParent][] = $idChild;
    }

    /**
     * @param string $root
     */
    public function renderNavbar(string $root) {
        $this->template->setFile(__DIR__ . DIRECTORY_SEPARATOR . 'Navigation.navbar.latte');
        $this->renderFromRoot([$root => $this->structure[$root]]);
    }

    /**
     * @param string $root
     */
    public function render(string $root) {
        $this->template->setFile(__DIR__ . DIRECTORY_SEPARATOR . 'Navigation.latte');
        $this->renderFromRoot($this->structure[$root]);
    }

    /**
     * @param array $nodes
     */
    private function renderFromRoot(array $nodes) {
        $this->template->nodes = $nodes;
        $this->template->render();
    }

    /**
     * @param Presenter $presenter
     * @param \stdClass $node
     * @return string
     * @throws BadRequestException
     * @throws InvalidLinkException
     * @throws \ReflectionException
     */
    private function createLink(Presenter $presenter, \stdClass $node): string {
        $linkedPresenter = $this->preparePresenter($node->linkPresenter, $node->linkAction, $node->linkParams);
        $linkParams = $this->actionParams($linkedPresenter, $node->linkAction, $node->linkParams);

        return $presenter->link(':' . $node->linkPresenter . ':' . $node->linkAction, $linkParams);
    }

    /**
     * @param Presenter $presenter
     * @param \stdClass $node
     * @return bool
     * @throws BadRequestException
     * @throws \ReflectionException
     */
    private function isAllowed(Presenter $presenter, \stdClass $node): bool {
        $allowedPresenter = $this->preparePresenter($node->linkPresenter, $node->linkAction, $node->linkParams);
        $allowedParams = $this->actionParams($allowedPresenter, $node->linkAction, $node->linkParams);
        return $presenter->authorized(':' . $node->linkPresenter . ':' . $node->linkAction, $allowedParams);
    }

    /**
     * @param Presenter $presenter
     * @param $actionParams
     * @param $params
     * @return array
     * @throws \ReflectionException
     */
    private function actionParams(Presenter $presenter, $actionParams, $params): array {
        $method = $presenter->publicFormatActionMethod($actionParams);

        $actionParams = [];
        $rc = new ReflectionClass($presenter);
        if ($rc->hasMethod($method)) {
            $rm = new ReflectionMethod($presenter, $method);
            foreach ($rm->getParameters() as $param) {
                $name = $param->getName();
                $actionParams[$name] = $params[$name];
            }
        }
        return $actionParams;
    }

    /**
     * @param string $presenterName
     * @param string $action
     * @param $providedParams
     * @return Presenter|INavigablePresenter
     * @throws BadRequestException
     */
    public function preparePresenter(string $presenterName, string $action, $providedParams): Presenter {
        $ownPresenter = $this->getPresenter();
        $presenter = $this->presenterBuilder->preparePresenter($presenterName, $action, $providedParams, $ownPresenter->getParameters());
        if (!$presenter instanceof INavigablePresenter) {
            throw new BadTypeException(INavigablePresenter::class, $presenter);
        }
        return $presenter;
    }
}
