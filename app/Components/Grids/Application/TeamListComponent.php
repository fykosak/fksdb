<?php

declare(strict_types=1);

namespace FKSDB\Components\Grids\Application;

use FKSDB\Components\Badges\ContestBadge;
use FKSDB\Components\Controls\ColumnPrinter\ColumnPrinterComponent;
use FKSDB\Components\Controls\LinkPrinter\LinkPrinterComponent;
use FKSDB\Components\Grids\ListComponent\ListComponent;
use FKSDB\Models\ORM\FieldLevelPermissionValue;
use FKSDB\Models\ORM\Models\EventModel;
use FKSDB\Models\ORM\Models\Fyziklani\TeamModel2;
use FKSDB\Models\ORM\ORMFactory;
use Nette\DI\Container;
use Nette\Utils\Html;

class TeamListComponent extends ListComponent
{
    private EventModel $event;
    protected ORMFactory $tableReflectionFactory;

    public function __construct(EventModel $event, Container $container)
    {
        parent::__construct($container);
        $this->event = $event;
    }

    public function render(): void
    {
        $this->template->teams = $this->event->getFyziklaniTeams();
        $this->template->render(__DIR__ . DIRECTORY_SEPARATOR . 'teamList.latte');
    }

    final public function injectPrimary(ORMFactory $tableReflectionFactory): void
    {
        $this->tableReflectionFactory = $tableReflectionFactory;
    }

    protected function createComponentContestBadge(): ContestBadge
    {
        return new ContestBadge($this->getContext());
    }

    protected function createComponentValuePrinter(): ColumnPrinterComponent
    {
        return new ColumnPrinterComponent($this->getContext());
    }

    protected function createComponentLinkPrinter(): LinkPrinterComponent
    {
        return new LinkPrinterComponent($this->getContext());
    }

    protected function configure(): void
    {
        $title = $this->createReferencedRow('fyziklani_team.name_n_id');
        $title->className .= ' fw-bold';
        $row = $this->createColumnsRow('row0');
        $row->createReferencedRow('fyziklani_team.phone');
        $row->createReferencedRow('fyziklani_team.state');
        $row->createReferencedRow('fyziklani_team.category');
    }
}
