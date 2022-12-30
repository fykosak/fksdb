<?php

declare(strict_types=1);

namespace FKSDB\Components\Grids\Application;

use FKSDB\Components\Grids\Components\Button\PresenterButton;
use FKSDB\Components\Grids\Components\Container\RowContainer;
use FKSDB\Components\Grids\Components\Container\ListGroupContainer;
use FKSDB\Components\Grids\Components\FilterListComponent;
use FKSDB\Components\Grids\Components\Referenced\TemplateItem;
use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Models\Exceptions\NotImplementedException;
use FKSDB\Models\ORM\FieldLevelPermission;
use FKSDB\Models\ORM\Models\EventModel;
use FKSDB\Models\ORM\Models\Fyziklani\GameLang;
use FKSDB\Models\ORM\Models\Fyziklani\TeamCategory;
use FKSDB\Models\ORM\Models\Fyziklani\TeamMemberModel;
use FKSDB\Models\ORM\Models\Fyziklani\TeamModel2;
use FKSDB\Models\ORM\Models\Fyziklani\TeamState;
use FKSDB\Models\ORM\ORMFactory;
use Fykosak\Utils\UI\Title;
use Nette\Database\Table\Selection;
use Nette\DI\Container;
use Nette\Forms\Form;

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

    /**
     * @throws BadTypeException
     * @throws \ReflectionException
     */
    protected function configure(): void
    {
        $this->classNameCallback = fn(TeamModel2 $team): string => 'alert alert-' . $team->state->getBehaviorType();
        $this->setTitle(
            new TemplateItem($this->container, '<h4>@fyziklani_team.name (@fyziklani_team.fyziklani_team_id)</h4>')
        );
        $row = new RowContainer($this->container, new Title(null, ''));
        $this->addComponent($row, 'row0');
        $row->addComponent(
            new TemplateItem($this->container, '@fyziklani_team.state', '@fyziklani_team.state:title'),
            'state'
        );
        $row->addComponent(
            new TemplateItem($this->container, '@fyziklani_team.category', '@fyziklani_team.category:title'),
            'category'
        );
        $row->addComponent(
            new TemplateItem($this->container, '@fyziklani_team.game_lang', '@fyziklani_team.game_lang:title'),
            'lang'
        );
        $row->addComponent(
            new TemplateItem($this->container, '@fyziklani_team.phone', '@fyziklani_team.phone:title'),
            'phone'
        );
        $memberList = new ListGroupContainer($this->container, function (TeamModel2 $team): array {
            $members = [];
            /** @var TeamMemberModel $member */
            foreach ($team->getMembers() as $member) {
                $members[] = $member->getPersonHistory();
            }
            return $members;
        }, new Title(null, _('Members')));
        $this->addComponent($memberList, 'members');
        $memberList->addComponent(new TemplateItem($this->container, '@person.full_name'), 'name');
        $memberList->addComponent(new TemplateItem($this->container, '@school.school'), 'school');

        $teacherList = new ListGroupContainer(
            $this->container,
            fn(TeamModel2 $team): iterable => $team->getTeachers(),
            new Title(null, _('Teachers'))
        );
        $this->addComponent($teacherList, 'teachers');
        $teacherList->addComponent(new TemplateItem($this->container, '@person.full_name'), 'name');
        $this->addButton(
            new PresenterButton(
                $this->container,
                new Title(null, _('Detail')),
                fn(TeamModel2 $team): array => ['detail', ['id' => $team->fyziklani_team_id]]
            ),
            'detail'
        );
    }

    protected function getModels(): Selection
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

    /**
     * @throws NotImplementedException
     */
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
