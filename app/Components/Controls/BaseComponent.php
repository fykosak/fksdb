<?php

namespace FKSDB\Components\Controls;

use FKSDB\Localization\GettextTranslator;
use Nette\Application\UI\Control;
use Nette\Application\UI\ITemplate;
use Nette\Bridges\ApplicationLatte\Template;
use Nette\DI\Container;

/**
 * Class BaseComponent
 * @author Michal Červeňák <miso@fykos.cz>
 * @property Template $template
 */
abstract class BaseComponent extends Control {

    private Container $context;

    private GettextTranslator $translator;

    /**
     * SubmitsTableControl constructor.
     * @param Container $container
     */
    public function __construct(Container $container) {
        $container->callInjects($this);
        $this->context = $container;
    }

    final public function injectTranslator(GettextTranslator $translator): void {
        $this->translator = $translator;
    }

    final protected function getTranslator(): GettextTranslator {
        return $this->translator;
    }

    protected function createTemplate(): ITemplate {
        $template = parent::createTemplate();
        $template->setTranslator($this->translator);
        return $template;
    }

    final protected function getContext(): Container {
        return $this->context;
    }
}
