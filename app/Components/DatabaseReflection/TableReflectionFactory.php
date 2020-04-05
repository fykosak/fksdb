<?php

namespace FKSDB\Components\Forms\Factories;

use FKSDB\Components\DatabaseReflection\AbstractRow;
use FKSDB\Components\DatabaseReflection\Links\AbstractLink;
use FKSDB\Components\DatabaseReflection\RowFactoryComponent;
use Nette\DI\Container;
use Nette\InvalidArgumentException;
use Nette\Localization\ITranslator;
use Nette\SmartObject;
use Nette\Utils\Html;

/**
 * Class TableReflectionFactory
 * @package FKSDB\Components\Forms\Factories\PersonInfo
 */
final class TableReflectionFactory {
    use SmartObject;
    /**
     * @var Container
     */
    private $container;
    /**
     * @var ITranslator
     */
    private $translator;

    /**
     * PersonInfoFactory constructor.
     * @param Container $container
     * @param ITranslator $translator
     */
    public function __construct(Container $container, ITranslator $translator) {
        $this->container = $container;
        $this->translator = $translator;
    }

    /**
     * @param string $tableName
     * @param string $fieldName
     * @return AbstractRow
     * @throws InvalidArgumentException
     * @throws \Exception
     */
    public function loadService(string $tableName, string $fieldName): AbstractRow {
        $service = $this->container->getService('DBReflection.' . $tableName . '.' . $fieldName);
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


    /**
     * @param array $rows
     * @return array
     */
    public static function parseRows(array $rows): array {
        $items = [];
        foreach ($rows as $item) {
            $items[] = self::parseRow($item);
        }
        return $items;
    }

    /**
     * @param string $row
     * @return array
     */
    public static function parseRow(string $row): array {
        return explode('.', $row);
    }
}
