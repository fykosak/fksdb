<?php

declare(strict_types=1);

namespace FKSDB\Components\Controls\Stalking\StalkingComponent;

use FKSDB\Components\Controls\Stalking\BaseStalkingComponent;
use Fykosak\NetteORM\Model;
use FKSDB\Models\ORM\Models\PersonModel;
use FKSDB\Models\Exceptions\NotImplementedException;
use Nette\InvalidStateException;

class StalkingComponent extends BaseStalkingComponent
{
    /**
     * @throws NotImplementedException
     */
    final public function render(string $section, PersonModel $person, int $userPermission): void
    {
        $definition = $this->getContext()->getParameters()['components'][$section];
        $this->beforeRender($person, _($definition['label']), $userPermission, $definition['minimalPermission']);
        $this->template->userPermission = $userPermission;
        switch ($definition['layout']) {
            case 'single':
                $this->renderSingle($definition, $person);
                return;
            case 'multi':
                $this->renderMulti($definition, $person);
                return;
            default:
                throw new InvalidStateException();
        }
    }

    /**
     * @throws NotImplementedException
     */
    private function renderSingle(array $definition, PersonModel $person): void
    {
        switch ($definition['table']) {
            case 'person_info':
                $model = $person->getInfo();
                break;
            case 'person':
                $model = $person;
                break;
            case 'login':
                $model = $person->getLogin();
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
    private function renderMulti(array $definition, PersonModel $person): void
    {
        $this->template->links = $definition['links'];
        $this->template->rows = $definition['rows'];
        $this->template->models =  $person->related($definition['table']);
        $this->template->itemHeadline = $definition['itemHeadline'];
        $this->template->render(__DIR__ . DIRECTORY_SEPARATOR . 'layout.multi.latte');
    }
}
