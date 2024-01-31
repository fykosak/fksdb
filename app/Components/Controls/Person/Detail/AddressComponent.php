<?php

declare(strict_types=1);

namespace FKSDB\Components\Controls\Person\Detail;

use FKSDB\Models\ORM\FieldLevelPermissionValue;
use FKSDB\Models\ORM\Models\PersonModel;
use FKSDB\Models\ORM\Models\PostContactType;
use Nette\DI\Container;

class AddressComponent extends BaseComponent
{
    private PostContactType $type;

    public function __construct(
        Container $container,
        PersonModel $person,
        FieldLevelPermissionValue $userPermissions,
        PostContactType $type
    ) {
        parent::__construct($container, $person, $userPermissions);
        $this->type = $type;
    }

    final public function render(): void
    {
        if ($this->beforeRender()) {
            $this->template->render(__DIR__ . DIRECTORY_SEPARATOR . 'address.latte', [
                'address' => $this->person->getAddress($this->type),
                'type' => $this->type,
            ]);
        }
    }

    protected function getMinimalPermission(): FieldLevelPermissionValue
    {
        return FieldLevelPermissionValue::Restrict;
    }
}
