<?php

declare(strict_types=1);

namespace FKSDB\Components\Controls\LinkPrinter;

use Fykosak\Utils\BaseComponent\BaseComponent;
use Fykosak\NetteORM\Exceptions\CannotAccessModelException;
use FKSDB\Models\ORM\ORMFactory;
use FKSDB\Models\Exceptions\BadTypeException;
use Fykosak\NetteORM\Model;
use Nette\Application\UI\InvalidLinkException;

class LinkPrinterComponent extends BaseComponent
{

    private ORMFactory $tableReflectionFactory;

    final public function injectTableReflectionFactory(ORMFactory $tableReflectionFactory): void
    {
        $this->tableReflectionFactory = $tableReflectionFactory;
    }

    /**
     * @throws BadTypeException
     * @throws CannotAccessModelException
     * @throws InvalidLinkException
     * @throws \ReflectionException
     */
    final public function render(string $linkId, Model $model): void
    {
        $factory = $this->tableReflectionFactory->loadLinkFactory(...explode('.', $linkId, 2));
        $this->getTemplate()->title = $factory->getText();
        $this->getTemplate()->link = $factory->create($this->getPresenter(), $model);
        $this->getTemplate()->render(__DIR__ . DIRECTORY_SEPARATOR . 'layout.link.latte');
    }
}
