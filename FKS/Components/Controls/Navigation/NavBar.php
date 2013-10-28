<?php

namespace FKS\Components\Controls\Navigation;

use Nette\Application\ForbiddenRequestException;
use Nette\Application\PresenterFactory;
use Nette\Application\UI\Control;
use Nette\Application\UI\InvalidLinkException;
use Nette\Application\UI\Presenter;
use Nette\InvalidArgumentException;
use ReflectionClass;
use ReflectionMethod;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 * 
 * @author Michal Koutný <michal@fykos.cz>
 */
class NavBar extends Control {

    private $nodes = array();
    private $nodeChildren = array();

    /**
     * @var PresenterFactory
     */
    private $presenterFactory;

    /**
     * @var array of Presenter
     */
    private $presenters = array();
    private $structure;

    function __construct(PresenterFactory $presenterFactory) {
        $this->presenterFactory = $presenterFactory;
    }

    public function getNode($nodeId) {
        return $this->nodes[$nodeId];
    }

    public function isActive($node) {
        $result = false;
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
            $presenter = $this->preparePresenter($node->linkPresenter, $node->linkAction, $node->linkParams);
            try {
                $presenter->checkRequirements($presenter->getReflection());
                return true;
            } catch (ForbiddenRequestException $e) {
                return false;
            }
        }

        return true;
    }

    public function getTitle($node) {
        if (isset($node->title)) {
            return $node->title;
        }
        if (isset($node->linkPresenter)) {
            $presenter = $this->preparePresenter($node->linkPresenter, $node->linkAction, $node->linkParams);
            return $presenter->getTitle();
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
        $node = (object) $arguments;
        $this->nodes[$nodeId] = $node;
    }

    public function addParent($idChild, $idParent) {
        if (!isset($this->nodeChildren)) {
            $this->nodeChildren[$idParent] = array();
        }
        $this->nodeChildren[$idParent][] = $idChild;
    }

    public function render() {
        $template = $this->getTemplate();
        $template->setFile(__DIR__ . DIRECTORY_SEPARATOR . 'NavBar.latte');

        $template->nodes = $this->structure;

        $template->render();
    }

    private function createLink($presenter, $node) {
        $linkedPresenter = $this->preparePresenter($node->linkPresenter, $node->linkAction, $node->linkParams);
        $method = $linkedPresenter->publicFormatActionMethod($node->linkAction);

        $linkParams = array();
        $rc = new ReflectionClass($linkedPresenter);
        if ($rc->hasMethod($method)) {
            $rm = new ReflectionMethod($linkedPresenter, $method);
            foreach ($rm->getParameters() as $param) {
                $name = $param->getName();
                $linkParams[$name] = $node->linkParams[$name];
            }
        }

        return $presenter->link(':' . $node->linkPresenter . ':' . $node->linkAction, $linkParams);
    }

    /**
     * 
     * @param string $presenterName
     * @return Presenter
     */
    private function getOuterPresener($presenterName) {
        if (!isset($this->presenters[$presenterName])) {
            $this->presenters[$presenterName] = $this->presenterFactory->createPresenter($presenterName);
        }
        return $this->presenters[$presenterName];
    }

    private function preparePresenter($presenter, $action, $providedParams) {
        $providedParams = $providedParams ? : array();
        $presenter = $this->getOuterPresener($presenter);
        if (!$presenter instanceof INavigablePresenter) {
            throw new InvalidArgumentException("Presenter must be instance of INavigablePresenter.");
        }

        $params = $this->getPresenter()->getParameter(); // by default inherit parameters of the calling presenter -- is this alright?
        unset($params[Presenter::ACTION_KEY]);
        foreach ($providedParams as $key => $value) {
            $params[$key] = $value;
        }
        $presenter->loadState($params);
        $presenter->changeAction($action);
        $presenter->setView($presenter->getView()); // to force update the title    

        return $presenter;
    }

}