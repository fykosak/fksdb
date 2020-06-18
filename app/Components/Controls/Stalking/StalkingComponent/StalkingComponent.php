<?php

namespace FKSDB\Components\Controls\Stalking\StalkingComponent;

use FKSDB\Components\Controls\Stalking\StalkingControl;
use FKSDB\Components\Controls\Stalking\StalkingService;
use FKSDB\ORM\Models\ModelPerson;
use Nette\Application\BadRequestException;
use Nette\DI\Container;
use FKSDB\Exceptions\NotImplementedException;
use Nette\InvalidStateException;

/**
 * Class StalkingComponent
 * @author Michal Červeňák <miso@fykos.cz>
 */
class StalkingComponent extends StalkingControl {
    /**
     * @var StalkingService
     */
    private $stalkingService;
    /**
     * @var int
     */
    private $userPermissions;
    /**
     * @var ModelPerson
     */
    private $person;

    /**
     * StalkingComponent constructor.
     * @param Container $container
     * @param ModelPerson $person
     * @param int $userPermissions
     */
    public function __construct(Container $container, ModelPerson $person, int $userPermissions) {
        parent::__construct($container);
        $this->stalkingService = $container->getByType(StalkingService::class);
        $this->userPermissions = $userPermissions;
        $this->person = $person;
    }

    /**
     * @param string $section
     * @return void
     * @throws BadRequestException
     * @throws NotImplementedException
     */
    public function render(string $section) {
        $definition = $this->stalkingService->getSection($section);
        $this->beforeRender($this->person, $this->userPermissions);
        $this->template->headline = _($definition['label']);
        $this->template->minimalPermissions = $definition['minimalPermission'];

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

    /**
     * @param array $definition
     * @return void
     * @throws NotImplementedException
     */
    private function renderSingle(array $definition) {

        $model = null;
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
        $this->template->setFile(__DIR__ . '/layout.single.latte');
        $this->template->render();
    }

    /**
     * @param array $definition
     * @return void
     */
    private function renderMulti(array $definition) {
        $models = [];
        $query = $this->person->related($definition['table']);
        foreach ($query as $datum) {
            $models[] = ($definition['model'])::createFromActiveRow($datum);
        }
        $this->template->links = array_map(function ($link) {
            $factory = $this->tableReflectionFactory->loadLinkFactory($link);
            $factory->setComponent($this);
            return $factory;
        }, $definition['links']);
        $this->template->rows = $definition['rows'];
        $this->template->models = $models;
        $this->template->itemHeadline = $definition['itemHeadline'];
        $this->template->setFile(__DIR__ . '/layout.multi.latte');
        $this->template->render();
    }
}
