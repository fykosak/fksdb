<?php

declare(strict_types=1);

namespace FKSDB\Components\Grids\Application;

use FKSDB\Components\Grids\Components\BaseList;
use FKSDB\Components\Grids\Components\Referenced\SimpleItem;
use FKSDB\Components\Grids\Components\Referenced\TemplateItem;
use FKSDB\Components\Grids\Components\Table\RelatedTable;
use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Models\ORM\FieldLevelPermission;
use FKSDB\Models\ORM\Models\EventModel;
use FKSDB\Models\ORM\Models\Fyziklani\TeamMemberModel;
use FKSDB\Models\ORM\Models\Fyziklani\TeamModel2;
use FKSDB\Models\ORM\Models\Fyziklani\TeamTeacherModel;
use FKSDB\Models\ORM\Models\PersonHistoryModel;
use Fykosak\Utils\UI\Title;
use Nette\DI\Container;

/**
 * @phpstan-extends BaseList<TeamModel2,array{
 *     category?:string|null,
 *     game_lang?:string|null,
 *     name?:string|null,
 *     state?:string|null,
 *     team_id?:int|null,
 *     }>
 */
final class TeamList extends BaseList
{
    use TeamTrait;

    public function __construct(EventModel $event, Container $container)
    {
        parent::__construct($container, FieldLevelPermission::ALLOW_FULL);
        $this->event = $event;
    }

    /**
     * @throws BadTypeException
     * @throws \ReflectionException
     */
    protected function configure(): void
    {
        $this->filtered = true;
        $this->paginate = false;
        $this->counter = true;
        $this->classNameCallback = fn(TeamModel2 $team): string => 'alert alert-' . $team->state->getBehaviorType();
        $this->setTitle(// @phpstan-ignore-line
            new TemplateItem(// @phpstan-ignore-line
                $this->container,
                '<h4>@fyziklani_team.name (@fyziklani_team.fyziklani_team_id)</h4>'
            )
        );
        $row = $this->createRow();
        $row->addComponent(
            new SimpleItem($this->container, '@fyziklani_team.state'),
            'state'
        );
        $row->addComponent(
            new SimpleItem($this->container, '@fyziklani_team.category'),
            'category'
        );
        $row->addComponent(
            new SimpleItem($this->container, '@fyziklani_team.game_lang'),
            'lang'
        );
        $row->addComponent(
            new SimpleItem($this->container, '@fyziklani_team.phone'),
            'phone'
        );
        $memberList = $this->addRow(
            new RelatedTable(
                $this->container,
                /**
                 * @phpstan-return PersonHistoryModel[]
                 * @phpstan-ignore-next-line
                 */
                function (TeamModel2 $team): array {
                    $members = [];
                    /** @var TeamMemberModel $member */
                    foreach ($team->getMembers() as $member) {
                        $members[] = $member->getPersonHistory();
                    }
                    return $members;
                },
                new Title(null, _('Members'))
            ),
            'members'
        );
        $memberList->addTableColumn( //@phpstan-ignore-line
            new SimpleItem($this->container, '@person.full_name'), //@phpstan-ignore-line
            'name'
        );
        $memberList->addTableColumn( //@phpstan-ignore-line
            new SimpleItem($this->container, '@school.school'), //@phpstan-ignore-line
            'school'
        );
        /** @phpstan-var RelatedTable<TeamModel2,TeamTeacherModel> $teacherList */
        $teacherList = $this->addRow(
            new RelatedTable(
                $this->container,
                fn(TeamModel2 $team): iterable => $team->getTeachers(),  //@phpstan-ignore-line
                new Title(null, _('Teachers'))
            ),
            'teachers'
        );
        $teacherList->addTableColumn( //@phpstan-ignore-line
            new SimpleItem($this->container, '@person.full_name'), //@phpstan-ignore-line
            'name'
        );
        $this->addPresenterButton(
            ':Event:TeamApplication:detail',
            'detail',
            _('button.detail'),
            false,
            ['id' => 'fyziklani_team_id']
        );
    }
}
