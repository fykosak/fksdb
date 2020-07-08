<?php

namespace FKSDB\Config\Extensions;

use FKSDB\Components\DatabaseReflection\ColumnFactories\{DateRow,
    DateTimeRow,
    EmailColumnFactory,
    IntColumnFactory,
    LogicColumnFactory,
    PrimaryKeyColumnFactory,
    StateColumnFactory,
    StringColumnFactory,
    PhoneColumnFactory,
    TextColumnFactory,
    TimeRow
};
use FKSDB\Components\DatabaseReflection\DetailFactory;
use FKSDB\Components\DatabaseReflection\LinkFactories\Link;
use FKSDB\Components\DatabaseReflection\ReferencedFactory;
use Nette\DI\CompilerExtension;
use Nette\DI\ServiceDefinition;
use Nette\DI\Statement;
use FKSDB\Exceptions\NotImplementedException;

/**
 * Class DBReflectionExtension
 * @author Michal Červeňák <miso@fykos.cz>
 */
class DBReflectionExtension extends CompilerExtension {
    /**
     * @throws NotImplementedException
     */
    public function loadConfiguration() {
        $this->registerFactories($this->config['tables']);
        $this->registerDetails($this->config['details']);
    }

    /**
     * @param array $details
     * @return void
     */
    private function registerDetails(array $details) {
        $builder = $this->getContainerBuilder();
        $builder->addDefinition($this->prefix('detailFactory'))
            ->setFactory(DetailFactory::class)
            ->addSetup('setNodes', [$details]);
    }

    /**
     * @param array $tables
     * @throws NotImplementedException
     */
    private function registerFactories(array $tables) {
        foreach ($tables as $tableName => $fieldDefinitions) {
            $referencedFactory = $this->createReferencedFactory($tableName, $fieldDefinitions);

            foreach ($fieldDefinitions['columnFactories'] as $fieldName => $field) {
                $factory = $this->createColumnFactory($tableName, $fieldName, $field);
                $factory->addSetup('setReferencedFactory', [$referencedFactory]);
            }
            foreach ($fieldDefinitions['linkFactories'] as $fieldName => $field) {
                $factory = $this->createLinkFactory($tableName, $fieldName, $field);
                $factory->addSetup('setReferencedFactory', [$referencedFactory]);
            }
        }
    }

    private function createReferencedFactory(string $tableName, array $fieldDefinitions): ServiceDefinition {
        $builder = $this->getContainerBuilder();
        $factory = $builder->addDefinition($this->prefix('referencedFactory.' . $tableName));
        $factory->setFactory(ReferencedFactory::class, [$fieldDefinitions['modelClassName'], $fieldDefinitions['referencedAccess'] ?? null]);
        return $factory;
    }

    /**
     * @param string $tableName
     * @param string $linkId
     * @param string|array $def
     * @return ServiceDefinition
     */
    private function createLinkFactory(string $tableName, string $linkId, $def): ServiceDefinition {
        $builder = $this->getContainerBuilder();
        $factory = $builder->addDefinition($this->prefix('link.' . $tableName . '.' . $linkId));
        if (is_array($def)) {
            $factory->setFactory(Link::class, [$def['destination'], $def['params'], $def['title'], $def['model']]);
        } elseif (is_string($def)) {
            $factory->setFactory($def);
        }
        return $factory;
    }

    /**
     * @param string $tableName
     * @param string $fieldName
     * @param array|string $field
     * @return ServiceDefinition
     * @throws NotImplementedException
     */
    private function createColumnFactory(string $tableName, string $fieldName, $field): ServiceDefinition {
        $builder = $this->getContainerBuilder();
        $factory = $builder->addDefinition($this->prefix('column.' . $tableName . '.' . $fieldName));
        if (is_array($field)) {
            switch ($field['type']) {
                case 'primaryKey':
                    $this->registerPrimaryKeyRow($factory, $tableName, $fieldName, $field);
                    break;
                case 'string':
                    $this->registerStringRow($factory, $tableName, $fieldName, $field);
                    break;
                case 'text':
                    $this->registerTextRow($factory, $tableName, $fieldName, $field);
                    break;
                case 'dateTime':
                    $this->registerDateTimeRow($factory, $tableName, $fieldName, $field);
                    break;
                case 'date':
                    $this->registerDateRow($factory, $tableName, $fieldName, $field);
                    break;
                case 'time':
                    $this->registerTimeRow($factory, $tableName, $fieldName, $field);
                    break;
                case 'phone':
                    $this->registerPhoneRow($factory, $tableName, $fieldName, $field);
                    break;
                case 'email':
                    $this->registerEmailRow($factory, $tableName, $fieldName, $field);
                    break;
                case 'state':
                    $this->registerStateRow($factory, $tableName, $fieldName, $field);
                    break;
                case 'int':
                    $this->registerIntRow($factory, $tableName, $fieldName, $field);
                    break;
                case 'logic':
                    $this->registerLogicRow($factory, $tableName, $fieldName, $field);
                    break;
                default:
                    throw new NotImplementedException();
            }
        } elseif (is_string($field) && preg_match('/([A-Za-z0-9]+\\\\)*/', $field)) {
            $factory->setFactory($field);
        }
        return $factory;
    }

    /**
     * @param ServiceDefinition $factory
     * @param string $tableName
     * @param string $fieldName
     * @param array $field
     * @return void
     */
    private function registerStateRow(ServiceDefinition $factory, string $tableName, string $fieldName, array $field) {
        $this->setUpDefaultFactory($factory, $tableName, $fieldName, StateColumnFactory::class, $field);
        $factory->addSetup('setStates', [$field['states']]);
    }

    /**
     * @param ServiceDefinition $factory
     * @param string $tableName
     * @param string $fieldName
     * @param array $field
     * @return void
     */
    private function registerIntRow(ServiceDefinition $factory, string $tableName, string $fieldName, array $field) {
        $this->setUpDefaultFactory($factory, $tableName, $fieldName, IntColumnFactory::class, $field);
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

    /**
     * @param ServiceDefinition $factory
     * @param string $tableName
     * @param string $fieldName
     * @param array $field
     * @return void
     */
    private function registerStringRow(ServiceDefinition $factory, string $tableName, string $fieldName, array $field) {
        $this->setUpDefaultFactory($factory, $tableName, $fieldName, StringColumnFactory::class, $field);
    }

    /**
     * @param ServiceDefinition $factory
     * @param string $tableName
     * @param string $fieldName
     * @param array $field
     * @return void
     */
    private function registerLogicRow(ServiceDefinition $factory, string $tableName, string $fieldName, array $field) {
        $this->setUpDefaultFactory($factory, $tableName, $fieldName, LogicColumnFactory::class, $field);
    }

    /**
     * @param ServiceDefinition $factory
     * @param string $tableName
     * @param string $fieldName
     * @param array $field
     * @return void
     */
    private function registerTextRow(ServiceDefinition $factory, string $tableName, string $fieldName, array $field) {
        $this->setUpDefaultFactory($factory, $tableName, $fieldName, TextColumnFactory::class, $field);
    }

    /**
     * @param ServiceDefinition $factory
     * @param string $tableName
     * @param string $fieldName
     * @param array $field
     * @return void
     */
    private function registerDateTimeRow(ServiceDefinition $factory, string $tableName, string $fieldName, array $field) {
        $this->registerAbstractDateTimeRow($factory, $tableName, $fieldName, DateTimeRow::class, $field);
    }

    /**
     * @param ServiceDefinition $factory
     * @param string $tableName
     * @param string $fieldName
     * @param array $field
     * @return void
     */
    private function registerDateRow(ServiceDefinition $factory, string $tableName, string $fieldName, array $field) {
        $this->registerAbstractDateTimeRow($factory, $tableName, $fieldName, DateRow::class, $field);
    }

    /**
     * @param ServiceDefinition $factory
     * @param string $tableName
     * @param string $fieldName
     * @param array $field
     * @return void
     */
    private function registerTimeRow(ServiceDefinition $factory, string $tableName, string $fieldName, array $field) {
        $this->registerAbstractDateTimeRow($factory, $tableName, $fieldName, TimeRow::class, $field);
    }

    /**
     * @param ServiceDefinition $factory
     * @param string $tableName
     * @param string $fieldName
     * @param string $factoryClassName
     * @param array $field
     * @return void
     */
    private function registerAbstractDateTimeRow(ServiceDefinition $factory, string $tableName, string $fieldName, string $factoryClassName, array $field) {
        $this->setUpDefaultFactory($factory, $tableName, $fieldName, $factoryClassName, $field);
        if (isset($field['format'])) {
            $factory->addSetup('setFormat', [$field['format']]);
        }
    }

    /**
     * @param ServiceDefinition $factory
     * @param string $tableName
     * @param string $fieldName
     * @param array $field
     * @return void
     */
    private function registerPrimaryKeyRow(ServiceDefinition $factory, string $tableName, string $fieldName, array $field) {
        $this->setUpDefaultFactory($factory, $tableName, $fieldName, PrimaryKeyColumnFactory::class, $field);
    }

    /**
     * @param ServiceDefinition $factory
     * @param string $tableName
     * @param string $fieldName
     * @param array $field
     * @return void
     */
    private function registerPhoneRow(ServiceDefinition $factory, string $tableName, string $fieldName, array $field) {
        $this->setUpDefaultFactory($factory, $tableName, $fieldName, PhoneColumnFactory::class, $field);
        if (isset($field['writeOnly'])) {
            $factory->addSetup('setWriteOnly', [$field['writeOnly']]);
        }
    }

    /**
     * @param ServiceDefinition $factory
     * @param string $tableName
     * @param string $fieldName
     * @param array $field
     * @return void
     */
    private function registerEmailRow(ServiceDefinition $factory, string $tableName, string $fieldName, array $field) {
        $this->setUpDefaultFactory($factory, $tableName, $fieldName, EmailColumnFactory::class, $field);
    }

    /**
     * @param string|Statement $value
     * @return string
     */
    private function translate($value): string {
        if ($value instanceof Statement) {
            return ($value->entity)(...$value->arguments);
        }
        return $value;
    }

    /**
     * @param ServiceDefinition $factory
     * @param string $tableName
     * @param string $fieldName
     * @param string $factoryClassName
     * @param array $field
     * @return void
     */
    private function setUpDefaultFactory(ServiceDefinition $factory, string $tableName, string $fieldName, string $factoryClassName, array $field) {
        $factory->setFactory($factoryClassName)
            ->addSetup('setUp', [
                $tableName,
                isset($field['accessKey']) ? $field['accessKey'] : $fieldName,
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
