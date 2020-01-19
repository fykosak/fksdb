<?php

namespace FKSDB\Config\Extensions;

use FKSDB\Components\DatabaseReflection\Links\Link;
use FKSDB\Components\DatabaseReflection\PrimaryKeyRow;
use FKSDB\Components\DatabaseReflection\StringRow;
use FKSDB\Components\DatabaseReflection\Tables\PhoneRow;
use Nette\Application\BadRequestException;
use Nette\Config\CompilerExtension;
use Nette\DI\ContainerBuilder;
use Nette\DI\ServiceDefinition;
use Nette\NotImplementedException;
use stdClass;

/**
 * Class StalkingExtension
 * @package FKSDB\Config\Extensions
 */
class DBReflectionExtension extends CompilerExtension {
    /**
     * @throws BadRequestException
     */
    public function loadConfiguration() {
        $builder = $this->getContainerBuilder();

        foreach ($this->config['tables'] as $tableName => $fieldDefinitions) {

            if (isset($fieldDefinitions['fields'])) {
                $fields = $fieldDefinitions['fields'];
            } else {
                $fields = $fieldDefinitions;
            }

            foreach ($fields as $fieldName => $field) {
                $factory = $this->createField($builder, $tableName, $fieldName, $field);
                if (isset($fieldDefinitions['referencedAccess'])) {
                    $factory->addSetup('setReferencedParams', [$fieldDefinitions['modelClassName'], $fieldDefinitions['referencedAccess']]);
                }
            }
        }
        foreach ($this->config['links'] as $linkId => $def) {
            if (is_array($def)) {
                $builder->addDefinition($this->prefix('link.' . $linkId))
                    ->setFactory(Link::class)
                    ->addSetup('setParams', [$def['destination'], $def['params'], $def['title'], $def['model']]);
            } else if (is_string($def)) {
                $builder->addDefinition($this->prefix('link.' . $linkId))
                    ->setFactory($def);
            }
        }
    }

    /**
     * @param ContainerBuilder $builder
     * @param string $tableName
     * @param string $fieldName
     * @param array|string $field
     * @return ServiceDefinition
     * @throws BadRequestException
     */
    private function createField(ContainerBuilder $builder, $tableName, $fieldName, $field): ServiceDefinition {
        $factory = null;
        if (is_array($field)) {
            switch ($field['type']) {
                case 'string':
                    return $this->registerStringRow($builder, $tableName, $fieldName, $field);
                case 'primaryKey':
                    return $this->registerPrimaryKeyRow($builder, $tableName, $fieldName, $field);
                case 'phone':
                    return $this->registerPhoneRow($builder, $tableName, $fieldName, $field);
                default:
                    throw new NotImplementedException();
            }
        }
        if (is_string($field) && preg_match('/([A-Za-z0-9]+\\\\)*/', $field)) {
            return $builder->addDefinition($this->prefix($tableName . '.' . $fieldName))
                ->setFactory($field);
        }
        throw new BadRequestException('Expected string or array give ' . get_class($field));
    }

    /**
     * @param ContainerBuilder $builder
     * @param string $tableName
     * @param string $fieldName
     * @param array $field
     * @return ServiceDefinition
     */
    private function registerStringRow(ContainerBuilder $builder, string $tableName, string $fieldName, array $field) {
        $factory = $builder->addDefinition($this->prefix($tableName . '.' . $fieldName))
            ->setFactory(StringRow::class)
            ->addSetup('setUp', [
                $tableName,
                $this->translate($field['title']),
                isset($field['accessKey']) ? $field['accessKey'] : $fieldName,
                isset($field['description']) ? $this->translate($field['description']) : null
            ]);
        if (isset($field['permission'])) {
            $factory->addSetup('setPermissionValue', $field['permission']);
        }
        return $factory;
    }

    /**
     * @param ContainerBuilder $builder
     * @param string $tableName
     * @param string $fieldName
     * @param array $field
     * @return ServiceDefinition
     */
    private function registerPrimaryKeyRow(ContainerBuilder $builder, string $tableName, string $fieldName, array $field) {
        return $this->setUpDefaultFactory($builder, $tableName, $fieldName, PrimaryKeyRow::class, $field);
    }

    /**
     * @param ContainerBuilder $builder
     * @param string $tableName
     * @param string $fieldName
     * @param array $field
     * @return ServiceDefinition
     */
    private function registerPhoneRow(ContainerBuilder $builder, string $tableName, string $fieldName, array $field) {
        $factory = $this->setUpDefaultFactory($builder, $tableName, $fieldName, PhoneRow::class, $field);
        if (isset($field['writeOnly'])) {
            $factory->addSetup('setWriteOnly', $field['writeOnly']);
        }
        return $factory;
    }

    /**
     * @param $value
     * @return mixed
     */
    private function translate($value): string {
        if (is_string($value)) {
            return $value;
        }
        if ($value instanceof stdClass) {
            return ($value->value)(...$value->attributes);
        }
        throw new NotImplementedException();
    }

    /**
     * @param ContainerBuilder $builder
     * @param string $tableName
     * @param string $fieldName
     * @param string $factoryClassName
     * @param array $field
     * @return ServiceDefinition
     */
    private function setUpDefaultFactory(ContainerBuilder $builder, string $tableName, string $fieldName, string $factoryClassName, array $field): ServiceDefinition {
        $factory = $builder->addDefinition($this->prefix($tableName . '.' . $fieldName))
            ->setFactory($factoryClassName)
            ->addSetup('setUp', [
                $tableName,
                isset($field['accessKey']) ? $field['accessKey'] : $fieldName,
                [], // TODO load metadata here
                $this->translate($field['title']),
                isset($field['description']) ? $this->translate($field['description']) : null
            ]);
        if (isset($field['permission'])) {
            $factory->addSetup('setPermissionValue', $field['permission']);
        }
        return $factory;
    }
}
