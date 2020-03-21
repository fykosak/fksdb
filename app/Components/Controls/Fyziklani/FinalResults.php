<?php

namespace FKSDB\Components\Controls\Fyziklani;

use FKSDB\Components\Forms\Factories\TableReflectionFactory;
use FKSDB\Components\Grids\Fyziklani\ResultsCategoryGrid;
use FKSDB\Components\Grids\Fyziklani\ResultsTotalGrid;
use FKSDB\ORM\Models\ModelEvent;
use FKSDB\ORM\Services\Fyziklani\ServiceFyziklaniTeam;
use Nette\Application\UI\Control;
use Nette\Localization\ITranslator;
use Nette\Templating\FileTemplate;

/**
 * Class OrgResults
 * @package FKSDB\Components\Controls\Fyziklani
 * @property FileTemplate $template
 */
class FinalResults extends Control {
    /**
     * @var ServiceFyziklaniTeam
     */
    private $serviceFyziklaniTeam;
    /**
     * @var ModelEvent
     */
    private $event;
    /**
     * @var ITranslator
     */
    private $translator;
    /**
     * @var TableReflectionFactory
     */
    private $tableReflectionFactory;

    /**
     * FinalResults constructor.
     * @param ModelEvent $event
     * @param ServiceFyziklaniTeam $serviceFyziklaniTeam
     * @param ITranslator $translator
     * @param TableReflectionFactory $tableReflectionFactory
     */
    public function __construct(ModelEvent $event, ServiceFyziklaniTeam $serviceFyziklaniTeam, ITranslator $translator, TableReflectionFactory $tableReflectionFactory) {
        parent::__construct();
        $this->serviceFyziklaniTeam = $serviceFyziklaniTeam;
        $this->event = $event;
        $this->translator = $translator;
        $this->tableReflectionFactory = $tableReflectionFactory;
    }

    /**
     * @param string $category
     * @return bool
     */
    public function isClosedCategory(string $category): bool {
        $count = (int)$this->serviceFyziklaniTeam->findParticipating($this->event)
            ->where('category', $category)
            ->where('rank_category IS NULL')
            ->count();
        return $count === 0;
    }

    /**
     * @return bool
     */
    public function isClosedTotal(): bool {
        $count = (int)$this->serviceFyziklaniTeam->findParticipating($this->event)
            ->where('rank_total IS NULL')
            ->count();
        return $count === 0;
    }

    /**
     * @return ResultsCategoryGrid
     */
    public function createComponentResultsCategoryAGrid(): ResultsCategoryGrid {
        return new ResultsCategoryGrid($this->event, $this->serviceFyziklaniTeam, 'A', $this->tableReflectionFactory);
    }

    /**
     * @return ResultsCategoryGrid
     */
    public function createComponentResultsCategoryBGrid(): ResultsCategoryGrid {
        return new ResultsCategoryGrid($this->event, $this->serviceFyziklaniTeam, 'B', $this->tableReflectionFactory);
    }

    /**
     * @return ResultsCategoryGrid
     */
    public function createComponentResultsCategoryCGrid(): ResultsCategoryGrid {
        return new ResultsCategoryGrid($this->event, $this->serviceFyziklaniTeam, 'C', $this->tableReflectionFactory);
    }

    /**
     * @return ResultsTotalGrid
     */
    public function createComponentResultsTotalGrid(): ResultsTotalGrid {
        return new ResultsTotalGrid($this->event, $this->serviceFyziklaniTeam, $this->tableReflectionFactory);
    }

    /**
     * @return void
     */
    public function render() {
        $this->template->that = $this;

        $this->template->setFile(__DIR__ . DIRECTORY_SEPARATOR . 'FinalResults.latte');
        $this->template->setTranslator($this->translator);
        $this->template->render();
    }
}
