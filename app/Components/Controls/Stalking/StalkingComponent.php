<?php

namespace FKSDB\Components\Controls\Stalking;

use FKSDB\Components\Forms\Factories\TableReflectionFactory;
use FKSDB\ORM\Models\ModelPerson;
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
     * @param int $mode
     * @param string $layout
     */
    public function __construct(StalkingService $stalkingService, ModelPerson $modelPerson, TableReflectionFactory $tableReflectionFactory, ITranslator $translator, int $mode, string $layout = self::LAYOUT_NONE) {
        parent::__construct($modelPerson, $tableReflectionFactory, $translator, $mode, $layout);
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

        $this->template->rows = [];
        foreach ($definition['rows'] as $row) {
            list(, $tableName, $fieldName) = \explode('.', $row);
            $model = null;
            switch ($tableName) {
                case 'person_info':
                    $model = $this->modelPerson->getInfo();
                    break;
                case 'person':
                    $model = $this->modelPerson;
                    break;
            }
            if (!$model) {
                throw new NotImplementedException();
            }
            $this->template->rows[] = [$tableName, $fieldName, $model];
        }
        $this->template->setFile(__DIR__ . '/StalkingComponent.latte');
        $this->template->render();
    }
}
