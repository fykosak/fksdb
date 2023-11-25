<?php

declare(strict_types=1);

namespace FKSDB\Components\Game\Closing;

use FKSDB\Components\Game\GameException;
use FKSDB\Components\Game\Submits\TaskCodePreprocessor;
use FKSDB\Components\Grids\Components\BaseList;
use FKSDB\Components\Grids\Components\Referenced\SimpleItem;
use FKSDB\Components\Grids\Components\Referenced\TemplateItem;
use FKSDB\Models\ORM\FieldLevelPermission;
use FKSDB\Models\ORM\Models\EventModel;
use FKSDB\Models\ORM\Models\Fyziklani\TeamModel2;
use Fykosak\NetteORM\Selection\TypedGroupedSelection;
use Fykosak\Utils\Logging\Message;
use Nette\DI\Container;
use Nette\Forms\Form;

/**
 * @phpstan-extends BaseList<TeamModel2,array{
 *     team_id?:int,
 *     code?:string,
 *     name?:string,
 * }>
 */
class TeamListComponent extends BaseList
{
    private EventModel $event;

    public function __construct(Container $container, EventModel $event)
    {
        parent::__construct($container, FieldLevelPermission::ALLOW_FULL);
        $this->event = $event;
    }

    /**
     * @phpstan-return TypedGroupedSelection<TeamModel2>
     */
    protected function getModels(): TypedGroupedSelection
    {
        $query = $this->event->getPossiblyAttendingTeams()->order('points');
        foreach ($this->filterParams as $key => $filterParam) {
            if (!$filterParam) {
                continue;
            }
            switch ($key) {
                case 'team_id':
                    $query->where('fyziklani_team_id', $filterParam);
                    break;
                case 'code':
                    $codeProcessor = new TaskCodePreprocessor($this->event);
                    try {
                        $query->where(
                            'fyziklani_team_id =? ',
                            $codeProcessor->getTeam($filterParam)->fyziklani_team_id
                        );
                    } catch (GameException $exception) {
                        $this->flashMessage(_('Wrong task code'), Message::LVL_WARNING);
                    }
                    break;
                case 'name':
                    $query->where('name LIKE ?', '%' . $filterParam . '%');
                    break;
            }
        }
        return $query;
    }

    protected function configureForm(Form $form): void
    {
        $form->addText('code', ('Task code'))->setOption('description', _('Find team using a task code'));
        $form->addText('team_id', _('Team id'));
        $form->addText('name', _('Team name'))->setOption('description', _('Works as %name%'));
    }

    protected function configure(): void
    {
        $this->filtered = true;
        $this->classNameCallback = function (TeamModel2 $team) {
            try {
                $team->canClose();
                return 'alert alert-info';
            } catch (AlreadyClosedException $exception) {
                return 'alert alert-success';
            } catch (NotCheckedSubmitsException $exception) {
                return 'alert alert-danger';
            }
        };
        $row1 = $this->createRow();
        $row1->addComponent(
            new TemplateItem($this->container, '<b>(@fyziklani_team.fyziklani_team_id) @fyziklani_team.name</b>'),
            'name'
        );
        $row1->addComponent(new SimpleItem($this->container, '@fyziklani_team.category'), 'category');
        $row1->addComponent(new SimpleItem($this->container, '@fyziklani_team.state'), 'state');

        $row2 = $this->createRow();
        $row2->addComponent(new TemplateItem($this->container, _('points: @fyziklani_team.points')), 'points');
    }
}
