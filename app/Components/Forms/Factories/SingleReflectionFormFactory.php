<?php

declare(strict_types=1);

namespace FKSDB\Components\Forms\Factories;

use FKSDB\Components\Forms\Containers\ModelContainer;
use FKSDB\Components\Forms\Containers\Models\ContainerWithOptions;
use FKSDB\Components\Forms\Containers\Models\ReferencedPersonContainer;
use FKSDB\Components\Forms\Controls\WriteOnly\WriteOnly;
use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Models\ORM\Columns\ColumnFactory;
use FKSDB\Models\ORM\FieldLevelPermission;
use FKSDB\Models\ORM\OmittedControlException;
use FKSDB\Models\ORM\ORMFactory;
use Fykosak\NetteORM\Model;
use Nette\DI\Container;
use Nette\Forms\Controls\BaseControl;

/**
 * @phpstan-import-type EvaluatedFieldsDefinition from ReferencedPersonContainer
 * @phpstan-import-type EvaluatedFieldMetaData from ReferencedPersonContainer
 */
final class SingleReflectionFormFactory
{
    private ORMFactory $tableReflectionFactory;
    private Container $container;

    public function __construct(ORMFactory $tableReflectionFactory, Container $container)
    {
        $this->tableReflectionFactory = $tableReflectionFactory;
        $this->container = $container;
    }

    /**
     * @throws BadTypeException
     * @phpstan-return ColumnFactory<Model,mixed>
     */
    protected function loadFactory(string $tableName, string $fieldName): ColumnFactory
    {
        return $this->tableReflectionFactory->loadColumnFactory($tableName, $fieldName);
    }

    /**
     * @throws BadTypeException
     * @throws OmittedControlException
     * @phpstan-param mixed $args
     */
    public function createField(string $tableName, string $fieldName, ...$args): BaseControl
    {
        return $this->loadFactory($tableName, $fieldName)->createField(...$args);
    }

    /**
     * @throws BadTypeException
     * @throws OmittedControlException
     * @phpstan-param mixed $args
     * @phpstan-param array<string,EvaluatedFieldMetaData> $fields
     */
    public function createContainerWithMetadata(
        string $table,
        array $fields,
        ?FieldLevelPermission $userPermissions = null,
        ...$args
    ): ModelContainer {
        $container = new ModelContainer($this->container);
        foreach ($fields as $field => $metadata) {
            $this->addToContainer($container, $table, $field, $metadata, $userPermissions, ...$args);
        }
        return $container;
    }

    /**
     * @param mixed $args
     * @throws BadTypeException
     * @throws OmittedControlException
     * @phpstan-param EvaluatedFieldMetaData $metaData
     */
    public function addToContainer(
        ContainerWithOptions $container,
        string $table,
        string $field,
        array $metaData = [],
        ?FieldLevelPermission $userPermissions = null,
        ...$args
    ): void {
        $factory = $this->loadFactory($table, $field);
        $control = $factory->createField(...$args);
        if ($userPermissions) {
            $canWrite = $factory->hasWritePermissions($userPermissions->write);
            $canRead = $factory->hasReadPermissions($userPermissions->read);
            if ($control instanceof WriteOnly) {
                $control->setWriteOnly(!$canRead);
            } elseif (!$canRead) {
                return;
            }
            $control->setDisabled(!$canWrite);
        }
        $this->appendMetadata($control, $metaData);
        $container->addComponent($control, $field);
    }

    /**
     * @phpstan-param EvaluatedFieldMetaData $metadata
     */
    protected function appendMetadata(BaseControl $control, array $metadata): void
    {
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
