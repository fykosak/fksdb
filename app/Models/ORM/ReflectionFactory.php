<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM;

use FKSDB\Components\Forms\Containers\ModelContainer;
use FKSDB\Components\Forms\Containers\Models\ReferencedPersonContainer;
use FKSDB\Components\Forms\Controls\WriteOnly\WriteOnly;
use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Models\ORM\Columns\ColumnFactory;
use FKSDB\Models\ORM\Columns\OmittedControlException;
use FKSDB\Models\ORM\Links\Link;
use Fykosak\NetteORM\Model\Model;
use Nette\ComponentModel\IContainer;
use Nette\DI\Container;
use Nette\DI\MissingServiceException;
use Nette\Forms\Controls\BaseControl;
use Nette\SmartObject;

/**
 * @phpstan-import-type EvaluatedFieldsDefinition from ReferencedPersonContainer
 * @phpstan-import-type EvaluatedFieldMetaData from ReferencedPersonContainer
 */
final class ReflectionFactory
{
    use SmartObject;

    private Container $container;

    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    /**
     * @phpstan-return ColumnFactory<Model,mixed>
     * @throws MissingServiceException
     * @throws BadTypeException
     */
    public function loadColumnFactory(string $tableName, string $colName): ColumnFactory
    {
        $service = $this->container->getService('orm.' . $tableName . '.column.' . $colName);
        if (!$service instanceof ColumnFactory) {
            throw new BadTypeException(ColumnFactory::class, $service);
        }
        return $service;
    }

    /**
     * @throws BadTypeException
     * @throws MissingServiceException
     * @phpstan-return Link<Model>
     */
    public function loadLinkFactory(string $tableName, string $linkId): Link
    {
        $service = $this->container->getService('orm.' . $tableName . '.link.' . $linkId);
        if (!$service instanceof Link) {
            throw new BadTypeException(Link::class, $service);
        }
        return $service;
    }

    /**
     * @throws BadTypeException
     * @throws OmittedControlException
     * @phpstan-param mixed $args
     */
    public function createField(string $tableName, string $fieldName, ...$args): BaseControl
    {
        return $this->loadColumnFactory($tableName, $fieldName)->createField(...$args);
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
        IContainer $container,
        string $table,
        string $field,
        array $metaData = [],
        ?FieldLevelPermission $userPermissions = null,
        ...$args
    ): void {
        $factory = $this->loadColumnFactory($table, $field);
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
