<?php

namespace FKS\Components\Controls\Navigation;

use FKS\Components\Controls\PresenterBuilder;
use Nette\Application\UI\Control;
use Nette\Application\UI\InvalidLinkException;
use Nette\Diagnostics\Debugger;
use Nette\InvalidArgumentException;
use ReflectionClass;
use ReflectionMethod;


/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
class Navigation extends Control {

    private $nodes = array();
    private $nodeChildren = array();

    /**
     * @var PresenterBuilder
     */
    private $presenterBuilder;
    private $structure;

    function __construct(PresenterBuilder $presenterBuilder) {
        $this->presenterBuilder = $presenterBuilder;
    }

    public function getNode($nodeId) {
        return $this->nodes[$nodeId];
    }

    public function isActive($node) {
        if (isset($node->linkPresenter)) {
            $presenter = $this->getPresenter();
            try {
                $this->createLink($presenter, $node);
            } catch (InvalidLinkException $e) {
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

    public function isVisible($node) {
        if (isset($node->visible)) {
            return $node->visible;
        }

        if (isset($node->linkPresenter)) {
            return $this->isAllowed($this->getPresenter(), $node);
        }

        return true;
    }

    public function getTitle($node) {
        if (isset($node->title)) {
            return $node->title;
        }
        if (isset($node->linkPresenter)) {
            $presenter = $this->preparePresenter($node->linkPresenter, $node->linkAction, $node->linkParams);
            $presenter->setView($presenter->getView()); // to force update the title
            return $presenter->getTitle();
        }
    }

    public function getIcon($node) {
        if (isset($node->icon)) {
            return $node->icon;
        }
        if (isset($node->linkPresenter)) {
            $presenter = $this->preparePresenter($node->linkPresenter, $node->linkAction, $node->linkParams);
            $presenter->setView($presenter->getView());
            return $presenter->getIcon();
        }
    }

    public function getLink($node) {
        if (isset($node->link)) {
            return $node->link;
        }
        if (isset($node->linkPresenter)) {
            return $this->createLink($this->getPresenter(), $node);
        }
    }

    public function setStructure($structure) {
        $this->structure = $structure;
    }

    public function createNode($nodeId, $arguments) {
        $node = (object)$arguments;
        $this->nodes[$nodeId] = $node;
    }

    public function addParent($idChild, $idParent) {
        if (!isset($this->nodeChildren)) {
            $this->nodeChildren[$idParent] = array();
        }
        $this->nodeChildren[$idParent][] = $idChild;
    }

    public function renderNavbar($root = null) {
        //Debugger::barDump($root);
        $template = $this->getTemplate();
        $template->setFile(__DIR__ . DIRECTORY_SEPARATOR . 'Navigation.navbar.latte');
        $this->renderFromRoot($template, $root ?: '', true);
    }

    public function render($root = null) {
        $template = $this->getTemplate();
        $template->setFile(__DIR__ . DIRECTORY_SEPARATOR . 'Navigation.latte');
        $this->renderFromRoot($template, $root);
    }

    private function renderFromRoot($template, $root, $navbar = false) {
        if (!is_null($root)) {
            if ($root) {
                $template->nodes = $navbar ? [$root => $this->structure[$root]] : $this->structure[$root];
            } else {
                $template->nodes = [];
            }

        } else {
            $template->nodes = $this->structure;
        }
        $template->render();
    }

    private function createLink($presenter, $node) {
        $linkedPresenter = $this->preparePresenter($node->linkPresenter, $node->linkAction, $node->linkParams);
        $linkParams = $this->actionParams($linkedPresenter, $node->linkAction, $node->linkParams);

        return $presenter->link(':' . $node->linkPresenter . ':' . $node->linkAction, $linkParams);
    }

    private function isAllowed($presenter, $node) {
        $allowedPresenter = $this->preparePresenter($node->linkPresenter, $node->linkAction, $node->linkParams);
        $allowedParams = $this->actionParams($allowedPresenter, $node->linkAction, $node->linkParams);

        return $presenter->authorized(':' . $node->linkPresenter . ':' . $node->linkAction, $allowedParams);
    }

    private function actionParams($presenter, $actionParams, $params) {
        $method = $presenter->publicFormatActionMethod($actionParams);

        $actionParams = array();
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

    public function preparePresenter($presenterName, $action, $providedParams) {
        $ownPresenter = $this->getPresenter();
        $presenter = $this->presenterBuilder->preparePresenter($presenterName, $action, $providedParams, $ownPresenter->getParameter());
        if (!$presenter instanceof INavigablePresenter) {
            $class = get_class($presenter);
            throw new InvalidArgumentException("Presenter must be instance of INavigablePresenter, $class given.");
        }
        return $presenter;
    }

}