<?php

namespace FKSDB\Components\Controls\Stalking\StalkingComponent;

use FKSDB\Components\Controls\Stalking\StalkingControl;
use FKSDB\Components\Controls\Stalking\StalkingService;
use FKSDB\ORM\Models\ModelPerson;
use Nette\Application\BadRequestException;
use Nette\DI\Container;
use FKSDB\Exceptions\NotImplementedException;
use Nette\InvalidStateException;
use Nette\Templating\FileTemplate;

/**
 * Class StalkingComponent
 * @package FKSDB\Components\Controls\Stalking
 * @property-read FileTemplate $template
 */
class StalkingComponent extends StalkingControl {
    /**
     * @var StalkingService
     */
    private $stalkingService;

    /**
     * StalkingComponent constructor.
     * @param Container $container
     */
    public function __construct(Container $container) {
        parent::__construct($container);
        $this->stalkingService = $container->getByType(StalkingService::class);
    }

    /**
     * @param string $section
     * @param ModelPerson $person
     * @param int $userPermissions
     * @return void
     * @throws BadRequestException
     * @throws NotImplementedException
     */
    public function render(string $section, ModelPerson $person, int $userPermissions) {
        $definition = $this->stalkingService->getSection($section);
        $this->beforeRender($person, $userPermissions);
        $this->template->headline = _($definition['label']);
        $this->template->minimalPermissions = $definition['minimalPermission'];

        switch ($definition['layout']) {
            case 'single':
                return $this->renderSingle($definition, $person);
            case 'multi':
                return $this->renderMulti($definition, $person);
            default:
                throw new InvalidStateException();
        }
    }

    /**
     * @param array $definition
     * @param ModelPerson $person
     * @throws NotImplementedException
     */
    private function renderSingle(array $definition, ModelPerson $person) {

        $model = null;
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
        $this->template->setFile(__DIR__ . '/layout.single.latte');
        $this->template->render();
    }

    /**
     * @param array $definition
     * @param ModelPerson $person
     * @return void
     */
    private function renderMulti(array $definition, ModelPerson $person) {
        $models = [];
        $query = $person->related($definition['table']);
        foreach ($query as $datum) {
            $models[] = ($definition['model'])::createFromActiveRow($datum);
        }
        $this->template->links = array_map(function ($link) {
            return $this->tableReflectionFactory->loadLinkFactory($link);
        }, $definition['links']);
        $this->template->rows = $definition['rows'];
        $this->template->models = $models;
        $this->template->itemHeadline = $definition['itemHeadline'];
        $this->template->setFile(__DIR__ . '/layout.multi.latte');
        $this->template->render();
    }
}
