<?php

namespace FKSDB\Components\Controls\LinkPrinter;

use FKSDB\Components\Controls\BaseComponent;
use Fykosak\NetteORM\Exceptions\CannotAccessModelException;
use FKSDB\Models\ORM\ORMFactory;
use FKSDB\Models\Exceptions\BadTypeException;
use Fykosak\NetteORM\AbstractModel;
use Nette\Application\UI\InvalidLinkException;

/**
 * Class LinkPrinterComponent
 * @author Michal Červeňák <miso@fykos.cz>
 */
class LinkPrinterComponent extends BaseComponent {

    private ORMFactory $tableReflectionFactory;

    final public function injectTableReflectionFactory(ORMFactory $tableReflectionFactory): void {
        $this->tableReflectionFactory = $tableReflectionFactory;
    }

    /**
     * @param string $linkId
     * @param AbstractModel $model
     * @return void
     * @throws BadTypeException
     * @throws CannotAccessModelException
     * @throws InvalidLinkException
     */
    final public function render(string $linkId, AbstractModel $model): void {
        $factory = $this->tableReflectionFactory->loadLinkFactory(...explode('.', $linkId, 2));
        $this->template->title = $factory->getText();
        $this->template->link = $factory->create($this->getPresenter(), $model);
        $this->template->render(__DIR__ . DIRECTORY_SEPARATOR . 'layout.link.latte');
    }
}
