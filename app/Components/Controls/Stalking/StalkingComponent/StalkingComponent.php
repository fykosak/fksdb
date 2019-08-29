<?php

namespace FKSDB\Components\Controls\Stalking\StalkingComponent;

use FKSDB\Components\Controls\Stalking\StalkingControl;
use FKSDB\Components\Controls\Stalking\StalkingService;
use FKSDB\Components\Forms\Factories\TableReflectionFactory;
use FKSDB\ORM\DbNames;
use FKSDB\ORM\Models\ModelOrg;
use FKSDB\ORM\Models\ModelPerson;
use FKSDB\ORM\Models\ModelPersonHistory;
use Nette\Application\BadRequestException;
use Nette\Localization\ITranslator;
use Nette\NotImplementedException;
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
     * @param StalkingService $stalkingService
     * @param ModelPerson $modelPerson
     * @param TableReflectionFactory $tableReflectionFactory
     * @param ITranslator $translator
     * @param int $userPermission
     */
    public function __construct(StalkingService $stalkingService, ModelPerson $modelPerson, TableReflectionFactory $tableReflectionFactory, ITranslator $translator, int $userPermission) {
        parent::__construct($modelPerson, $tableReflectionFactory, $translator, $userPermission);
        $this->stalkingService = $stalkingService;
    }

    /**
     * @param string $section
     * @throws \Nette\Application\BadRequestException
     * @throws \Exception
     */
    public function render(string $section) {
        $definition = $this->stalkingService->getSection($section);
        $this->beforeRender();
        $this->template->headline = _($definition['label']);
        $this->template->minimalPermissions = $definition['minimalPermission'];

        $this->template->table = $definition['table'];
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
        switch ($definition['table']) {
            case 'person_history':
                $histories = $this->modelPerson->related(DbNames::TAB_PERSON_HISTORY, 'person_id');
                foreach ($histories as $history) {
                    $models[] = ModelPersonHistory::createFromActiveRow($history);
                }
                break;
            case 'org':
                $orgs = $this->modelPerson->getOrgs();
                foreach ($orgs as $org) {
                    $models[] = ModelOrg::createFromActiveRow($org);
                }
                break;
            default:
                throw new NotImplementedException();
        }
        $this->template->rows = $definition['rows'];
        $this->template->models = $models;
        $this->template->itemHeadline = $definition['item_headline'];
        $this->template->setFile(__DIR__ . '/layout.multi.latte');
        $this->template->render();
    }
}
