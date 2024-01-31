<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM;

use FKSDB\Components\Forms\Containers\Models\ReferencedPersonContainer;
use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Models\ORM\Links\Link;
use FKSDB\Models\ORM\Columns\ColumnFactory;
use FKSDB\Models\ORM\Columns\OmittedControlException;
use Fykosak\NetteORM\Model\Model;
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
}
