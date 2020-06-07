<?php

namespace FKSDB\Components\Controls;

use Nette\Application\UI\Control;
use Nette\Bridges\ApplicationLatte\Template;
use Nette\DI\Container;
use Nette\Localization\ITranslator;

/**
 * Class BaseComponent
 * @author Michal Červeňák <miso@fykos.cz>
 * @property Template $template
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
     * @return \Nette\Application\UI\ITemplate
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
