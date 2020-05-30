<?php

namespace FKSDB\Components\Forms\Factories;

use FKSDB\Components\DatabaseReflection\AbstractRow;
use FKSDB\Components\DatabaseReflection\Links\AbstractLink;
use Nette\DI\Container;
use Nette\InvalidArgumentException;
use Nette\SmartObject;
use Nette\Utils\Html;

/**
 * Class TableReflectionFactory
 * *
 */
final class TableReflectionFactory {
    use SmartObject;
    /**
     * @var Container
     */
    private $container;

    /**
     * PersonInfoFactory constructor.
     * @param Container $container
     */
    public function __construct(Container $container) {
        $this->container = $container;
    }

    /**
     * @param string $tableName
     * @param string $fieldName
     * @return AbstractRow
     * @throws InvalidArgumentException
     * @throws \Exception
     */
    public function loadService(string $tableName, string $fieldName = null): AbstractRow {
        if (is_null($fieldName)) {
            $factoryName = $tableName;
        } else {
            $factoryName = $tableName . '.' . $fieldName;
        }
        $service = $this->container->getService('DBReflection.' . $factoryName);
        if (!$service instanceof AbstractRow) {
            throw new InvalidArgumentException('Field ' . $tableName . '.' . $fieldName . ' not exists');
        }
        return $service;
    }

    /**
     * @param string $linkId
     * @return AbstractLink
     * @throws \Exception
     */
    public function loadLinkFactory(string $linkId): AbstractLink {
        $service = $this->container->getService('DBReflection.link.' . $linkId);
        if (!$service instanceof AbstractLink) {
            throw new InvalidArgumentException('LinkFactory ' . $linkId . ' not exists');
        }
        return $service;
    }

    /**
     * @param string $tableName
     * @param string $fieldName
     * @param int $userPermissionLevel
     * @return callable
     * @throws \Exception
     */
    public function createGridCallback(string $tableName, string $fieldName, int $userPermissionLevel): callable {
        $factory = $this->loadService($tableName, $fieldName);
        return function ($model) use ($factory, $userPermissionLevel): Html {
            return $factory->renderValue($model, $userPermissionLevel);
        };
    }

    public static function parseRows(array $rows): array {
        $items = [];
        foreach ($rows as $item) {
            $items[] = self::parseRow($item);
        }
        return $items;
    }

    public static function parseRow(string $row): array {
        return explode('.', $row);
    }
}
