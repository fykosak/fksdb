<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM;

use FKSDB\Models\Exceptions\NotImplementedException;
use FKSDB\Models\ORM\Columns\ColumnFactory;
use FKSDB\Models\ORM\Columns\Types\{DateTime\DateColumnFactory,
    DateTime\DateTimeColumnFactory,
    DateTime\TimeColumnFactory,
    EmailColumnFactory,
    EnumColumn,
    EnumColumnFactory,
    FloatColumnFactory,
    IntColumnFactory,
    LocalizedStringColumnFactory,
    LogicColumnFactory,
    PhoneColumnFactory,
    PrimaryKeyColumnFactory,
    StringColumnFactory,
    TextColumnFactory
};
use FKSDB\Models\ORM\Links\Link;
use FKSDB\Models\Utils\FakeStringEnum;
use Fykosak\NetteORM\Extension;
use Nette\DI\Definitions\ServiceDefinition;
use Nette\DI\Definitions\Statement;
use Nette\Schema\Elements\AnyOf;
use Nette\Schema\Elements\Structure;
use Nette\Schema\Expect;
use Nette\Schema\Schema;

/**
 * @phpstan-type TCommonParams array{
 *     permission: string,
 *     accessKey?:string,
 *     omitInputField:bool,
 *     required:bool,
 *     description?:string,
 *     writeOnly:bool,
 *     title:string,
 * }
 */
class ORMExtension extends Extension
{

    /**
     * @throws NotImplementedException
     */
    public function loadConfiguration(): void
    {
        parent::loadConfiguration();
        foreach ($this->config as $tableName => $fieldDefinitions) {//@phpstan-ignore-line
            foreach ($fieldDefinitions['columns'] as $fieldName => $field) {
                $this->createColumnFactory($tableName, $fieldDefinitions['model'], $fieldName, $field);
            }
            foreach ($fieldDefinitions['links'] as $fieldName => $field) {
                $this->createLinkFactory($tableName, $fieldDefinitions['model'], $fieldName, $field);
            }
        }
    }

    /**
     * @phpstan-param array<string,Schema> $items
     */
    private function createDefaultStructure(AnyOf $type, array $items = []): Structure
    {
        return Expect::structure(
            array_merge([
                'type' => $type->required(),
                'title' => Expect::type(Statement::class)->required(),
                'accessKey' => Expect::string(),
                'description' => Expect::type(Statement::class),
                'required' => Expect::bool(false),
                'omitInputField' => Expect::bool(false),
                'writeOnly' => Expect::bool(false),
                'permission' => Expect::anyOf('ANYBODY', 'BASIC', 'RESTRICT', 'FULL')->firstIsDefault(),
            ], $items)
        )->castTo('array');
    }

    public function getConfigSchema(): Schema
    {
        return Expect::arrayOf(
            Expect::structure([
                'service' => Expect::string()->required(),
                'model' => Expect::string()->required(),
                'columns' => Expect::arrayOf(
                    Expect::anyOf(
                        $this->createDefaultStructure(
                            Expect::anyOf('primaryKey', 'text', 'string', 'bool', 'phone', 'email', 'localizedString')
                        ),
                        $this->createDefaultStructure(Expect::anyOf('dateTime', 'time', 'date'), [
                            'format' => Expect::string(),
                        ]),
                        $this->createDefaultStructure(Expect::anyOf('state'), [
                            'states' => Expect::arrayOf(
                                Expect::structure(['badge' => Expect::string(), 'label' => Expect::string()])
                                    ->castTo('array'),
                                Expect::string()
                            )->required(),
                        ]),
                        $this->createDefaultStructure(Expect::anyOf('class', 'enum'), [
                            'class' => Expect::string()->required(),
                        ]),
                        $this->createDefaultStructure(Expect::anyOf('float'), [
                            'format' => Expect::structure([
                                'decimalDigits' => Expect::int()->required(),
                                'suffix' => Expect::string()->nullable()->default(null),
                                'prefix' => Expect::string()->nullable()->default(null),
                                'nullValue' => Expect::anyOf('infinite', 'notSet', 'zero')->default('notSet'),
                            ])->castTo('array'),

                        ]),
                        $this->createDefaultStructure(Expect::anyOf('int'), [
                            'format' => Expect::structure([
                                'decimalDigits' => Expect::int()->default(0),
                                'suffix' => Expect::string()->nullable()->default(null),
                                'prefix' => Expect::string()->nullable()->default(null),
                                'nullValue' => Expect::anyOf('infinite', 'notSet', 'zero')->default('notSet'),
                            ])->castTo('array'),

                        ]),
                    ),
                    Expect::string()
                ),
                'links' => Expect::arrayOf(
                    Expect::structure([
                        'destination' => Expect::string()->required(),
                        'params' => Expect::arrayOf(Expect::string(), Expect::string()),
                        'title' => Expect::type(Statement::class),
                        'icon' => Expect::string()->required(false),
                    ])->castTo('array')
                ),
            ])->castTo('array'),
            Expect::string()
        )->castTo('array');
    }

    /**
     * @phpstan-param array{destination:string,params:array<string,string>,title:string,icon?:string} $def
     */
    private function createLinkFactory(
        string $tableName,
        string $modelClassName,
        string $linkId,
        array $def
    ): void {
        $builder = $this->getContainerBuilder();
        $factory = $builder->addDefinition($this->prefix($tableName . '.link.' . $linkId));
        $factory->setFactory(
            Link::class,
            [
                $def['destination'],
                $def['params'],
                $this->translate($def['title']),
                $def['icon'] ?? '',
                $modelClassName,
            ]
        );
    }

    /**
     * @throws NotImplementedException
     * @phpstan-param array{
     *     type:string,
     *     permission: string,
     *     accessKey?:string,
     *     omitInputField:bool,
     *     required:bool,
     *     description?:string,
     *     writeOnly:bool,
     *     title:string,
     *     class:class-string<ColumnFactory<TModel,mixed>>|class-string<FakeStringEnum&EnumColumn>,
     *     format?:string|array{
     *          decimalDigits:int,
     *          nullValue:string,
     *          prefix:string,
     *          suffix:string,
     *      },
     *     states:array<string,array{badge:string,label:string}>,
     * } $definition
     * @phpstan-template TModel of \Fykosak\NetteORM\Model\Model
     */
    private function createColumnFactory(
        string $tableName,
        string $modelClassName,
        string $fieldName,
        array $definition
    ): void {
        $builder = $this->getContainerBuilder();
        $factory = $builder->addDefinition($this->prefix($tableName . '.column.' . $fieldName));
        switch ($definition['type']) {
            case 'primaryKey':
                $this->setUpDefaultFactory(
                    $factory,
                    $tableName,
                    $modelClassName,
                    $fieldName,
                    PrimaryKeyColumnFactory::class,
                    $definition
                );
                break;
            case 'string':
                $this->setUpDefaultFactory(
                    $factory,
                    $tableName,
                    $modelClassName,
                    $fieldName,
                    StringColumnFactory::class,
                    $definition
                );
                break;
            case 'text':
                $this->setUpDefaultFactory(
                    $factory,
                    $tableName,
                    $modelClassName,
                    $fieldName,
                    TextColumnFactory::class,
                    $definition
                );
                break;
            case 'dateTime':
                $this->registerAbstractDateTimeRow(
                    $factory,
                    $tableName,
                    $modelClassName,
                    $fieldName,
                    DateTimeColumnFactory::class,
                    $definition //@phpstan-ignore-line
                );
                break;
            case 'date':
                $this->registerAbstractDateTimeRow(
                    $factory,
                    $tableName,
                    $modelClassName,
                    $fieldName,
                    DateColumnFactory::class,
                    $definition //@phpstan-ignore-line
                );
                break;
            case 'time':
                $this->registerAbstractDateTimeRow(
                    $factory,
                    $tableName,
                    $modelClassName,
                    $fieldName,
                    TimeColumnFactory::class,
                    $definition //@phpstan-ignore-line
                );
                break;
            case 'phone':
                $this->setUpDefaultFactory(
                    $factory,
                    $tableName,
                    $modelClassName,
                    $fieldName,
                    PhoneColumnFactory::class,
                    $definition
                );
                break;
            case 'email':
                $this->setUpDefaultFactory(
                    $factory,
                    $tableName,
                    $modelClassName,
                    $fieldName,
                    EmailColumnFactory::class,
                    $definition
                );
                break;
            case 'localizedString':
                $this->setUpDefaultFactory(
                    $factory,
                    $tableName,
                    $modelClassName,
                    $fieldName,
                    LocalizedStringColumnFactory::class,
                    $definition
                );
                break;
            case 'float':
                $this->setUpNumberFactory(
                    $factory,
                    $tableName,
                    $modelClassName,
                    $fieldName,
                    FloatColumnFactory::class,
                    $definition //@phpstan-ignore-line
                );
                break;
            case 'int':
                $this->setUpNumberFactory(
                    $factory,
                    $tableName,
                    $modelClassName,
                    $fieldName,
                    IntColumnFactory::class,
                    $definition //@phpstan-ignore-line
                );
                break;
            case 'bool':
                $this->setUpDefaultFactory(
                    $factory,
                    $tableName,
                    $modelClassName,
                    $fieldName,
                    LogicColumnFactory::class,
                    $definition
                );
                break;
            case 'class':
                $this->registerClassColumnFactory(
                    $factory,
                    $tableName,
                    $modelClassName,
                    $fieldName,
                    $definition// @phpstan-ignore-line
                );
                break;
            case 'enum':
                $this->registerEnumColumnFactory(
                    $factory,
                    $tableName,
                    $modelClassName,
                    $fieldName,
                    $definition// @phpstan-ignore-line
                );
                break;
            default:
                throw new NotImplementedException();
        }
    }

    /**
     * @phpstan-param array{
     *     permission: string,
     *     accessKey?:string,
     *     omitInputField:bool,
     *     required:bool,
     *     description?:string,
     *     writeOnly:bool,
     *     title:string,
     *     class:class-string<FakeStringEnum&EnumColumn>
     * } $field
     */
    private function registerEnumColumnFactory(
        ServiceDefinition $factory,
        string $tableName,
        string $modelClassName,
        string $fieldName,
        array $field
    ): void {
        $this->setUpDefaultFactory($factory, $tableName, $modelClassName, $fieldName, EnumColumnFactory::class, $field);
        $factory->addSetup('setEnumClassName', [$field['class']]);
    }

    /**
     * @phpstan-param array{
     *     permission: string,
     *     accessKey?:string,
     *     omitInputField:bool,
     *     required:bool,
     *     description?:string,
     *     writeOnly:bool,
     *     title:string,
     *     class:class-string<ColumnFactory<TModel,mixed>>,
     * } $field
     * @phpstan-template TModel of \Fykosak\NetteORM\Model\Model
     */
    private function registerClassColumnFactory(
        ServiceDefinition $factory,
        string $tableName,
        string $modelClassName,
        string $fieldName,
        array $field
    ): void {
        $this->setUpDefaultFactory($factory, $tableName, $modelClassName, $fieldName, $field['class'], $field);
    }

    /**
     * @phpstan-param array{
     *     permission: string,
     *     accessKey?:string,
     *     omitInputField:bool,
     *     required:bool,
     *     description?:string,
     *     writeOnly:bool,
     *     title:string,
     *     format:array{
     *          decimalDigits:int,
     *          nullValue:string,
     *          prefix:string,
     *          suffix:string,
     *      },
     * } $field
     * @phpstan-template TModel of \Fykosak\NetteORM\Model\Model
     * @phpstan-param class-string<ColumnFactory<TModel,mixed>> $factoryClassName
     */
    private function setUpNumberFactory(
        ServiceDefinition $factory,
        string $tableName,
        string $modelClassName,
        string $fieldName,
        string $factoryClassName,
        array $field
    ): void {
        $this->setUpDefaultFactory(
            $factory,
            $tableName,
            $modelClassName,
            $fieldName,
            $factoryClassName,
            $field
        );
        $data = $field['format'];
        $factory->addSetup(
            'setNumberFactory',
            [$data['nullValue'], $data['prefix'], $data['suffix'], $data['decimalDigits']]
        );
    }

    /**
     * @phpstan-param array{
     *     permission: string,
     *     accessKey?:string,
     *     omitInputField:bool,
     *     required:bool,
     *     description?:string,
     *     writeOnly:bool,
     *     title:string,
     *     format?:string,
     * } $field
     * @phpstan-template TModel of \Fykosak\NetteORM\Model\Model
     * @phpstan-param class-string<ColumnFactory<TModel,mixed>> $factoryClassName
     */
    private function registerAbstractDateTimeRow(
        ServiceDefinition $factory,
        string $tableName,
        string $modelClassName,
        string $fieldName,
        string $factoryClassName,
        array $field
    ): void {
        $this->setUpDefaultFactory($factory, $tableName, $modelClassName, $fieldName, $factoryClassName, $field);
        if (isset($field['format'])) {
            $factory->addSetup('setFormat', [$field['format']]);
        }
    }

    /**
     * @param string|Statement $value
     */
    private function translate($value): string
    {
        if ($value instanceof Statement) {
            return ($value->entity)(...$value->arguments);// @phpstan-ignore-line
        }
        return $value;
    }

    /**
     * @phpstan-param TCommonParams $field
     * @phpstan-template TModel of \Fykosak\NetteORM\Model\Model
     * @phpstan-param class-string<ColumnFactory<TModel,mixed>> $factoryClassName
     */
    private function setUpDefaultFactory(
        ServiceDefinition $factory,
        string $tableName,
        string $modelClassName,
        string $fieldName,
        string $factoryClassName,
        array $field
    ): void {
        $factory->setFactory($factoryClassName);
        $factory->addSetup('setUp', [
            $tableName,
            $modelClassName,
            $field['accessKey'] ?? $fieldName,
            $this->translate($field['title']),
            isset($field['description']) ? $this->translate($field['description']) : null,
        ]);
        $factory->addSetup('setPermissionValue', [$field['permission']]);
        $factory->addSetup('setOmitInputField', [$field['omitInputField']]);
        $factory->addSetup('setRequired', [$field['required']]);
        $factory->addSetup('setWriteOnly', [$field['writeOnly']]);
    }
}
