<?php

namespace FKSDB\Components\Controls\ColumnPrinter;

use FKSDB\Components\Controls\BaseComponent;
use FKSDB\Models\Entity\CannotAccessModelException;
use FKSDB\Models\ORM\ORMFactory;
use FKSDB\Models\ORM\FieldLevelPermission;
use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Models\ORM\Models\AbstractModelSingle;

/**
 * Class ValuePrinterComponent
 * @author Michal Červeňák <miso@fykos.cz>
 */
class ColumnPrinterComponent extends BaseComponent {

    private ORMFactory $tableReflectionFactory;

    final public function injectTableReflectionFactory(ORMFactory $tableReflectionFactory): void {
        $this->tableReflectionFactory = $tableReflectionFactory;
    }

    /**
     * @param string $field
     * @param AbstractModelSingle $model
     * @param int $userPermission
     * @return void
     * @throws BadTypeException
     * @throws CannotAccessModelException
     */
    public function render(string $field, AbstractModelSingle $model, int $userPermission): void {
        $factory = $this->tableReflectionFactory->loadColumnFactory(...explode('.', $field));
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
     * @throws CannotAccessModelException
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
     * @throws CannotAccessModelException
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
     * @throws CannotAccessModelException
     */
    public function renderOnlyValue(string $field, AbstractModelSingle $model, int $userPermission = FieldLevelPermission::ALLOW_FULL): void {
        $this->template->setFile(__DIR__ . DIRECTORY_SEPARATOR . 'layout.onlyValue.latte');
        $this->render($field, $model, $userPermission);
    }
}
