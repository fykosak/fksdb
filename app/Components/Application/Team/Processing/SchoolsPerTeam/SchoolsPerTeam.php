<?php

declare(strict_types=1);

namespace FKSDB\Components\Application\Team\Processing\SchoolsPerTeam;

use FKSDB\Components\Application\Team\TeamForm;
use FKSDB\Components\EntityForms\Processing\Preprocessing;
use FKSDB\Models\ORM\Models\EventModel;
use FKSDB\Models\ORM\Models\Fyziklani\TeamModel2;
use Fykosak\NetteORM\Model\Model;
use Nette\DI\Container;
use Nette\Forms\Form;

/**
 * @phpstan-extends Preprocessing<TeamModel2,array{team:array{category:string,name:string}}>
 */
final class SchoolsPerTeam extends Preprocessing
{
    private EventModel $event;

    public function __construct(Container $container, EventModel $event)
    {
        parent::__construct($container);
        $this->event = $event;
    }

    public function __invoke(array $values, Form $form, ?Model $model): array
    {
        $members = TeamForm::getFormMembers($form);
        $schools = [];
        foreach ($members as $member) {
            $school = $member->getHistory($this->event->getContestYear())->school;
            if (!isset($schools[$school->school_id])) {
                $schools[$school->school_id] = $school;
            }
        }
        if (count($schools) > 2) {
            throw new SchoolsPerTeamException($schools);
        }
        return $values;
    }
}
