<?php

declare(strict_types=1);

namespace FKSDB\Components\Grids\Application;

use FKSDB\Components\Grids\Components\Container\RelatedTable;
use FKSDB\Components\Grids\Components\FilterList;
use FKSDB\Components\Grids\Components\Referenced\TemplateItem;
use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Models\ORM\FieldLevelPermission;
use FKSDB\Models\ORM\Models\EventModel;
use FKSDB\Models\ORM\Models\Fyziklani\GameLang;
use FKSDB\Models\ORM\Models\Fyziklani\TeamCategory;
use FKSDB\Models\ORM\Models\Fyziklani\TeamMemberModel;
use FKSDB\Models\ORM\Models\Fyziklani\TeamModel2;
use FKSDB\Models\ORM\Models\Fyziklani\TeamState;
use FKSDB\Models\ORM\Models\Fyziklani\TeamTeacherModel;
use FKSDB\Models\ORM\ORMFactory;
use Fykosak\NetteORM\TypedGroupedSelection;
use Fykosak\Utils\UI\Title;
use Nette\DI\Container;
use Nette\Forms\Form;

/**
 * @phpstan-extends FilterList<TeamModel2,array{
 *     category?:string|null,
 *     game_lang?:string|null,
 *     name?:string|null,
 *     state?:string|null,
 *     team_id?:int|null,
 *     }>
 */
class TeamListComponent extends FilterList
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
            new TemplateItem(// @phpstan-ignore-line
                $this->container,
                '<h4>@fyziklani_team.name (@fyziklani_team.fyziklani_team_id)</h4>'
            )
        );
        $row = $this->createRow();
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

        $memberList = $this->addRow(
            new RelatedTable($this->container, function (TeamModel2 $team): array {
                $members = [];
                /** @var TeamMemberModel $member */
                foreach ($team->getMembers() as $member) {
                    $members[] = $member->getPersonHistory();
                }
                return $members;
            }, new Title(null, _('Members'))),
            'members'
        );
        $memberList->addColumn(
            new TemplateItem($this->container, '@person.full_name', '@person.full_name:title'),
            'name'
        );
        $memberList->addColumn(new TemplateItem($this->container, '@school.school', '@school.school:title'), 'school');
        /** @phpstan-var RelatedTable<TeamModel2,TeamTeacherModel> $teacherList */
        $teacherList = $this->addRow(
            new RelatedTable(
                $this->container,
                fn(TeamModel2 $team): iterable => $team->getTeachers(),  //@phpstan-ignore-line
                new Title(null, _('Teachers'))
            ),
            'teachers'
        );
        $teacherList->addColumn(
            new TemplateItem($this->container, '@person.full_name', '@person.full_name:title'), //@phpstan-ignore-line
            'name'
        );
        $this->addPresenterButton(
            ':Event:TeamApplication:detail',
            'detail',
            _('Detail'),
            false,
            ['id' => 'fyziklani_team_id']
        );
    }

    /**
     * @phpstan-return TypedGroupedSelection<TeamModel2>
     */
    protected function getModels(): TypedGroupedSelection
    {
        $query = $this->event->getTeams();
        foreach ($this->filterParams as $key => $filterParam) {
            if (is_null($filterParam)) {
                continue;
            }
            switch ($key) {
                case 'category':
                    $query->where('category', $filterParam);
                    break;
                case 'game_lang':
                    $query->where('game_lang', $filterParam);
                    break;
                case 'name':
                    $query->where('name LIKE ?', '%' . $filterParam . '%');
                    break;
                case 'state':
                    $query->where('state', $filterParam);
                    break;
                case 'team_id':
                    $query->where('fyziklani_team_id', $filterParam);
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
        $form->addSelect('game_lang', _('Game language'), $gameLang)->setPrompt(_('Select language'));

        $states = [];
        foreach (TeamState::cases() as $teamState) {
            $states[$teamState->value] = $teamState->label();
        }
        $form->addSelect('state', _('State'), $states)->setPrompt(_('Select state'));
    }
}
