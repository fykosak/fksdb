<?php

namespace FKSDB\Components\Controls\Fyziklani;

use FKSDB\Components\Grids\Fyziklani\ResultsCategoryGrid;
use FKSDB\Components\Grids\Fyziklani\ResultsTotalGrid;
use FKSDB\ORM\ModelEvent;
use Nette\Application\UI\Control;
use Nette\Localization\ITranslator;
use Nette\Templating\FileTemplate;
use ORM\Services\Events\ServiceFyziklaniTeam;

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

    public function __construct(ModelEvent $event, ServiceFyziklaniTeam $serviceFyziklaniTeam, ITranslator $translator) {
        parent::__construct();
        $this->serviceFyziklaniTeam = $serviceFyziklaniTeam;
        $this->event = $event;
        $this->translator = $translator;
    }

    public function isClosedCategory(string $category): bool {
        $query = $this->serviceFyziklaniTeam->findParticipating($this->event);
        $query->where('category', $category)->where('rank_category', null);
        $count = $query->count();
        return $count == 0;
    }

    public function isClosedTotal(): bool {
        $query = $this->serviceFyziklaniTeam->findParticipating($this->event);
        $query->where('rank_total', null);
        $count = $query->count();
        return $count == 0;
    }

    public function createComponentResultsCategoryAGrid(): ResultsCategoryGrid {
        return new ResultsCategoryGrid($this->event, $this->serviceFyziklaniTeam, 'A');
    }

    public function createComponentResultsCategoryBGrid(): ResultsCategoryGrid {
        return new ResultsCategoryGrid($this->event, $this->serviceFyziklaniTeam, 'B');
    }

    public function createComponentResultsCategoryCGrid(): ResultsCategoryGrid {
        return new ResultsCategoryGrid($this->event, $this->serviceFyziklaniTeam, 'C');
    }

    public function createComponentResultsTotalGrid(): ResultsTotalGrid {
        return new ResultsTotalGrid($this->event, $this->serviceFyziklaniTeam);
    }

    public function render() {
        $this->template->that = $this;

        $this->template->setFile(__DIR__ . DIRECTORY_SEPARATOR . 'FinalResults.latte');
        $this->template->setTranslator($this->translator);
        $this->template->render();
    }
}
