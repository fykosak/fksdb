<?php

namespace FKSDB\Components\Controls\DBReflection;

use FKSDB\Components\Controls\BaseComponent;
use FKSDB\DBReflection\DBReflectionFactory;
use FKSDB\DBReflection\FieldLevelPermission;
use FKSDB\Exceptions\BadTypeException;
use FKSDB\ORM\AbstractModelSingle;

/**
 * Class ValuePrinterComponent
 * @author Michal Červeňák <miso@fykos.cz>
 */
class ValuePrinterComponent extends BaseComponent {
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
     * @param string $field
     * @param AbstractModelSingle $model
     * @param int $userPermission
     * @return void
     * @throws BadTypeException
     */
    public function render(string $field, AbstractModelSingle $model, int $userPermission) {
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
     *
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
     *
     * @throws BadTypeException
     */
    public function renderOnlyValue(string $field, AbstractModelSingle $model, int $userPermission = FieldLevelPermission::ALLOW_FULL) {
        $this->template->setFile(__DIR__ . '/layout.onlyValue.latte');
        $this->render($field, $model, $userPermission);
    }
}
