<?php

declare(strict_types=1);

namespace FKSDB\Components\Applications\Team\Forms\Processing\SchoolRequirement;

use FKSDB\Components\Applications\Team\Forms\NoMemberException;
use FKSDB\Components\Applications\Team\Forms\TeamForm;
use FKSDB\Components\EntityForms\Processing\Preprocessing;
use FKSDB\Models\ORM\Models\EventModel;
use FKSDB\Models\ORM\Models\Fyziklani\TeamModel2;
use FKSDB\Models\ORM\Models\StudyYear;
use Fykosak\NetteORM\Model\Model;
use Nette\DI\Container;
use Nette\Forms\Form;

/**
 * @phpstan-extends Preprocessing<TeamModel2,array{team:array{category:string,name:string}}>
 */
final class FOLSchoolRequirementProcessing extends Preprocessing
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
        if (!count($members)) {
            throw new NoMemberException();
        }
        foreach ($members as $member) {
            $history = $member->getHistory($this->event->getContestYear());
            if ($history->study_year_new->value !== StudyYear::None && !isset($history->school_id)) {
                throw new SchoolRequirementProcessingException($history->study_year_new, $member);
            }
        }
        return $values;
    }
}