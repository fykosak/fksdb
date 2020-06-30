<?php

namespace FKSDB\Components\Forms\Factories;

use FKSDB\Components\DatabaseReflection\ColumnFactories\AbstractColumnException;
use FKSDB\Components\DatabaseReflection\ColumnFactories\IColumnFactory;
use FKSDB\Components\DatabaseReflection\FieldLevelPermission;
use FKSDB\Components\DatabaseReflection\OmittedControlException;
use FKSDB\Components\Forms\Containers\IWriteOnly;
use FKSDB\Components\Forms\Containers\ModelContainer;
use FKSDB\Exceptions\NotImplementedException;
use Nette\Forms\Controls\BaseControl;
use Nette\InvalidStateException;

/**
 * Class SingleReflectionFactory
 * @author Michal Červeňák <miso@fykos.cz>
 */
abstract class SingleReflectionFactory {
    /**
     * @var TableReflectionFactory
     */
    protected $tableReflectionFactory;

    /**
     * PersonHistoryFactory constructor.
     * @param TableReflectionFactory $tableReflectionFactory
     */
    public function __construct(TableReflectionFactory $tableReflectionFactory) {
        $this->tableReflectionFactory = $tableReflectionFactory;
    }

    protected function getTableName(): string {
        throw new NotImplementedException();
    }

    /**
     * @param string $fieldName
     * @return IColumnFactory
     * @throws InvalidStateException
     * @throws \Exception
     */
    protected function loadFactory(string $fieldName): IColumnFactory {
        if (strpos($fieldName, '.') !== false) {
            return $this->tableReflectionFactory->loadColumnFactory($fieldName);
        }
        return $this->tableReflectionFactory->loadColumnFactory($this->getTableName() . '.' . $fieldName);
    }

    /**
     * @param string $fieldName
     * @param mixed ...$args
     * @return BaseControl
     * @throws AbstractColumnException
     * @throws OmittedControlException
     */
    public function createField(string $fieldName, ...$args): BaseControl {
        return $this->loadFactory($fieldName)->createField(...$args);
    }

    /**
     * @param array $fields
     * @return ModelContainer
     * @throws AbstractColumnException
     * @throws OmittedControlException
     */
    public function createContainer(array $fields): ModelContainer {
        $container = new ModelContainer();

        foreach ($fields as $field) {
            $control = $this->createField($field);
            $container->addComponent($control, str_replace('.', '__', $field));
        }
        return $container;
    }

    /**
     * @param array $fields
     * @param FieldLevelPermission $userPermissions
     * @return ModelContainer
     * @throws \Exception
     */
    public function createContainerWithMetadata(array $fields, FieldLevelPermission $userPermissions): ModelContainer {
        $container = new ModelContainer();
        foreach ($fields as $field => $metadata) {
            $factory = $this->loadFactory($field);
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

    /**
     * @param BaseControl $control
     * @param array $metadata
     * @return void
     */
    protected function appendMetadata(BaseControl $control, array $metadata) {
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
