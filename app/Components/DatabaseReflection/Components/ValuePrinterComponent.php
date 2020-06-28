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
        $factory = $this->tableReflectionFactory->loadColumnFactory($field);
        $this->template->title = $factory->getTitle();
        $this->template->description = $factory->getDescription();
        $this->template->html = $factory->renderValue($model, $userPermission);
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
    public function renderRow(string $field, AbstractModelSingle $model, int $userPermission = FieldLevelPermission::ALLOW_FULL) {
        $this->template->setFile(__DIR__ . '/layout.row.latte');
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
    public function renderListItem(string $field, AbstractModelSingle $model, int $userPermission = FieldLevelPermission::ALLOW_FULL) {
        $this->template->setFile(__DIR__ . '/layout.listItem.latte');
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
    public function renderOnlyValue(string $field, AbstractModelSingle $model, int $userPermission = FieldLevelPermission::ALLOW_FULL) {
        $this->template->setFile(__DIR__ . '/layout.onlyValue.latte');
        $this->render($field, $model, $userPermission);
    }
}
