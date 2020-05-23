<?php

namespace FKSDB\Components\DatabaseReflection;

use FKSDB\Components\Controls\BaseComponent;
use FKSDB\Components\Forms\Factories\ITestedRowFactory;
use FKSDB\Components\Forms\Factories\TableReflectionFactory;
use FKSDB\ORM\AbstractModelSingle;

/**
 * Class ValuePrinterComponent
 * @package FKSDB\Components\DatabaseReflection
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
     * @param bool $tested
     * @throws \Exception
     */
    public function render(string $field, AbstractModelSingle $model, int $userPermission, bool $tested) {
        list($tableName, $fieldName) = TableReflectionFactory::parseRow($field);
        $factory = $this->tableReflectionFactory->loadService($tableName, $fieldName);
        $this->template->title = $factory->getTitle();
        $this->template->description = $factory->getDescription();
        $this->template->testLog = null;
        // if ($factory instanceof ITestedRowFactory && $tested) {
        //  $this->template->testLog = $factory->runTest($model); TODO FIX
        // }
        $this->template->html = $factory->renderValue($model, $userPermission);
        $this->template->setFile(__DIR__ . '/layout.latte');
        $this->template->render();
    }

    /**
     * @param string $field
     * @param AbstractModelSingle $model
     * @param int $userPermission
     * @param bool $tested
     * @throws \Exception
     */
    public function renderRow(string $field, AbstractModelSingle $model, int $userPermission = 2048, bool $tested = false) {
        $this->template->layout = self::LAYOUT_ROW;
        $this->render($field, $model, $userPermission, $tested);
    }

    /**
     * @param string $field
     * @param AbstractModelSingle $model
     * @param int $userPermission
     * @param bool $tested
     * @throws \Exception
     */
    public function renderListItem(string $field, AbstractModelSingle $model, int $userPermission = 2048, bool $tested = false) {
        $this->template->layout = self::LAYOUT_LIST_ITEM;
        $this->render($field, $model, $userPermission, $tested);
    }

    /**
     * @param string $field
     * @param AbstractModelSingle $model
     * @param int $userPermission
     * @param bool $tested
     * @throws \Exception
     */
    public function renderOnlyValue(string $field, AbstractModelSingle $model, int $userPermission = 2048, bool $tested = false) {
        $this->template->layout = self::LAYOUT_ONLY_VALUE;
        $this->render($field, $model, $userPermission, $tested);
    }
}
