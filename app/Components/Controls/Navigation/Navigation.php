<?php

namespace FKSDB\Components\Controls\Navigation;

use FKSDB\Components\Controls\PresenterBuilder;
use Nette\Application\UI\Control;
use Nette\InvalidArgumentException;
use Nette\Templating\FileTemplate;
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

    function __construct(PresenterBuilder $presenterBuilder) {
        parent::__construct();
        $this->presenterBuilder = $presenterBuilder;
    }

    public function getNode($nodeId) {
        return $this->nodes[$nodeId];
    }

    public function isActive($node) {
        if (isset($node->linkPresenter)) {
            /**
             * @var $presenter \BasePresenter
             */
            $presenter = $this->getPresenter();
            try {
                $this->createLink($presenter, $node);
            } catch (\Exception $e) {
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

    public function isVisible(\stdClass $node) {
        if (isset($node->visible)) {
            return $node->visible;
        }

        if (isset($node->linkPresenter)) {
            /**
             * @var $presenter \BasePresenter
             */
            $presenter = $this->getPresenter();
            return $this->isAllowed($presenter, $node);
        }

        return true;
    }

    public function getTitle($node) {
        if (isset($node->title)) {
            return $node->title;
        }
        if (isset($node->linkPresenter)) {
            /**
             * @var $presenter \BasePresenter
             */
            $presenter = $this->preparePresenter($node->linkPresenter, $node->linkAction, $node->linkParams);
            $presenter->setView($presenter->getView()); // to force update the title

            return $presenter->getTitle();
        }
        return null;
    }

    public function getSubTitle($node) {
        if (isset($node->title)) {
            return $node->title;
        }
        if (isset($node->linkPresenter)) {
            /**
             * @var $presenter \BasePresenter
             */
            $presenter = $this->preparePresenter($node->linkPresenter, $node->linkAction, $node->linkParams);
            $presenter->setView($presenter->getView()); // to force update the title

            return $presenter->getSubtitle();
        }
        return null;
    }

    public function getIcon($node) {
        if (isset($node->icon)) {
            return $node->icon;
        }
        if (isset($node->linkPresenter)) {
            /**
             * @var $presenter \BasePresenter
             */
            $presenter = $this->preparePresenter($node->linkPresenter, $node->linkAction, $node->linkParams);
            $presenter->setView($presenter->getView()); // to force update the title

            return $presenter->getIcon();
        }
        return null;
    }


    public function getLink(\stdClass $node) {
        if (isset($node->link)) {
            return $node->link;
        }
        if (isset($node->linkPresenter)) {
            /**
             * @var $presenter \BasePresenter
             */
            $presenter = $this->getPresenter();
            return $this->createLink($presenter, $node);
        }
        return null;
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
            $this->nodeChildren[$idParent] = [];
        }
        $this->nodeChildren[$idParent][] = $idChild;
    }

    public function renderNavbar($root = null) {
        /**
         * @var $template FileTemplate
         */
        $template = $this->getTemplate();
        $template->setFile(__DIR__ . DIRECTORY_SEPARATOR . 'Navigation.navbar.latte');
        $this->renderFromRoot($template, $root, true);
    }

    public function render($root = null) {
        /**
         * @var $template FileTemplate
         */
        $template = $this->getTemplate();
        $template->setFile(__DIR__ . DIRECTORY_SEPARATOR . 'Navigation.latte');
        $this->renderFromRoot($template, $root, false);
    }

    private function renderFromRoot(FileTemplate $template, $root, $isNavbar = false) {
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

    private function createLink(\BasePresenter $presenter, \stdClass $node) {
        /**
         * @var $linkedPresenter \BasePresenter
         */
        $linkedPresenter = $this->preparePresenter($node->linkPresenter, $node->linkAction, $node->linkParams);
        $linkParams = $this->actionParams($linkedPresenter, $node->linkAction, $node->linkParams);

        return $presenter->link(':' . $node->linkPresenter . ':' . $node->linkAction, $linkParams);
    }

    private function isAllowed(\BasePresenter $presenter, \stdClass $node) {
        /**
         * @var $allowedPresenter \BasePresenter
         */
        $allowedPresenter = $this->preparePresenter($node->linkPresenter, $node->linkAction, $node->linkParams);
        $allowedParams = $this->actionParams($allowedPresenter, $node->linkAction, $node->linkParams);
        return $presenter->authorized(':' . $node->linkPresenter . ':' . $node->linkAction, $allowedParams);
    }

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
