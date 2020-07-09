<?php

namespace FKSDB\Components\Controls\Navigation;

use FKSDB\Components\Controls\BaseComponent;
use FKSDB\Components\Controls\PresenterBuilder;
use FKSDB\Exceptions\BadTypeException;
use FKSDB\Modules\Core\BasePresenter;
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
class Navigation extends BaseComponent {
    /**
     * @var array[]
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
    public function __construct(PresenterBuilder $presenterBuilder, Container $container) {
        parent::__construct($container);
        $this->presenterBuilder = $presenterBuilder;
    }

    /**
     * @param string|int $nodeId
     * @return array
     */
    public function getNode(string $nodeId) {
        return $this->nodes[$nodeId];
    }

    public function isActive(array $node): bool {
        if (isset($node['linkPresenter'])) {
            /**
             * @var BasePresenter $presenter
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
        if (!isset($this->nodeChildren[$node['nodeId']])) {
            return false;
        }
        foreach ($this->nodeChildren[$node['nodeId']] as $childId) {
            if ($this->isActive($this->nodes[$childId])) {
                return true;
            }
        }
        return false;
    }

    /**
     * @param array $node
     * @return bool
     * @throws BadTypeException
     * @throws \ReflectionException
     * @throws BadRequestException
     */
    public function isVisible(array $node): bool {
        if (isset($node['visible'])) {
            return $node['visible'];
        }

        if (isset($node['linkPresenter'])) {
            return $this->isAllowed($this->getPresenter(), $node);
        }

        return true;
    }

    /**
     * @param array $node
     * @return PageTitle
     *
     *
     * @throws BadRequestException
     * @throws BadTypeException
     */
    public function getTitle(array $node): PageTitle {
        if (isset($node['title'])) {
            return new PageTitle($node['title'], $node['icon']);
        }
        if (isset($node['linkPresenter'])) {
            $presenter = $this->preparePresenter($node['linkPresenter'], $node['linkAction'], $node['linkParams']);
            $presenter->setView($presenter->getView()); // to force update the title

            return $presenter->getTitle();
        }
        return new PageTitle('');
    }

    /**
     * @param array $node
     * @return null|string
     * @throws BadTypeException
     * @throws InvalidLinkException
     * @throws \ReflectionException
     * @throws BadRequestException
     */
    public function getLink(array $node) {
        if (isset($node['link'])) {
            return $node['link'];
        }
        if (isset($node['linkPresenter'])) {
            return $this->createLink($this->getPresenter(), $node);
        }
        return null;
    }

    /**
     * @param array $structure
     * @return void
     */
    public function setStructure(array $structure) {
        $this->structure = $structure;
    }

    /**
     * @param string $nodeId
     * @param array $arguments
     * @return void
     */
    public function createNode(string $nodeId, array $arguments) {
        $this->nodes[$nodeId] = $arguments;
    }

    /**
     * @param string|int $idChild
     * @param string|int $idParent
     * @return void
     */
    public function addParent($idChild, $idParent) {
        if (!isset($this->nodeChildren)) {
            $this->nodeChildren[$idParent] = [];
        }
        $this->nodeChildren[$idParent][] = $idChild;
    }

    /**
     * @param string $root
     * @return void
     */
    public function renderNavbar(string $root) {
        $this->template->setFile(__DIR__ . DIRECTORY_SEPARATOR . 'Navigation.navbar.latte');
        $this->renderFromRoot([$root => $this->structure[$root]]);
    }

    /**
     * @param string $root
     * @return void
     */
    public function render(string $root) {
        $this->template->setFile(__DIR__ . DIRECTORY_SEPARATOR . 'Navigation.latte');
        $this->renderFromRoot($this->structure[$root]);
    }

    /**
     * @param array $nodes
     * @return void
     */
    private function renderFromRoot(array $nodes) {
        $this->template->nodes = $nodes;
        $this->template->render();
    }

    /**
     * @param Presenter $presenter
     * @param array $node
     * @return string
     * @throws BadRequestException
     * @throws BadTypeException
     * @throws InvalidLinkException
     * @throws \ReflectionException
     */
    private function createLink(Presenter $presenter, array $node): string {
        $linkedPresenter = $this->preparePresenter($node['linkPresenter'], $node['linkAction'], $node['linkParams']);
        $linkParams = $this->actionParams($linkedPresenter, $node['linkAction'], $node['linkParams']);

        return $presenter->link(':' . $node['linkPresenter'] . ':' . $node['linkAction'], $linkParams);
    }

    /**
     * @param Presenter $presenter
     * @param array $node
     * @return bool
     * @throws BadRequestException
     * @throws BadTypeException
     * @throws \ReflectionException
     */
    private function isAllowed(Presenter $presenter, array $node): bool {
        $allowedPresenter = $this->preparePresenter($node['linkPresenter'], $node['linkAction'], $node['linkParams']);
        $allowedParams = $this->actionParams($allowedPresenter, $node['linkAction'], $node['linkParams']);
        return $presenter->authorized(':' . $node['linkPresenter'] . ':' . $node['linkAction'], $allowedParams);
    }

    /**
     * @param Presenter $presenter
     * @param string $actionParams
     * @param array $params
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
     * @param string $providedParams
     * @return Presenter|INavigablePresenter
     *
     *
     * @throws BadRequestException
     * @throws BadTypeException
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
