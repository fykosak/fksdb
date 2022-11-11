<?php

declare(strict_types=1);

namespace FKSDB\Components\Controls\Stalking\StalkingComponent;

use FKSDB\Components\Controls\Stalking\BaseStalkingComponent;
use FKSDB\Models\Exceptions\NotImplementedException;
use FKSDB\Models\ORM\FieldLevelPermissionValue;

class StalkingComponent extends BaseStalkingComponent
{
    private FieldLevelPermissionValue $minimalPermissions = FieldLevelPermissionValue::Full;

    /**
     * @throws NotImplementedException
     */
    final public function render(string $section): void
    {
        $definition = $this->getContext()->getParameters()['components'][$section];
        $this->minimalPermissions = FieldLevelPermissionValue::from($definition['minimalPermission']);
        if ($this->beforeRender()) {
            $this->template->headline = $definition['label'];
            $this->template->userPermission = $this->userPermissions;
            $this->renderSingle($definition);
        }
    }

    /**
     * @throws NotImplementedException
     */
    private function renderSingle(array $definition): void
    {
        $this->template->model = match ($definition['table']) {
            'person_info' => $this->person->getInfo(),
            'person' => $this->person,
            'login' => $this->person->getLogin(),
            default => throw new NotImplementedException(),
        };
        $this->template->rows = $definition['rows'];
        $this->template->render(__DIR__ . DIRECTORY_SEPARATOR . 'layout.single.latte');
    }

    protected function getMinimalPermissions(): FieldLevelPermissionValue
    {
        return $this->minimalPermissions;
    }
}
