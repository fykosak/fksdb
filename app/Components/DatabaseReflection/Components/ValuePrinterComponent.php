<?php

namespace FKSDB\Components\DatabaseReflection;

use FKSDB\Components\Forms\Factories\ITestedRowFactory;
use FKSDB\Components\Forms\Factories\TableReflectionFactory;
use FKSDB\ORM\AbstractModelSingle;
use Nette\Application\UI\Control;
use Nette\Localization\ITranslator;

/**
 * Class ValuePrinterComponent
 * @package FKSDB\Components\DatabaseReflection
 */
class ValuePrinterComponent extends Control {
    const LAYOUT_LIST_ITEM = 'list-item';
    const LAYOUT_ROW = 'row';
    const LAYOUT_ONLY_VALUE = 'only-value';
    /**
     * @var ITranslator
     */
    private $translator;
    /**
     * @var TableReflectionFactory
     */
private $tableReflectionFactory;

    /**
     * StalkingRowComponent constructor.
     * @param ITranslator $translator
     * @param TableReflectionFactory $tableReflectionFactory
     */
    public function __construct(ITranslator $translator, TableReflectionFactory $tableReflectionFactory) {
        parent::__construct();
        $this->translator = $translator;
        $this->tableReflectionFactory=$tableReflectionFactory;

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

        $this->template->setTranslator($this->translator);
        $this->template->title = $factory->getTitle();
        $this->template->description = $factory->getDescription();
        $this->template->testLog = null;
        if ($factory instanceof ITestedRowFactory && $tested) {
            $this->template->testLog = $factory->runTest($model);
        }
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
