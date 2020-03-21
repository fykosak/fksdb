<?php

namespace FKSDB\Components\Events;

use Events\Machine\BaseMachine;
use FKSDB\Application\IJavaScriptCollector;
use Nette\Application\UI\Control;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
class GraphComponent extends Control {

    /**
     * @var BaseMachine
     */
    private $baseMachine;
    private $expressionPrinter;

    /**
     * GraphComponent constructor.
     * @param BaseMachine $baseMachine
     * @param ExpressionPrinter $expressionPrinter
     */
    function __construct(BaseMachine $baseMachine, ExpressionPrinter $expressionPrinter) {
        parent::__construct();
        $this->monitor(IJavaScriptCollector::class);
        $this->baseMachine = $baseMachine;
        $this->expressionPrinter = $expressionPrinter;
    }

    private $attachedJS = false;

    /**
     * @param $obj
     */
    protected function attached($obj) {
        parent::attached($obj);
        if (!$this->attachedJS && $obj instanceof IJavaScriptCollector) {
            $this->attachedJS = true;
            $obj->registerJSFile('js/graph/raphael.js');
            $obj->registerJSFile('js/graph/dracula_graffle.js');
            $obj->registerJSFile('js/graph/dracula_graph.js');
        }
    }

    /**
     * @param null $class
     * @return \Nette\Templating\ITemplate
     */
    protected function createTemplate($class = NULL) {
        $template = parent::createTemplate($class);
        $template->setTranslator($this->presenter->getTranslator());
        return $template;
    }

    public function renderCanvas() {
        $this->template->setFile(__DIR__ . DIRECTORY_SEPARATOR . 'GraphComponent.canvas.latte');
        $this->renderTemplate();
    }

    public function renderScript() {
        $this->template->setFile(__DIR__ . DIRECTORY_SEPARATOR . 'GraphComponent.script.latte');
        $this->renderTemplate();
    }

    private function renderTemplate() {
        $this->template->machine = $this->baseMachine;
        $this->template->states = array_merge(array_keys($this->baseMachine->getStates()), array(BaseMachine::STATE_INIT, BaseMachine::STATE_TERMINATED));
        $this->template->id = $this->getHtmlId();
        $this->template->printer = $this->expressionPrinter;
        $this->template->render();
    }

    /**
     * @return string
     */
    private function getHtmlId() {
        return 'graph-' . $this->getUniqueId();
    }

}

