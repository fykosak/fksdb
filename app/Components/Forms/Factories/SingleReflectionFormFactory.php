<?php

namespace FKSDB\Components\Forms\Factories;

use FKSDB\Components\Forms\Controls\WriteOnly\WriteOnly;
use FKSDB\Models\ORM\Columns\ColumnFactory;
use FKSDB\Models\ORM\FieldLevelPermission;
use FKSDB\Models\ORM\OmittedControlException;
use FKSDB\Components\Forms\Containers\ModelContainer;
use FKSDB\Models\ORM\ORMFactory;
use FKSDB\Models\Exceptions\BadTypeException;
use Nette\Forms\Controls\BaseControl;

/**
 * Class SingleReflectionFactory
 * @author Michal Červeňák <miso@fykos.cz>
 */
class SingleReflectionFormFactory {

    protected ORMFactory $tableReflectionFactory;

    public function __construct(ORMFactory $tableReflectionFactory) {
        $this->tableReflectionFactory = $tableReflectionFactory;
    }

    /**
     * @param string $tableName
     * @param string $fieldName
     * @return ColumnFactory
     * @throws BadTypeException
     */
    protected function loadFactory(string $tableName, string $fieldName): ColumnFactory {
        return $this->tableReflectionFactory->loadColumnFactory($tableName, $fieldName);
    }

    /**
     * @param string $tableName
     * @param string $fieldName
     * @param ...$args
     * @return BaseControl
     * @throws BadTypeException
     * @throws OmittedControlException
     */
    public function createField(string $tableName, string $fieldName, ...$args): BaseControl {
        return $this->loadFactory($tableName, $fieldName)->createField(...$args);
    }

    /**
     * @param string $table
     * @param array $fields
     * @param array $args
     * @return ModelContainer
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
            if ($control instanceof WriteOnly) {
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
