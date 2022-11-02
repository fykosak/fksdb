<?php

declare(strict_types=1);

namespace FKSDB\Components\Controls\Stalking\Components;

use FKSDB\Components\Controls\Stalking\BaseStalkingComponent;
use FKSDB\Models\ORM\FieldLevelPermission;
use FKSDB\Models\ORM\Models\PersonModel;
use FKSDB\Models\ORM\Models\PostContactType;
use Nette\DI\Container;

class AddressComponent extends BaseStalkingComponent
{
    private PostContactType $type;

    public function __construct(Container $container, PersonModel $person, int $userPermissions, PostContactType $type)
    {
        parent::__construct($container, $person, $userPermissions);
        $this->type = $type;
    }

    final public function render(): void
    {
        if ($this->beforeRender()) {
            $this->template->address = $this->person->getAddress($this->type);
            $this->template->type = $this->type;
            $this->template->render(__DIR__ . DIRECTORY_SEPARATOR . 'layout.address.latte');
        }
    }

    protected function getMinimalPermissions(): int
    {
        return FieldLevelPermission::ALLOW_RESTRICT;
    }
}
