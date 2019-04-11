<?php

namespace FKSDB\Components\Forms\Factories;

use FKSDB\Components\DatabaseReflection\AbstractRow;
use FKSDB\Components\DatabaseReflection\DetailRowComponent;
use FKSDB\Components\DatabaseReflection\StalkingRowComponent;
use FKSDB\ORM\AbstractModelSingle;
use Nette\DI\Container;
use Nette\Forms\Controls\BaseControl;
use Nette\InvalidArgumentException;
use Nette\Localization\ITranslator;
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
     * @return StalkingRowComponent
     * @throws \Exception
     */
    public function createStalkingComponent(string $tableName, string $fieldName, int $userPermission): StalkingRowComponent {
        $factory = $this->loadService($tableName, $fieldName);
        $callBack = function (AbstractModelSingle $model) use ($factory, $fieldName, $userPermission) {
            return $factory->renderValue($model, $fieldName, $userPermission);
        };
        return new StalkingRowComponent($this->translator, $callBack, $factory::getTitle());
    }

    /**
     * @param string $tableName
     * @param string $fieldName
     * @param int $userPermission
     * @return DetailRowComponent
     * @throws \Exception
     */
    public function createDetailComponent(string $tableName, string $fieldName, int $userPermission): DetailRowComponent {
        $factory = $this->loadService($tableName, $fieldName);
        $callBack = function (AbstractModelSingle $model) use ($factory, $fieldName, $userPermission) {
            return $factory->renderValue($model, $fieldName, $userPermission);
        };
        return new DetailRowComponent($this->translator, $callBack, $factory::getTitle());
    }

    /**
     * @param string $tableName
     * @param string $fieldName
     * @return BaseControl
     * @throws \Exception
     */
    public function createField(string $tableName, string $fieldName): BaseControl {
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
    public function createGridValue(string $tableName, string $fieldName, AbstractModelSingle $modelSingle, int $userPermissionLevel): Html {
        return $this->loadService($tableName, $fieldName)->renderValue($modelSingle, $fieldName, $userPermissionLevel);
    }
}
