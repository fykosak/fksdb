<?php

declare(strict_types=1);

namespace FKSDB\Components\PDFGenerators\Providers;

use FKSDB\Components\Controls\BaseComponent;
use Nette\DI\Container;

final class ProviderComponent extends BaseComponent
{
    private AbstractPageComponent $pageComponent;
    private iterable $items;

    public function __construct(
        AbstractPageComponent $pageComponent,
        iterable $items,
        Container $container
    ) {
        parent::__construct($container);
        $this->pageComponent = $pageComponent;
        $this->items = $items;
    }

    protected function createComponentPage(): AbstractPageComponent
    {
        return $this->pageComponent;
    }

    public function render(): void
    {
        $this->template->items = $this->items;
        /** @var AbstractPageComponent $component */
        $component = $this->getComponent('page');
        $this->template->render($component->getPagesTemplatePath());
    }
}
