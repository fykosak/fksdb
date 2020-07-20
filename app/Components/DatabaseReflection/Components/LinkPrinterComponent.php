<?php

namespace FKSDB\Components\DatabaseReflection;

use FKSDB\Components\Controls\BaseComponent;
use FKSDB\Components\Forms\Factories\TableReflectionFactory;
use FKSDB\Exceptions\BadTypeException;
use FKSDB\ORM\AbstractModelSingle;

class LinkPrinterComponent extends BaseComponent {
    /** @var TableReflectionFactory */
    private $tableReflectionFactory;

    /**
     * @param TableReflectionFactory $tableReflectionFactory
     * @return void
     */
    public function injectTableReflectionFactory(TableReflectionFactory $tableReflectionFactory) {
        $this->tableReflectionFactory = $tableReflectionFactory;
    }

    /**
     * @param string $linkId
     * @param AbstractModelSingle $model
     * @return void
     * @throws BadTypeException
     */
    public function render(string $linkId, AbstractModelSingle $model) {
        $factory = $this->tableReflectionFactory->loadLinkFactory($linkId);
        $this->template->title = $factory->getText();
        $this->template->link = $factory->create($this->getPresenter(), $model);
        $this->template->setFile(__DIR__ . '/layout.link.latte');
        $this->template->render();
    }
}
