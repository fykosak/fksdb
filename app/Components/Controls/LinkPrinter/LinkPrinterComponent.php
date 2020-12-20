<?php

namespace FKSDB\Components\Controls\LinkPrinter;

use FKSDB\Components\Controls\BaseComponent;
use FKSDB\Models\ORM\ORMFactory;
use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Models\ORM\Models\AbstractModelSingle;

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
     * @param AbstractModelSingle $model
     * @return void
     * @throws BadTypeException
     */
    public function render(string $linkId, AbstractModelSingle $model): void {
        $factory = $this->tableReflectionFactory->loadLinkFactory(...explode('.', $linkId, 2));
        $this->template->title = $factory->getText();
        $this->template->link = $factory->create($this->getPresenter(), $model);
        $this->template->setFile(__DIR__ . DIRECTORY_SEPARATOR . 'layout.link.latte');
        $this->template->render();
    }
}
