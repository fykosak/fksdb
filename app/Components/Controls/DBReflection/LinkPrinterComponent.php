<?php

namespace FKSDB\Components\Controls\DBReflection;

use FKSDB\Components\Controls\BaseComponent;
use FKSDB\DBReflection\DBReflectionFactory;
use FKSDB\Exceptions\BadTypeException;
use FKSDB\ORM\AbstractModelSingle;

/**
 * Class LinkPrinterComponent
 * @author Michal Červeňák <miso@fykos.cz>
 */
class LinkPrinterComponent extends BaseComponent {
    /** @var DBReflectionFactory */
    private $tableReflectionFactory;

    /**
     * @param DBReflectionFactory $tableReflectionFactory
     * @return void
     */
    public function injectTableReflectionFactory(DBReflectionFactory $tableReflectionFactory) {
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
        $this->template->setFile(__DIR__ . DIRECTORY_SEPARATOR);
        $this->template->render();
    }
}
