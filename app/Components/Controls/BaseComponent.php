<?php

namespace FKSDB\Components\Controls;

use Nette\Application\UI\Control;
use Nette\Application\UI\ITemplate;
use Nette\DI\Container;
use Nette\Localization\ITranslator;

/**
 * Class BaseComponent
 * @author Michal Červeňák <miso@fykos.cz>
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

    protected function createTemplate(): ITemplate {
        $template = parent::createTemplate();
        $template->setTranslator($this->translator);
        return $template;
    }

    final protected function getContext(): Container {
        return $this->context;
    }
}
