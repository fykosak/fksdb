<?php

namespace FKSDB\Components\Forms\Factories;

use FKSDB\Components\Forms\Controls\IWriteOnly;
use FKSDB\DBReflection\ColumnFactories\AbstractColumnException;
use FKSDB\DBReflection\ColumnFactories\IColumnFactory;
use FKSDB\DBReflection\FieldLevelPermission;
use FKSDB\DBReflection\OmittedControlException;
use FKSDB\Components\Forms\Containers\ModelContainer;
use FKSDB\DBReflection\DBReflectionFactory;
use FKSDB\Exceptions\BadTypeException;
use Nette\Forms\Controls\BaseControl;

/**
 * Class SingleReflectionFactory
 * @author Michal Červeňák <miso@fykos.cz>
 */
class SingleReflectionFormFactory {

    protected DBReflectionFactory $tableReflectionFactory;

    public function __construct(DBReflectionFactory $tableReflectionFactory) {
        $this->tableReflectionFactory = $tableReflectionFactory;
    }

    /**
     * @param string $tableName
     * @param string $fieldName
     * @return IColumnFactory
     * @throws BadTypeException
     */
    protected function loadFactory(string $tableName, string $fieldName): IColumnFactory {
        return $this->tableReflectionFactory->loadColumnFactory($tableName . '.' . $fieldName);
    }

    /**
     * @param string $tableName
     * @param string $fieldName
     * @param mixed ...$args
     * @return BaseControl
     * @throws AbstractColumnException
     * @throws OmittedControlException
     * @throws BadTypeException
     */
    public function createField(string $tableName, string $fieldName, ...$args): BaseControl {
        return $this->loadFactory($tableName, $fieldName)->createField(...$args);
    }

    /**
     * @param string $table
     * @param array $fields
     * @param array $args
     * @return ModelContainer
     * @throws AbstractColumnException
     * @throws BadTypeException
     * @throws OmittedControlException
     */
    public function createContainer(string $table, array $fields, ...$args): ModelContainer {
        $container = new ModelContainer();

        foreach ($fields as $field) {
            $control = $this->createField($table, $field, ...$args);
            $container->addComponent($control, $field);
        }
        return $container;
    }

    /**
     * @param string $table
     * @param array $fields
     * @param FieldLevelPermission $userPermissions
     * @return ModelContainer
     * @throws AbstractColumnException
     * @throws BadTypeException
     * @throws OmittedControlException
     */
    public function createContainerWithMetadata(string $table, array $fields, FieldLevelPermission $userPermissions): ModelContainer {
        $container = new ModelContainer();
        foreach ($fields as $field => $metadata) {
            $factory = $this->loadFactory($table, $field);
            $control = $factory->createField();
            $canWrite = $factory->hasWritePermissions($userPermissions->write);
            $canRead = $factory->hasReadPermissions($userPermissions->read);
            if ($control instanceof IWriteOnly) {
                $control->setWriteOnly(!$canRead);
            } elseif ($canRead) {
// do nothing
            } else {
                continue;
            }
            $control->setDisabled(!$canWrite);

            $this->appendMetadata($control, $metadata);
            $container->addComponent($control, $field);
        }
        return $container;
    }

    protected function appendMetadata(BaseControl $control, array $metadata): void {
        foreach ($metadata as $key => $value) {
            switch ($key) {
                case 'required':
                    $control->setRequired($value);
                    break;
                case 'caption':
                    if ($value) {
                        $control->caption = $value;
                    }
                    break;
                case 'description':
                    if ($value) {
                        $control->setOption('description', $value);
                    }
            }
        }
    }
}
