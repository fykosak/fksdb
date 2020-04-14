<?php

namespace FKSDB\Components\Controls\Stalking\StalkingComponent;

use Exception;
use FKSDB\Components\Controls\Stalking\StalkingControl;
use FKSDB\Components\Controls\Stalking\StalkingService;
use FKSDB\ORM\Models\ModelPerson;
use Nette\Application\BadRequestException;
use Nette\DI\Container;
use FKSDB\NotImplementedException;
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
     * @param ModelPerson $modelPerson
     * @param int $userPermission
     */
    public function __construct(Container $container, ModelPerson $modelPerson, int $userPermission) {
        parent::__construct($container, $modelPerson, $userPermission);
        $this->stalkingService = $container->getByType(StalkingService::class);
    }

    /**
     * @param string $section
     * @throws BadRequestException
     * @throws Exception
     */
    public function render(string $section) {
        $definition = $this->stalkingService->getSection($section);
        $this->beforeRender();
        $this->template->headline = _($definition['label']);
        $this->template->minimalPermissions = $definition['minimalPermission'];

        switch ($definition['layout']) {
            case 'single':
                return $this->renderSingle($definition);
            case 'multi':
                return $this->renderMulti($definition);
            default:
                throw new BadRequestException();
        }
    }

    /**
     * @param array $definition
     * @throws NotImplementedException
     */
    private function renderSingle(array $definition) {

        $model = null;
        switch ($definition['table']) {
            case 'person_info':
                $model = $this->modelPerson->getInfo();
                break;
            case 'person':
                $model = $this->modelPerson;
                break;
            case 'login':
                $model = $this->modelPerson->getLogin();
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
     */
    private function renderMulti(array $definition) {
        $models = [];
        $query = $this->modelPerson->related($definition['table']);
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
