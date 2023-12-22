<?php

declare(strict_types=1);

namespace FKSDB\Components\Forms\Containers;

use FKSDB\Components\Forms\Containers\Models\ContainerWithOptions;
use FKSDB\Components\Forms\Containers\Models\ReferencedPersonContainer;
use FKSDB\Components\Forms\Controls\WriteOnly\WriteOnly;
use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Models\ORM\Columns\OmittedControlException;
use FKSDB\Models\ORM\FieldLevelPermission;
use FKSDB\Models\ORM\ReflectionFactory;
use Nette\Application\ForbiddenRequestException;
use Nette\DI\Container as DIContainer;
use Nette\Forms\Controls\BaseControl;

/**
 * @phpstan-import-type EvaluatedFieldMetaData from ReferencedPersonContainer
 */
class ModelContainer extends ContainerWithOptions
{
    private string $table;
    private ReflectionFactory $reflectionFactory;

    public function __construct(DIContainer $container, string $table)
    {
        parent::__construct($container);
        $this->table = $table;
    }

    public function inject(ReflectionFactory $reflectionFactory): void
    {
        $this->reflectionFactory = $reflectionFactory;
    }

    /**
     * @param mixed $args
     * @throws BadTypeException
     * @throws OmittedControlException
     * @throws ForbiddenRequestException
     * @phpstan-param EvaluatedFieldMetaData $metaData
     */
    public function addField(
        string $field,
        array $metaData = [],
        ?FieldLevelPermission $userPermissions = null,
        ...$args
    ): BaseControl {
        $factory = $this->reflectionFactory->loadColumnFactory($this->table, $field);
        $control = $factory->createField(...$args);
        if ($userPermissions) {
            $canWrite = $factory->hasWritePermissions($userPermissions->write);
            $canRead = $factory->hasReadPermissions($userPermissions->read);
            if ($control instanceof WriteOnly) {
                $control->setWriteOnly(!$canRead);
            } elseif (!$canRead) {
                throw new ForbiddenRequestException();
            }
            $control->setDisabled(!$canWrite);
        }
        self::appendMetadata($control, $metaData);
        $this->addComponent($control, $field);
        return $control;
    }

    /**
     * @phpstan-param EvaluatedFieldMetaData $metadata
     */
    public static function appendMetadata(BaseControl $control, array $metadata): void
    {
        foreach ($metadata as $key => $value) {
            switch ($key) {
                case 'required':
                    $control->setRequired($value);
                    break;
                case 'caption':
                    if ($value) {
                        $control->caption = $value;
                    }
                    break;
                case 'description':
                    if ($value) {
                        $control->setOption('description', $value);
                    }
            }
        }
    }
    /*
     * @phpstan-param Model|iterable<string|int,mixed> $data
     * @return static
     *
    public function setValues($data, bool $erase = false): self
    {
        if ($data instanceof Model) {
            $data = $data->toArray();
        }
        return parent::setValues($data, $erase);
    }*/
}
