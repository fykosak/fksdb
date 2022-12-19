<?php

declare(strict_types=1);

namespace FKSDB\Components\Grids\Application;

use FKSDB\Components\Grids\ListComponent\FilterListComponent;
use FKSDB\Models\ORM\FieldLevelPermission;
use FKSDB\Models\ORM\Models\EventModel;
use FKSDB\Models\ORM\Models\Fyziklani\GameLang;
use FKSDB\Models\ORM\Models\Fyziklani\TeamCategory;
use FKSDB\Models\ORM\Models\Fyziklani\TeamMemberModel;
use FKSDB\Models\ORM\Models\Fyziklani\TeamModel2;
use FKSDB\Models\ORM\Models\Fyziklani\TeamState;
use FKSDB\Models\ORM\ORMFactory;
use Nette\DI\Container;
use Nette\Forms\Form;
use Nette\Utils\Html;

class TeamListComponent extends FilterListComponent
{
    private EventModel $event;
    protected ORMFactory $tableReflectionFactory;

    public function __construct(EventModel $event, Container $container)
    {
        parent::__construct($container, FieldLevelPermission::ALLOW_FULL);
        $this->event = $event;
    }

    final public function injectPrimary(ORMFactory $tableReflectionFactory): void
    {
        $this->tableReflectionFactory = $tableReflectionFactory;
    }

    protected function configure(): void
    {

        $this->classNameCallback = fn(TeamModel2 $team) => 'alert alert-' . $team->state->getBehaviorType();
        $title = $this->createReferencedRow('fyziklani_team.name_n_id');
        $title->className .= ' fw-bold h4';
        $row = $this->createColumnsRow('row0');
        $row->createReferencedColumn('fyziklani_team.state');
        $row->createReferencedColumn('fyziklani_team.category');
        $row->createReferencedColumn('fyziklani_team.game_lang');
        $row->createReferencedColumn('fyziklani_team.phone');
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
        $memberList->createReferencedColumn('person.full_name');
        $memberList->createReferencedColumn('school.school');


        $teacherTitle = $this->createRendererRow('teacher_title', fn() => Html::el('strong')->addText(_('Teachers')));
        $teacherTitle->className .= ' h5';
        $teacherList = $this->createListGroupRow('teachers', fn(TeamModel2 $team) => $team->getTeachers());
        $teacherList->createReferencedColumn('person.full_name');

        $this->createDefaultButton(
            'detail',
            _('Detail'),
            fn(TeamModel2 $team) => ['detail', ['id' => $team->fyziklani_team_id]]
        );
    }

    protected function getModels(): iterable
    {
        $query = $this->event->getTeams();
        foreach ($this->filterParams as $key => $value) {
            if (is_null($value)) {
                continue;
            }
            switch ($key) {
                case 'category':
                    $query->where('category', $value);
                    break;
                case 'game_lang':
                    $query->where('game_lang', $value);
                    break;
                case 'name':
                    $query->where('name LIKE ?', '%' . $value . '%');
                    break;
                case 'state':
                    $query->where('state', $value);
                    break;
                case 'team_id':
                    $query->where('fyziklani_team_id', $value);
            }
        }
        return $query;
    }

    protected function configureForm(Form $form): void
    {
        $form->addText('name', _('Team name'))->setOption(
            'description',
            _('Works as %name%, characters "%" will be added automatically.')
        );
        $form->addText('team_id', _('Team Id'))->setHtmlType('number');
        $categories = [];
        foreach (TeamCategory::casesForEvent($this->event) as $teamCategory) {
            $categories[$teamCategory->value] = $teamCategory->label();
        }
        $form->addSelect('category', _('Category'), $categories)->setPrompt(_('Select category'));

        $gameLang = [];
        foreach (GameLang::cases() as $lang) {
            $gameLang[$lang->value] = $lang->label();
        }
        $form->addSelect('game_lang', _('Game lang'), $gameLang)->setPrompt(_('Select language'));

        $states = [];
        foreach (TeamState::cases() as $teamState) {
            $states[$teamState->value] = $teamState->label();
        }
        $form->addSelect('state', _('State'), $states)->setPrompt(_('Select state'));
    }
}
