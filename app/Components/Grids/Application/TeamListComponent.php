<?php

declare(strict_types=1);

namespace FKSDB\Components\Grids\Application;

use FKSDB\Components\Badges\ContestBadge;
use FKSDB\Components\Controls\ColumnPrinter\ColumnPrinterComponent;
use FKSDB\Components\Controls\LinkPrinter\LinkPrinterComponent;
use FKSDB\Components\Grids\ListComponent\ListComponent;
use FKSDB\Models\ORM\Models\EventModel;
use FKSDB\Models\ORM\Models\Fyziklani\TeamMemberModel;
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

        $this->classNameCallback = fn(TeamModel2 $team) => 'alert alert-' . $team->state->getBehaviorType();
        $title = $this->createReferencedRow('fyziklani_team.name_n_id');
        $title->className .= ' fw-bold h4';
        $row = $this->createColumnsRow('row0');
        $row->createReferencedRow('fyziklani_team.state');
        $row->createReferencedRow('fyziklani_team.category');
        $row->createReferencedRow('fyziklani_team.game_lang');
        $row->createReferencedRow('fyziklani_team.phone');
        $memberTitle = $this->createRendererRow('member_title', fn() => Html::el('strong')->addText(_('Members')));
        $memberTitle->className .= ' h5';
        $memberList = $this->createListGroupRow('members', function (TeamModel2 $team) {
            $members = [];
            /** @var TeamMemberModel $member */
            foreach ($team->getMembers() as $member) {
                $members[] = $member->getPersonHistory();
            }
            return $members;
        });
        $memberList->createReferencedRow('person.full_name');
        $memberList->createReferencedRow('school.school');


        $teacherTitle = $this->createRendererRow('teacher_title', fn() => Html::el('strong')->addText(_('Teachers')));
        $teacherTitle->className .= ' h5';
        $teacherList = $this->createListGroupRow('teachers', fn(TeamModel2 $team) => $team->getTeachers());
        $teacherList->createReferencedRow('person.full_name');

        $this->createDefaultButton(
            'detail',
            _('Detail'),
            fn(TeamModel2 $team) => ['detail', ['id' => $team->fyziklani_team_id]]
        );
    }

    protected function getModels(): iterable
    {
        return $this->event->getTeams();
    }
}
