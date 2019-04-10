<?php

namespace FKSDB\Components\Forms\Factories;

use FKSDB\Components\Controls\Helpers\ValuePrinters\AbstractValueControl;
use FKSDB\Components\DatabaseReflection\AbstractRow;
use FKSDB\ORM\AbstractModelSingle;
use Nette\DI\Container;
use Nette\Forms\IControl;
use Nette\InvalidArgumentException;
use Nette\Utils\Html;

/**
 * Class TableReflectionFactory
 * @package FKSDB\Components\Forms\Factories\PersonInfo
 */
class TableReflectionFactory {

    /**
     * @var AbstractRow[]
     */
    private $fieldFactories = [];
    /**
     * @var Container
     */
    private $container;

    /**
     * PersonInfoFactory constructor.
     * @param Container $container
     * @throws \Exception
     */
    public function __construct(Container $container) {
        $this->container = $container;

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
        $service = $this->container->getService('field.' . $tableName . '.' . $fieldName);
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
     * @return AbstractValueControl
     * @throws \Exception
     */
    public function createStalkingRow(string $tableName, string $fieldName, int $userPermission): AbstractValueControl {
        return $this->loadService($tableName, $fieldName)->createStalkingRowControl($userPermission);
    }

    /**
     * @param string $tableName
     * @param string $fieldName
     * @return IControl
     * @throws \Exception
     */
    public function createField(string $tableName, string $fieldName): IControl {
        return $this->loadService($tableName, $fieldName)->createField();
    }

    /**
     * @param string $tableName
     * @param string $fieldName
     * @param AbstractModelSingle $modelSingle
     * @param int $userPermissionLevel
     * @return \Nette\Utils\Html
     * @throws \Exception
     */
    public function createGridItem(string $tableName, string $fieldName, AbstractModelSingle $modelSingle, int $userPermissionLevel): Html {
        return $this->loadService($tableName, $fieldName)->createHtmlValue($modelSingle, $fieldName, $userPermissionLevel);
    }
}
