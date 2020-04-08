<?php

namespace FKSDB\Components\Controls;

use Nette\Application\UI\Control;
use Nette\DI\Container;
use Nette\Localization\ITranslator;

/**
 * Class BaseControl
 * @package FKSDB\Components\Controls
 */
abstract class BaseControl extends Control {
    /**
     * @var Container
     */
    private $context;

    /**
     * SubmitsTableControl constructor.
     * @param Container $container
     */
    public function __construct(Container $container) {
        parent::__construct();
        $this->context = $container;
    }

    /**
     * @return \Nette\Application\UI\ITemplate
     */
    protected function createTemplate() {
        $template = parent::createTemplate();
        /** @var ITranslator $translator */
        $translator = $this->getContext()->getByType(ITranslator::class);
        $template->setTranslator($translator);
        return $template;
    }
    /**
     * @return Container
     */
    protected final function getContext() {
        return $this->context;
    }
}
