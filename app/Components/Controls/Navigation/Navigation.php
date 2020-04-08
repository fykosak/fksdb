<?php

namespace FKSDB\Components\Controls\Navigation;

use FKSDB\Components\Controls\PresenterBuilder;
use Nette\Application\BadRequestException;
use Nette\Application\UI\Control;
use Nette\Application\UI\InvalidLinkException;
use Nette\Application\UI\ITemplate;
use Nette\Application\UI\Presenter;
use Nette\InvalidArgumentException;
use ReflectionClass;
use ReflectionMethod;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
class Navigation extends Control {

    private $nodes = [];
    private $nodeChildren = [];

    /**
     * @var PresenterBuilder
     */
    private $presenterBuilder;
    private $structure;

    /**
     * Navigation constructor.
     * @param PresenterBuilder $presenterBuilder
     */
    function __construct(PresenterBuilder $presenterBuilder) {
        parent::__construct();
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
    public function isActive($node) {
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
     * @return bool|mixed
     * @throws BadRequestException
     * @throws InvalidLinkException
     * @throws \ReflectionException
     */
    public function isVisible(\stdClass $node) {
        if (isset($node->visible)) {
            return $node->visible;
        }

        if (isset($node->linkPresenter)) {
            /**
             * @var \BasePresenter $presenter
             */
            $presenter = $this->getPresenter();
            return $this->isAllowed($presenter, $node);
        }

        return true;
    }

    /**
     * @param $node
     * @return array
     * @throws BadRequestException
     */
    public function getTitle($node) {
        if (isset($node->title)) {
            return $node->title;
        }
        if (isset($node->linkPresenter)) {
            /**
             * @var \BasePresenter $presenter
             */
            $presenter = $this->preparePresenter($node->linkPresenter, $node->linkAction, $node->linkParams);
            $presenter->setView($presenter->getView()); // to force update the title

            return $presenter->getTitle();
        }
        return [];
    }

    /**
     * @param $node
     * @return null|string
     * @throws BadRequestException
     */
    public function getSubTitle($node) {
        if (isset($node->title)) {
            return $node->title;
        }
        if (isset($node->linkPresenter)) {
            /**
             * @var \BasePresenter $presenter
             */
            $presenter = $this->preparePresenter($node->linkPresenter, $node->linkAction, $node->linkParams);
            $presenter->setView($presenter->getView()); // to force update the title

            return $presenter->getSubtitle();
        }
        return null;
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
            /**
             * @var \BasePresenter $presenter
             */
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
     * @param null $root
     */
    public function renderNavbar($root = null) {
        $template = $this->getTemplate();
        $template->setFile(__DIR__ . DIRECTORY_SEPARATOR . 'Navigation.navbar.latte');
        $this->renderFromRoot($template, $root, true);
    }

    /**
     * @param null $root
     */
    public function render($root = null) {
        $template = $this->getTemplate();
        $template->setFile(__DIR__ . DIRECTORY_SEPARATOR . 'Navigation.latte');
        $this->renderFromRoot($template, $root, false);
    }

    /**
     * @param ITemplate $template
     * @param $root
     * @param bool $isNavbar
     */
    private function renderFromRoot(ITemplate $template, $root, $isNavbar = false) {
        if (!is_null($root)) {
            if ($root) {
                $template->nodes = $isNavbar ? [$root => $this->structure[$root]] : $this->structure[$root];
            } else {
                $template->nodes = [];
            }
        } else {
            $template->nodes = $this->structure;
        }
        $template->render();
    }

    /**
     * @param \BasePresenter $presenter
     * @param \stdClass $node
     * @return string
     * @throws BadRequestException
     * @throws InvalidLinkException
     * @throws \ReflectionException
     */
    private function createLink(\BasePresenter $presenter, \stdClass $node) {
        /**
         * @var \BasePresenter $linkedPresenter
         */
        $linkedPresenter = $this->preparePresenter($node->linkPresenter, $node->linkAction, $node->linkParams);
        $linkParams = $this->actionParams($linkedPresenter, $node->linkAction, $node->linkParams);

        return $presenter->link(':' . $node->linkPresenter . ':' . $node->linkAction, $linkParams);
    }

    /**
     * @param \BasePresenter $presenter
     * @param \stdClass $node
     * @return mixed
     * @throws BadRequestException
     * @throws InvalidLinkException
     * @throws \ReflectionException
     */
    private function isAllowed(\BasePresenter $presenter, \stdClass $node) {
        /**
         * @var \BasePresenter $allowedPresenter
         */
        $allowedPresenter = $this->preparePresenter($node->linkPresenter, $node->linkAction, $node->linkParams);
        $allowedParams = $this->actionParams($allowedPresenter, $node->linkAction, $node->linkParams);
        return $presenter->authorized(':' . $node->linkPresenter . ':' . $node->linkAction, $allowedParams);
    }

    /**
     * @param \BasePresenter $presenter
     * @param $actionParams
     * @param $params
     * @return array
     * @throws \ReflectionException
     */
    private function actionParams(\BasePresenter $presenter, $actionParams, $params) {
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
     * @param $presenterName
     * @param $action
     * @param $providedParams
     * @return Presenter
     * @throws BadRequestException
     */
    public function preparePresenter($presenterName, $action, $providedParams) {
        $ownPresenter = $this->getPresenter();
        $presenter = $this->presenterBuilder->preparePresenter($presenterName, $action, $providedParams, $ownPresenter->getParameters());
        if (!$presenter instanceof INavigablePresenter) {
            $class = get_class($presenter);
            throw new InvalidArgumentException("Presenter must be instance of INavigablePresenter, $class given.");
        }
        return $presenter;
    }

}
