<?php

namespace FKSDB\Config\Extensions;

use FKSDB\Components\DatabaseReflection\ColumnFactories\{
    DateRow,
    DateTimeRow,
    EmailColumnFactory,
    IntColumnFactory,
    PrimaryKeyColumnFactory,
    StateColumnFactory,
    StringColumnFactory,
    PhoneColumnFactory,
    TextColumnFactory,
    TimeRow
};
use FKSDB\Components\DatabaseReflection\DetailFactory;
use FKSDB\Components\DatabaseReflection\LinkFactories\Link;
use Nette\Application\BadRequestException;
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
     * @throws BadRequestException
     */
    public function loadConfiguration() {
        $this->registerTables($this->config['tables']);
        $this->registerLinks($this->config['links']);
        $this->registerDetails($this->config['details']);
    }

    /**
     * @param array $tables
     * @throws BadRequestException
     */
    private function registerTables(array $tables) {
        foreach ($tables as $tableName => $fieldDefinitions) {

            if (isset($fieldDefinitions['fields'])) {
                $fields = $fieldDefinitions['fields'];
            } else {
                $fields = $fieldDefinitions;
            }

            foreach ($fields as $fieldName => $field) {
                $factory = $this->createField($tableName, $fieldName, $field);
                if (isset($fieldDefinitions['referencedAccess'])) {
                    $factory->addSetup('setReferencedParams', [$fieldDefinitions['modelClassName'], $fieldDefinitions['referencedAccess']]);
                }
            }
        }
    }

    /**
     * @param array $links
     * @return void
     */
    private function registerLinks(array $links) {
        $builder = $this->getContainerBuilder();
        foreach ($links as $linkId => $def) {
            if (is_array($def)) {
                $builder->addDefinition($this->prefix('link.' . $linkId))
                    ->setFactory(Link::class)
                    ->addSetup('setParams', [$def['destination'], $def['params'], $def['title'], $def['model']]);
            } elseif (is_string($def)) {
                $builder->addDefinition($this->prefix('link.' . $linkId))
                    ->setFactory($def);
            }
        }
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
     * @param string $tableName
     * @param string $fieldName
     * @param array|string $field
     * @return ServiceDefinition
     * @throws BadRequestException
     */
    private function createField(string $tableName, string $fieldName, $field): ServiceDefinition {
        $builder = $this->getContainerBuilder();
        $factory = null;
        if (is_array($field)) {
            switch ($field['type']) {
                case 'primaryKey':
                    return $this->registerPrimaryKeyRow($tableName, $fieldName, $field);
                case 'string':
                    return $this->registerStringRow($tableName, $fieldName, $field);
                case 'text':
                    return $this->registerTextRow($tableName, $fieldName, $field);
                case 'dateTime':
                    return $this->registerDateTimeRow($tableName, $fieldName, $field);
                case 'date':
                    return $this->registerDateRow($tableName, $fieldName, $field);
                case 'time':
                    return $this->registerTimeRow($tableName, $fieldName, $field);
                case 'phone':
                    return $this->registerPhoneRow($tableName, $fieldName, $field);
                case 'email':
                    return $this->registerEmailRow($tableName, $fieldName, $field);
                case 'state':
                    return $this->registerStateRow($tableName, $fieldName, $field);
                case 'int':
                    return $this->registerIntRow($tableName, $fieldName, $field);
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

    private function registerStateRow(string $tableName, string $fieldName, array $field): ServiceDefinition {
        $factory = $this->setUpDefaultFactory($tableName, $fieldName, StateColumnFactory::class, $field);
        $factory->addSetup('setStates', [$field['states']]);
        return $factory;
    }

    private function registerIntRow(string $tableName, string $fieldName, array $field): ServiceDefinition {
        $factory = $this->setUpDefaultFactory($tableName, $fieldName, IntColumnFactory::class, $field);
        if (isset($field['nullValueFormat'])) {
            $factory->addSetup('setNullValueFormat', [$field['nullValueFormat']]);
        }
        if (isset($field['prefix'])) {
            $factory->addSetup('setPrefix', [$field['prefix']]);
        }
        if (isset($field['suffix'])) {
            $factory->addSetup('setSuffix', [$field['suffix']]);
        }
        return $factory;
    }

    private function registerStringRow(string $tableName, string $fieldName, array $field): ServiceDefinition {
        return $this->setUpDefaultFactory($tableName, $fieldName, StringColumnFactory::class, $field);
    }

    private function registerTextRow(string $tableName, string $fieldName, array $field): ServiceDefinition {
        return $this->setUpDefaultFactory($tableName, $fieldName, TextColumnFactory::class, $field);
    }

    private function registerDateTimeRow(string $tableName, string $fieldName, array $field): ServiceDefinition {
        return $this->registerAbstractDateTimeRow($tableName, $fieldName, DateTimeRow::class, $field);
    }

    private function registerDateRow(string $tableName, string $fieldName, array $field): ServiceDefinition {
        return $this->registerAbstractDateTimeRow($tableName, $fieldName, DateRow::class, $field);
    }

    private function registerTimeRow(string $tableName, string $fieldName, array $field): ServiceDefinition {
        return $this->registerAbstractDateTimeRow($tableName, $fieldName, TimeRow::class, $field);
    }

    private function registerAbstractDateTimeRow(string $tableName, string $fieldName, string $factoryClassName, array $field): ServiceDefinition {
        $factory = $this->setUpDefaultFactory($tableName, $fieldName, $factoryClassName, $field);
        if (isset($field['format'])) {
            $factory->addSetup('setFormat', [$field['format']]);
        }
        return $factory;
    }

    private function registerPrimaryKeyRow(string $tableName, string $fieldName, array $field): ServiceDefinition {
        return $this->setUpDefaultFactory($tableName, $fieldName, PrimaryKeyColumnFactory::class, $field);
    }

    private function registerPhoneRow(string $tableName, string $fieldName, array $field): ServiceDefinition {
        $factory = $this->setUpDefaultFactory($tableName, $fieldName, PhoneColumnFactory::class, $field);
        if (isset($field['writeOnly'])) {
            $factory->addSetup('setWriteOnly', [$field['writeOnly']]);
        }
        return $factory;
    }

    private function registerEmailRow(string $tableName, string $fieldName, array $field): ServiceDefinition {
        return $this->setUpDefaultFactory($tableName, $fieldName, EmailColumnFactory::class, $field);
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

    private function setUpDefaultFactory(string $tableName, string $fieldName, string $factoryClassName, array $field): ServiceDefinition {
        $builder = $this->getContainerBuilder();
        $factory = $builder->addDefinition($this->prefix($tableName . '.' . $fieldName))
            ->setFactory($factoryClassName)
            ->addSetup('setUp', [
                $tableName,
                isset($field['accessKey']) ? $field['accessKey'] : $fieldName,
                $this->translate($field['title']),
                isset($field['description']) ? $this->translate($field['description']) : null,
            ]);
        if (isset($field['permission'])) {
            $factory->addSetup('setPermissionValue', [$field['permission']]);
        }
        if (isset($field['omitInputField'])) {
            $factory->addSetup('setOmitInputField', [$field['omitInputField']]);
        }
        if (isset($field['required'])) {
            $factory->addSetup('setRequired', [$field['required']]);
        }
        return $factory;
    }
}
