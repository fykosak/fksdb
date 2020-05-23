<?php

namespace FKSDB\Components\Controls;

use Nette\Application\UI\Control;
use Nette\DI\Container;
use Nette\Localization\ITranslator;
use Nette\Templating\FileTemplate;
use Nette\Templating\ITemplate;

/**
 * Class BaseControl
 * @package FKSDB\Components\Controls
 * @property FileTemplate $template
 */
abstract class BaseComponent extends Control {
    /**
     * @var Container
     */
    private $context;
    /**
     * @var ITranslator
     */
    private $translator;

    /**
     * SubmitsTableControl constructor.
     * @param Container $container
     */
    public function __construct(Container $container) {
        parent::__construct();
        $container->callInjects($this);
        $this->context = $container;
    }

    /**
     * @param ITranslator $translator
     * @return void
     */
    public function injectTranslator(ITranslator $translator) {
        $this->translator = $translator;
    }

    /**
     * @param null $class
     * @return ITemplate
     */
    protected function createTemplate($class = NULL) {
        $template = parent::createTemplate($class);
        $template->setTranslator($this->translator);
        return $template;
    }

    /**
     * @return Container
     */
    final protected function getContext() {
        return $this->context;
    }
}
