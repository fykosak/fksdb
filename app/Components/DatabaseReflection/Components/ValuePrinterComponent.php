<?php

namespace FKSDB\Components\DatabaseReflection;

use FKSDB\Components\Controls\BaseComponent;
use FKSDB\Components\Forms\Factories\TableReflectionFactory;
use FKSDB\Exceptions\BadTypeException;
use FKSDB\ORM\AbstractModelSingle;
use Nette\Application\BadRequestException;

/**
 * Class ValuePrinterComponent
 * @author Michal Červeňák <miso@fykos.cz>
 */
class ValuePrinterComponent extends BaseComponent {
    const LAYOUT_LIST_ITEM = 'list-item';
    const LAYOUT_ROW = 'row';
    const LAYOUT_ONLY_VALUE = 'only-value';
    /**
     * @var TableReflectionFactory
     */
    private $tableReflectionFactory;

    /**
     * @param TableReflectionFactory $tableReflectionFactory
     * @return void
     */
    public function injectTableReflectionFactory(TableReflectionFactory $tableReflectionFactory) {
        $this->tableReflectionFactory = $tableReflectionFactory;
    }

    /**
     * @param string $field
     * @param AbstractModelSingle $model
     * @param int $userPermission
     * @return void
     * @throws BadTypeException
     * @throws BadRequestException
     */
    public function render(string $field, AbstractModelSingle $model, int $userPermission) {
        $factory = $this->tableReflectionFactory->loadRowFactory($field);
        $this->template->title = $factory->getTitle();
        $this->template->description = $factory->getDescription();
        $this->template->testLog = null;
        $this->template->html = $factory->renderValue($model, $userPermission);
        $this->template->setFile(__DIR__ . '/layout.latte');
        $this->template->render();
    }

    /**
     * @param string $field
     * @param AbstractModelSingle $model
     * @param int $userPermission
     * @return void
     * @throws BadRequestException
     * @throws BadTypeException
     */
    public function renderRow(string $field, AbstractModelSingle $model, int $userPermission = 2048) {
        $this->template->layout = self::LAYOUT_ROW;
        $this->render($field, $model, $userPermission);
    }

    /**
     * @param string $field
     * @param AbstractModelSingle $model
     * @param int $userPermission
     * @return void
     * @throws BadRequestException
     * @throws BadTypeException
     */
    public function renderListItem(string $field, AbstractModelSingle $model, int $userPermission = 2048) {
        $this->template->layout = self::LAYOUT_LIST_ITEM;
        $this->render($field, $model, $userPermission);
    }

    /**
     * @param string $field
     * @param AbstractModelSingle $model
     * @param int $userPermission
     * @return void
     * @throws BadRequestException
     * @throws BadTypeException
     */
    public function renderOnlyValue(string $field, AbstractModelSingle $model, int $userPermission = 2048) {
        $this->template->layout = self::LAYOUT_ONLY_VALUE;
        $this->render($field, $model, $userPermission);
    }
}
