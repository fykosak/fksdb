<?php

declare(strict_types=1);

namespace FKSDB\Components\Controls;

use Fykosak\Utils\Localization\GettextTranslator;
use Nette\Application\UI\Control;
use Nette\Application\UI\Template;
use Nette\DI\Container;

abstract class BaseComponent extends Control
{
    private Container $context;

    private GettextTranslator $translator;

    public function __construct(Container $container)
    {
        $container->callInjects($this);
        $this->context = $container;
    }

    final public function injectTranslator(GettextTranslator $translator): void
    {
        $this->translator = $translator;
    }

    final protected function getTranslator(): GettextTranslator
    {
        return $this->translator;
    }

    protected function createTemplate(): Template
    {
        $template = parent::createTemplate();
        $template->setTranslator($this->translator);
        return $template;
    }

    final protected function getContext(): Container
    {
        return $this->context;
    }
}
