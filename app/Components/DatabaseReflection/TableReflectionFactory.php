<?php

namespace FKSDB\Components\Forms\Factories;

use Closure;
use FKSDB\Components\DatabaseReflection\AbstractRow;
use FKSDB\Components\DatabaseReflection\RowComponent;
use FKSDB\Components\DatabaseReflection\OnlyValueComponent;
use FKSDB\Components\DatabaseReflection\ListComponent;
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
    public function createListComponent(string $tableName, string $fieldName, int $userPermission): ListComponent {
        $factory = $this->loadService($tableName, $fieldName);
        $callBack = $this->getComponentCallback($factory, $fieldName, $userPermission);
        return new ListComponent($this->translator, $callBack, $factory::getTitle());
    }

    /**
     * @param string $tableName
     * @param string $fieldName
     * @param int $userPermission
     * @return RowComponent
     * @throws \Exception
     */
    public function createRowComponent(string $tableName, string $fieldName, int $userPermission): RowComponent {
        $factory = $this->loadService($tableName, $fieldName);
        $callBack = $this->getComponentCallback($factory, $fieldName, $userPermission);
        return new RowComponent($this->translator, $callBack, $factory::getTitle());
    }

    /**
     * @param string $tableName
     * @param string $fieldName
     * @param int $userPermission
     * @return OnlyValueComponent
     * @throws \Exception
     */
    public function createOnlyValueComponent(string $tableName, string $fieldName, int $userPermission): OnlyValueComponent {
        $factory = $this->loadService($tableName, $fieldName);
        $callBack = $this->getComponentCallback($factory, $fieldName, $userPermission);
        return new OnlyValueComponent($this->translator, $callBack, $factory::getTitle());
    }

    /**
     * @param AbstractRow $factory
     * @param string $fieldName
     * @param int $userPermission
     * @return \Closure
     */
    private function getComponentCallback(AbstractRow $factory, string $fieldName, int $userPermission): Closure {
        return function (AbstractModelSingle $model) use ($factory, $fieldName, $userPermission): Html {
            return $factory->renderValue($model, $fieldName, $userPermission);
        };
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
