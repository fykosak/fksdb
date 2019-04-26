<?php

namespace FKSDB\Components\Forms\Factories;

use Closure;
use FKSDB\Components\DatabaseReflection\AbstractRow;
use FKSDB\Components\DatabaseReflection\AbstractRowComponent;
use FKSDB\Components\DatabaseReflection\ListComponent;
use FKSDB\Components\DatabaseReflection\OnlyValueComponent;
use FKSDB\Components\DatabaseReflection\RowComponent;
use FKSDB\ORM\AbstractModelSingle;
use Nette\DI\Container;
use Nette\InvalidArgumentException;
use Nette\Localization\ITranslator;
use Nette\Utils\Html;

/**
 * Class TableReflectionFactory
 * @package FKSDB\Components\Forms\Factories\PersonInfo
 */
final class TableReflectionFactory {

    /**
     * @var AbstractRow[]
     */
    private $fieldFactories = [];
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
     * @throws \Exception
     */
    public function loadService(string $tableName, string $fieldName): AbstractRow {
        if (isset($this->fieldFactories[$fieldName])) {
            return $this->fieldFactories[$fieldName];
        }
        $service = $this->container->getService('row.' . $tableName . '.' . $fieldName);
        if (!$service instanceof AbstractRow) {
            throw new InvalidArgumentException('Field ' . $tableName . '.' . $fieldName . ' not exists');
        }
        $this->fieldFactories[$fieldName] = $service;
        return $service;
    }

    /**
     * @param string $tableName
     * @param string $fieldName
     * @param int $userPermission
     * @return ListComponent
     * @throws \Exception
     */
    private function createListComponent(string $tableName, string $fieldName, int $userPermission): ListComponent {
        $factory = $this->loadService($tableName, $fieldName);
        return new ListComponent($this->translator, $factory, $fieldName, $userPermission);
    }

    /**
     * @param string $tableName
     * @param string $fieldName
     * @param int $userPermission
     * @return RowComponent
     * @throws \Exception
     */
    private function createRowComponent(string $tableName, string $fieldName, int $userPermission): RowComponent {
        $factory = $this->loadService($tableName, $fieldName);
        return new RowComponent($this->translator, $factory, $fieldName, $userPermission);
    }

    /**
     * @param string $tableName
     * @param string $fieldName
     * @param int $userPermission
     * @return OnlyValueComponent
     * @throws \Exception
     */
    private function createOnlyValueComponent(string $tableName, string $fieldName, int $userPermission): OnlyValueComponent {
        $factory = $this->loadService($tableName, $fieldName);
        return new OnlyValueComponent($this->translator, $factory, $fieldName, $userPermission);
    }

    /**
     * @param string $name
     * @param int $permissionLevel
     * @return AbstractRowComponent|null
     * @throws \Exception
     * @deprecated
     */
    public function createComponent(string $name, int $permissionLevel) {
        $parts = \explode('__', $name);
        if (\count($parts) === 3) {
            list($prefix, $tableName, $fieldName) = $parts;
            if ($prefix === 'valuePrinter' || $prefix === 'valuePrinterDetail' || $prefix === 'valuePrinterRow') {
                return $this->createRowComponent($tableName, $fieldName, $permissionLevel);
            }
            if ($prefix === 'valuePrinterStalking' || $prefix === 'valuePrinterList') {
                return $this->createListComponent($tableName, $fieldName, $permissionLevel);
            }
            if ($prefix === 'valuePrinterOnlyValue') {
                return $this->createOnlyValueComponent($tableName, $fieldName, $permissionLevel);
            }
        }
        return null;
    }

    /**
     * @param string $tableName
     * @param string $fieldName
     * @param AbstractModelSingle $modelSingle
     * @param int $userPermissionLevel
     * @return \Nette\Utils\Html
     * @throws \Exception
     */
    public function createGridValue(string $tableName, string $fieldName, AbstractModelSingle $modelSingle, int $userPermissionLevel): Html {
        return $this->loadService($tableName, $fieldName)->renderValue($modelSingle, $fieldName, $userPermissionLevel);
    }
}
