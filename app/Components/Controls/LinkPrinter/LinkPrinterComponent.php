<?php

declare(strict_types=1);

namespace FKSDB\Components\Controls\LinkPrinter;

use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Models\ORM\ORMFactory;
use Fykosak\NetteORM\Exceptions\CannotAccessModelException;
use Fykosak\NetteORM\Model;
use Fykosak\Utils\BaseComponent\BaseComponent;
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
        $this->template->render(
            __DIR__ . DIRECTORY_SEPARATOR . 'layout.link.latte',
            [
                'title' => $factory->getText(),
                'link' => $factory->create($this->getPresenter(), $model),
            ]
        );
    }
}
