<?php

namespace FKSDB\Models\ORM;

use FKSDB\Models\ORM\Columns\Types\{
    DateTime\DateColumnFactory,
    DateTime\DateTimeColumnFactory,
    EmailColumnFactory,
    IntColumnFactory,
    LogicColumnFactory,
    PrimaryKeyColumnFactory,
    StateColumnFactory,
    StringColumnFactory,
    PhoneColumnFactory,
    TextColumnFactory,
    DateTime\TimeColumnFactory,
};
use FKSDB\Models\ORM\Links\Link;
use Nette\DI\Definitions\ServiceDefinition;
use FKSDB\Models\Exceptions\NotImplementedException;
use Nette\DI\Definitions\Statement;

class ORMExtension extends \Fykosak\NetteORM\ORMExtension {

    /**
     * @throws NotImplementedException
     */
    public function loadConfiguration(): void {
        foreach ($this->config as $tableName => $fieldDefinitions) {
            $this->tryRegisterORMService($tableName, $fieldDefinitions);
            foreach ($fieldDefinitions['columnFactories'] as $fieldName => $field) {
                $this->createColumnFactory($tableName, $fieldDefinitions['model'], $fieldName, $field);
            }
            foreach ($fieldDefinitions['linkFactories'] as $fieldName => $field) {
                $this->createLinkFactory($tableName, $fieldDefinitions['model'], $fieldName, $field);
            }
        }
    }

    private function tryRegisterORMService(string $tableName, array $fieldDefinitions): void {
        if (isset($fieldDefinitions['service'])) {
            $builder = $this->getContainerBuilder();
            $factory = $builder->addDefinition($this->prefix($tableName . '.service'));
            $factory->setFactory($fieldDefinitions['service'], [$tableName, $fieldDefinitions['model']]);
        }
    }

    /**
     * @param string|array $def
     */
    private function createLinkFactory(string $tableName, string $modelClassName, string $linkId, $def): ServiceDefinition {
        $builder = $this->getContainerBuilder();
        $factory = $builder->addDefinition($this->prefix($tableName . '.link.' . $linkId));
        if (is_array($def)) {
            $factory->setFactory(Link::class, [$def['destination'], $def['params'], $def['title'], $modelClassName]);
        } elseif (is_string($def)) {
            $factory->setFactory($def);
        }
        return $factory;
    }

    /**
     * @param array|string $field
     * @throws NotImplementedException
     */
    private function createColumnFactory(string $tableName, string $modelClassName, string $fieldName, $field): ServiceDefinition {
        $builder = $this->getContainerBuilder();
        $factory = $builder->addDefinition($this->prefix($tableName . '.column.' . $fieldName));
        if (is_array($field)) {
            switch ($field['type']) {
                case 'primaryKey':
                    $this->registerPrimaryKeyRow($factory, $tableName, $modelClassName, $fieldName, $field);
                    break;
                case 'string':
                    $this->registerStringRow($factory, $tableName, $modelClassName, $fieldName, $field);
                    break;
                case 'text':
                    $this->registerTextRow($factory, $tableName, $modelClassName, $fieldName, $field);
                    break;
                case 'dateTime':
                    $this->registerDateTimeRow($factory, $tableName, $modelClassName, $fieldName, $field);
                    break;
                case 'date':
                    $this->registerDateRow($factory, $tableName, $modelClassName, $fieldName, $field);
                    break;
                case 'time':
                    $this->registerTimeRow($factory, $tableName, $modelClassName, $fieldName, $field);
                    break;
                case 'phone':
                    $this->registerPhoneRow($factory, $tableName, $modelClassName, $fieldName, $field);
                    break;
                case 'email':
                    $this->registerEmailRow($factory, $tableName, $modelClassName, $fieldName, $field);
                    break;
                case 'state':
                    $this->registerStateRow($factory, $tableName, $modelClassName, $fieldName, $field);
                    break;
                case 'int':
                    $this->registerIntRow($factory, $tableName, $modelClassName, $fieldName, $field);
                    break;
                case 'logic':
                    $this->registerLogicRow($factory, $tableName, $modelClassName, $fieldName, $field);
                    break;
                case 'class':
                    $this->registerClassColumnFactory($factory, $tableName, $modelClassName, $fieldName, $field);
                    break;
                default:
                    throw new NotImplementedException();
            }
        } elseif (is_string($field) && preg_match('/([A-Za-z0-9]+\\\\)*/', $field)) {
            $factory->setFactory($field);
        }
        return $factory;
    }

    private function registerClassColumnFactory(ServiceDefinition $factory, string $tableName, string $modelClassName, string $fieldName, array $field): void {
        $this->setUpDefaultFactory($factory, $tableName, $modelClassName, $fieldName, $field['class'], $field);
    }

    private function registerStateRow(ServiceDefinition $factory, string $tableName, string $modelClassName, string $fieldName, array $field): void {
        $this->setUpDefaultFactory($factory, $tableName, $modelClassName, $fieldName, StateColumnFactory::class, $field);
        $factory->addSetup('setStates', [$field['states']]);
    }

    private function registerIntRow(ServiceDefinition $factory, string $tableName, string $modelClassName, string $fieldName, array $field): void {
        $this->setUpDefaultFactory($factory, $tableName, $modelClassName, $fieldName, IntColumnFactory::class, $field);
        if (isset($field['nullValueFormat'])) {
            $factory->addSetup('setNullValueFormat', [$field['nullValueFormat']]);
        }
        if (isset($field['prefix'])) {
            $factory->addSetup('setPrefix', [$field['prefix']]);
        }
        if (isset($field['suffix'])) {
            $factory->addSetup('setSuffix', [$field['suffix']]);
        }
    }

    private function registerStringRow(ServiceDefinition $factory, string $tableName, string $modelClassName, string $fieldName, array $field): void {
        $this->setUpDefaultFactory($factory, $tableName, $modelClassName, $fieldName, StringColumnFactory::class, $field);
    }

    private function registerLogicRow(ServiceDefinition $factory, string $tableName, string $modelClassName, string $fieldName, array $field): void {
        $this->setUpDefaultFactory($factory, $tableName, $modelClassName, $fieldName, LogicColumnFactory::class, $field);
    }

    private function registerTextRow(ServiceDefinition $factory, string $tableName, string $modelClassName, string $fieldName, array $field): void {
        $this->setUpDefaultFactory($factory, $tableName, $modelClassName, $fieldName, TextColumnFactory::class, $field);
    }

    private function registerDateTimeRow(ServiceDefinition $factory, string $tableName, string $modelClassName, string $fieldName, array $field): void {
        $this->registerAbstractDateTimeRow($factory, $tableName, $modelClassName, $fieldName, DateTimeColumnFactory::class, $field);
    }

    private function registerDateRow(ServiceDefinition $factory, string $tableName, string $modelClassName, string $fieldName, array $field): void {
        $this->registerAbstractDateTimeRow($factory, $tableName, $modelClassName, $fieldName, DateColumnFactory::class, $field);
    }

    private function registerTimeRow(ServiceDefinition $factory, string $tableName, string $modelClassName, string $fieldName, array $field): void {
        $this->registerAbstractDateTimeRow($factory, $tableName, $modelClassName, $fieldName, TimeColumnFactory::class, $field);
    }

    private function registerAbstractDateTimeRow(ServiceDefinition $factory, string $tableName, string $modelClassName, string $fieldName, string $factoryClassName, array $field): void {
        $this->setUpDefaultFactory($factory, $tableName, $modelClassName, $fieldName, $factoryClassName, $field);
        if (isset($field['format'])) {
            $factory->addSetup('setFormat', [$field['format']]);
        }
    }

    private function registerPrimaryKeyRow(ServiceDefinition $factory, string $tableName, string $modelClassName, string $fieldName, array $field): void {
        $this->setUpDefaultFactory($factory, $tableName, $modelClassName, $fieldName, PrimaryKeyColumnFactory::class, $field);
    }

    private function registerPhoneRow(ServiceDefinition $factory, string $tableName, string $modelClassName, string $fieldName, array $field): void {
        $this->setUpDefaultFactory($factory, $tableName, $modelClassName, $fieldName, PhoneColumnFactory::class, $field);
        if (isset($field['writeOnly'])) {
            $factory->addSetup('setWriteOnly', [$field['writeOnly']]);
        }
    }

    private function registerEmailRow(ServiceDefinition $factory, string $tableName, string $modelClassName, string $fieldName, array $field): void {
        $this->setUpDefaultFactory($factory, $tableName, $modelClassName, $fieldName, EmailColumnFactory::class, $field);
    }

    /**
     * @param string|Statement $value
     */
    private function translate($value): string {
        if ($value instanceof Statement) {
            return ($value->entity)(...$value->arguments);
        }
        return $value;
    }

    private function setUpDefaultFactory(ServiceDefinition $factory, string $tableName, string $modelClassName, string $fieldName, string $factoryClassName, array $field): void {
        $factory->setFactory($factoryClassName);
        $factory->addSetup('setUp', [
            $tableName,
            $modelClassName,
            $field['accessKey'] ?? $fieldName,
            $this->translate($field['title']),
            isset($field['description']) ? $this->translate($field['description']) : null,
        ]);
        if (isset($field['permission'])) {
            if (is_array($field['permission'])) {
                $permission = $field['permission'];
            } else {
                $permission = ['read' => $field['permission'], 'write' => $field['permission']];
            }
            $factory->addSetup('setPermissionValue', [$permission]);
        }
        if (isset($field['omitInputField'])) {
            $factory->addSetup('setOmitInputField', [$field['omitInputField']]);
        }
        if (isset($field['required'])) {
            $factory->addSetup('setRequired', [$field['required']]);
        }
    }
}
