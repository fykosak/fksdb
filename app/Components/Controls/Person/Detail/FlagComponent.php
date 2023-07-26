<?php

declare(strict_types=1);

namespace FKSDB\Components\Controls\Person\Detail;

use FKSDB\Models\ORM\FieldLevelPermission;

class FlagComponent extends BaseComponent
{
    final public function render(): void
    {
        if ($this->beforeRender()) {
            $this->template->flags = $this->person->getFlags();
            /** @phpstan-ignore-next-line */
            $this->template->render(__DIR__ . DIRECTORY_SEPARATOR . 'flag.latte');
        }
    }

    protected function getMinimalPermissions(): int
    {
        return FieldLevelPermission::ALLOW_RESTRICT;
    }
}
