<?php

declare(strict_types=1);

namespace FKSDB\Components\Controls\Stalking\StalkingComponent;

use FKSDB\Components\Controls\Stalking\BaseStalkingComponent;
use FKSDB\Models\Exceptions\NotImplementedException;
use FKSDB\Models\ORM\FieldLevelPermission;
use Fykosak\NetteORM\Model;
use Nette\InvalidStateException;

class StalkingComponent extends BaseStalkingComponent
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
            switch ($definition['layout']) {
                case 'single':
                    $this->renderSingle($definition);
                    return;
                case 'multi':
                    $this->renderMulti($definition);
                    return;
                default:
                    throw new InvalidStateException();
            }
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
        $this->template->render(__DIR__ . DIRECTORY_SEPARATOR . 'layout.single.latte');
    }

    /**
     * @param array|Model[] $definition
     */
    private function renderMulti(array $definition): void
    {
        $this->template->links = $definition['links'];
        $this->template->rows = $definition['rows'];
        $this->template->models = $this->person->related($definition['table']);
        $this->template->itemHeadline = $definition['itemHeadline'];
        $this->template->render(__DIR__ . DIRECTORY_SEPARATOR . 'layout.multi.latte');
    }

    protected function getMinimalPermissions(): int
    {
        return $this->minimalPermissions;
    }
}
