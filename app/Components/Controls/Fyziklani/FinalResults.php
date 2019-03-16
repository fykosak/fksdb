<?php

namespace FKSDB\Components\Controls\Fyziklani;

use FKSDB\Components\Grids\Fyziklani\ResultsCategoryGrid;
use FKSDB\Components\Grids\Fyziklani\ResultsTotalGrid;
use FKSDB\ORM\ModelEvent;
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
     * @var \FKSDB\ORM\Services\Fyziklani\ServiceFyziklaniTeam
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
     * FinalResults constructor.
     * @param ModelEvent $event
     * @param ServiceFyziklaniTeam $serviceFyziklaniTeam
     * @param ITranslator $translator
     */
    public function __construct(ModelEvent $event, ServiceFyziklaniTeam $serviceFyziklaniTeam, ITranslator $translator) {
        parent::__construct();
        $this->serviceFyziklaniTeam = $serviceFyziklaniTeam;
        $this->event = $event;
        $this->translator = $translator;
    }

    /**
     * @param string $category
     * @return bool
     */
    public function isClosedCategory(string $category): bool {
        $query = $this->serviceFyziklaniTeam->findParticipating($this->event);
        $query->where('category', $category)->where('rank_category', null);
        $count = $query->count();
        return $count == 0;
    }

    /**
     * @return bool
     */
    public function isClosedTotal(): bool {
        $query = $this->serviceFyziklaniTeam->findParticipating($this->event);
        $query->where('rank_total', null);
        $count = $query->count();
        return $count == 0;
    }

    /**
     * @return ResultsCategoryGrid
     */
    public function createComponentResultsCategoryAGrid(): ResultsCategoryGrid {
        return new ResultsCategoryGrid($this->event, $this->serviceFyziklaniTeam, 'A');
    }

    /**
     * @return ResultsCategoryGrid
     */
    public function createComponentResultsCategoryBGrid(): ResultsCategoryGrid {
        return new ResultsCategoryGrid($this->event, $this->serviceFyziklaniTeam, 'B');
    }

    /**
     * @return ResultsCategoryGrid
     */
    public function createComponentResultsCategoryCGrid(): ResultsCategoryGrid {
        return new ResultsCategoryGrid($this->event, $this->serviceFyziklaniTeam, 'C');
    }

    /**
     * @return ResultsTotalGrid
     */
    public function createComponentResultsTotalGrid(): ResultsTotalGrid {
        return new ResultsTotalGrid($this->event, $this->serviceFyziklaniTeam);
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
