<?php

declare(strict_types=1);

namespace FKSDB\Components\Controls\LinkPrinter;

use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Models\ORM\ReflectionFactory;
use Fykosak\NetteORM\Exceptions\CannotAccessModelException;
use Fykosak\NetteORM\Model;
use Fykosak\Utils\BaseComponent\BaseComponent;
use Nette\Application\UI\InvalidLinkException;

class LinkPrinterComponent extends BaseComponent
{

    private ReflectionFactory $tableReflectionFactory;

    final public function injectTableReflectionFactory(ReflectionFactory $tableReflectionFactory): void
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
            __DIR__ . DIRECTORY_SEPARATOR,
            [
                'title' => $factory->getTitle(),
                'link' => $factory->create($this->getPresenter(), $model),
            ]
        );
    }
}
