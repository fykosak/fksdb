<?php

declare(strict_types=1);

namespace FKSDB\Components\Controls\Person\Detail;

use FKSDB\Models\Exceptions\NotImplementedException;
use FKSDB\Models\ORM\FieldLevelPermission;

class Component extends BaseComponent
{
    private int $minimalPermissions = FieldLevelPermission::ALLOW_FULL;

    /**
     * @throws NotImplementedException
     */
    final public function render(string $section): void
    {
        $definition = $this->getContext()->getParameters()['components'][$section];
        $this->minimalPermissions = $definition['minimalPermission'];
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
        switch ($definition['table']) {
            case 'person_info':
                $model = $this->person->getInfo();
                break;
            case 'person':
                $model = $this->person;
                break;
            case 'login':
                $model = $this->person->getLogin();
                break;
            default:
                throw new NotImplementedException();
        }

        $this->template->model = $model;
        $this->template->rows = $definition['rows'];
        $this->template->render(__DIR__ . DIRECTORY_SEPARATOR . 'single.latte');
    }

    protected function getMinimalPermissions(): int
    {
        return $this->minimalPermissions;
    }
}