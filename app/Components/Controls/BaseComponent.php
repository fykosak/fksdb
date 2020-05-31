<?php

namespace FKSDB\Components\Controls;

use Nette\Application\UI\Control;
use Nette\DI\Container;
use Nette\Localization\ITranslator;
use Nette\Templating\FileTemplate;
use Nette\Templating\ITemplate;

/**
 * Class BaseComponent
 * @author Michal Červeňák <miso@fykos.cz>
 * @property FileTemplate $template
 */
abstract class BaseComponent extends Control {

    private Container $context;

    private ITranslator $translator;

    /**
     * SubmitsTableControl constructor.
     * @param Container $container
     */
    public function __construct(Container $container) {
        parent::__construct();
        $container->callInjects($this);
        $this->context = $container;
    }

    public function injectTranslator(ITranslator $translator): void {
        $this->translator = $translator;
    }

    /**
     * @param null $class
     * @return ITemplate
     */
    protected function createTemplate($class = null) {
        $template = parent::createTemplate($class);
        $template->setTranslator($this->translator);
        return $template;
    }

    final protected function getContext(): Container {
        return $this->context;
    }
}
