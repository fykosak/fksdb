<?php

declare(strict_types=1);

namespace FKSDB\Components\PDFGenerators\Provider;

use FKSDB\Components\Controls\BaseComponent;
use FKSDB\Components\PDFGenerators\PageComponent;
use Nette\DI\Container;

final class ProviderComponent extends BaseComponent
{
    public const FORMAT_A5 = 'A5';

    private Provider $provider;

    public function __construct(Provider $provider, Container $container)
    {
        parent::__construct($container);
        $this->provider = $provider;
    }

    public function createComponentPage(): PageComponent
    {
        return $this->provider->createComponentPage();
    }

    public function render(): void
    {
        $this->template->items = $this->provider->getItems();
        $this->template->render(__DIR__ . '/provider.latte');
    }

    public function getFormat(): string
    {
        return $this->provider->getFormat();
    }
}
