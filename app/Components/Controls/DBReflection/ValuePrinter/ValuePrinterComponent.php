<?php

namespace FKSDB\Components\Controls\DBReflection\ValuePrinter;

use FKSDB\Components\Controls\BaseComponent;
use FKSDB\Model\DBReflection\DBReflectionFactory;
use FKSDB\Model\DBReflection\FieldLevelPermission;
use FKSDB\Model\Exceptions\BadTypeException;
use FKSDB\Model\ORM\Models\AbstractModelSingle;

/**
 * Class ValuePrinterComponent
 * @author Michal Červeňák <miso@fykos.cz>
 */
class ValuePrinterComponent extends BaseComponent {

    private DBReflectionFactory $tableReflectionFactory;

    final public function injectTableReflectionFactory(DBReflectionFactory $tableReflectionFactory): void {
        $this->tableReflectionFactory = $tableReflectionFactory;
    }

    /**
     * @param string $field
     * @param AbstractModelSingle $model
     * @param int $userPermission
     * @return void
     * @throws BadTypeException
     */
    public function render(string $field, AbstractModelSingle $model, int $userPermission): void {
        $factory = $this->tableReflectionFactory->loadColumnFactory($field);
        $this->template->title = $factory->getTitle();
        $this->template->description = $factory->getDescription();
        $this->template->html = $factory->render($model, $userPermission);
        $this->template->render();
    }

    /**
     * @param string $field
     * @param AbstractModelSingle $model
     * @param int $userPermission
     * @return void
     * @throws BadTypeException
     */
    public function renderRow(string $field, AbstractModelSingle $model, int $userPermission = FieldLevelPermission::ALLOW_FULL): void {
        $this->template->setFile(__DIR__ . DIRECTORY_SEPARATOR . 'layout.row.latte');
        $this->render($field, $model, $userPermission);
    }

    /**
     * @param string $field
     * @param AbstractModelSingle $model
     * @param int $userPermission
     * @return void
     * @throws BadTypeException
     */
    public function renderListItem(string $field, AbstractModelSingle $model, int $userPermission = FieldLevelPermission::ALLOW_FULL): void {
        $this->template->setFile(__DIR__ . DIRECTORY_SEPARATOR . 'layout.listItem.latte');
        $this->render($field, $model, $userPermission);
    }

    /**
     * @param string $field
     * @param AbstractModelSingle $model
     * @param int $userPermission
     * @return void
     * @throws BadTypeException
     */
    public function renderOnlyValue(string $field, AbstractModelSingle $model, int $userPermission = FieldLevelPermission::ALLOW_FULL): void {
        $this->template->setFile(__DIR__ . DIRECTORY_SEPARATOR . 'layout.onlyValue.latte');
        $this->render($field, $model, $userPermission);
    }
}
