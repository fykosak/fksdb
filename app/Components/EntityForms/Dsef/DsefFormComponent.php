<?php

declare(strict_types=1);

namespace FKSDB\Components\EntityForms\Dsef;

use FKSDB\Components\Forms\Containers\Models\ReferencedPersonContainer;
use FKSDB\Components\Forms\Controls\ReferencedId;
use FKSDB\Components\Schedule\Input\ScheduleContainer;
use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Models\ORM\Columns\OmittedControlException;
use FKSDB\Models\ORM\Models\PersonModel;
use FKSDB\Models\ORM\Models\Schedule\ScheduleGroupType;
use FKSDB\Modules\Core\BasePresenter;
use Nette\Forms\Controls\SelectBox;
use Nette\Forms\Form;
use Nette\Neon\Exception;
use Nette\Neon\Neon;

/**
 * @method BasePresenter getPresenter($need = true)
 * @phpstan-import-type EvaluatedFieldsDefinition from ReferencedPersonContainer
 */
final class DsefFormComponent extends SingleFormComponent
{
    /**
     * @throws Exception
     * @throws BadTypeException
     * @throws OmittedControlException
     */
    protected function configureForm(Form $form): void
    {
        parent::configureForm($form);

        /** @var ReferencedId<PersonModel> $personContainer */
        $personContainer = $form->getComponent('person_id');

        /** @var ScheduleContainer $dsefMorning */
        $dsefMorning = $personContainer->referencedContainer['person_schedule'][ScheduleGroupType::DSEF_MORNING];
        /** @var ScheduleContainer $dsefAfternoon */
        $dsefAfternoon = $personContainer->referencedContainer['person_schedule'][ScheduleGroupType::DSEF_AFTERNOON];
        /** @var ScheduleContainer $dsefAllDay */
        $dsefAllDay = $personContainer->referencedContainer['person_schedule'][ScheduleGroupType::DSEF_ALL_DAY];
        $halfDayComponents = [];
        foreach ($dsefMorning->getComponents() as $morningSelect) {
            $halfDayComponents[] = $morningSelect;
        }
        foreach ($dsefAfternoon->getComponents() as $afternoonSelect) {
            $halfDayComponents[] = $afternoonSelect;
        }
        /** @var SelectBox $allDaySelect */
        foreach ($dsefAllDay->getComponents() as $allDaySelect) {
            /** @var SelectBox[] $halfDayComponents */
            foreach ($halfDayComponents as $halfDayComponent) {
                $allDaySelect->addConditionOn($halfDayComponent, Form::Filled)
                    ->addRule(
                        Form::Blank,
                        _('You must register both morning and afternoon groups or only the all day group.')
                    );
                $allDaySelect->addConditionOn($halfDayComponent, Form::Blank)
                    ->addRule(
                        Form::Filled,
                        _('You must register both morning and afternoon groups or only the all day group.')
                    );
                $halfDayComponent->addConditionOn($allDaySelect, Form::Filled)
                    ->addRule(
                        Form::Blank,
                        _('You must register both morning and afternoon groups or only the all day group.')
                    );
                $halfDayComponent->addConditionOn($allDaySelect, Form::Blank)
                    ->addRule(
                        Form::Filled,
                        _('You must register both morning and afternoon groups or only the all day group.')
                    );
            }
        }
    }

    /**
     * @throws Exception
     * @phpstan-return EvaluatedFieldsDefinition
     */
    final protected function getPersonFieldsDefinition(): array
    {
        return Neon::decodeFile(__DIR__ . DIRECTORY_SEPARATOR . 'dsef.person.neon');
    }

    /**
     * @phpstan-return array<string, array<string, mixed>>
     */
    final protected function getParticipantFieldsDefinition(): array
    {
        return [
            'lunch_count' => ['required' => false]
        ];
    }
}
