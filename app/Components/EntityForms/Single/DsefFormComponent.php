<?php

declare(strict_types=1);

namespace FKSDB\Components\EntityForms\Single;

use FKSDB\Components\Forms\Containers\Models\ReferencedPersonContainer;
use FKSDB\Models\ORM\Models\Schedule\ScheduleGroupType;
use FKSDB\Modules\Core\BasePresenter;

/**
 * @method BasePresenter getPresenter($need = true)
 * @phpstan-import-type EvaluatedFieldsDefinition from ReferencedPersonContainer
 */
final class DsefFormComponent extends SingleFormComponent
{
    /**
     * @phpstan-return EvaluatedFieldsDefinition
     */
    final protected function getPersonFieldsDefinition(): array
    {
        return [
            'person' => [
                'other_name' => ['required' => true],
                'family_name' => ['required' => true],
            ],
            'person_info' => [
                'email' => ['required' => true],
                'born' => ['required' => true],
                'id_number' => ['required' => true],
            ],
            'person_schedule' => [
                'schedule' => [
                    'types' => [
                        ScheduleGroupType::DSEFMorning,
                        ScheduleGroupType::DSEFAfternoon,
                        ScheduleGroupType::DSEFAllDay,
                    ],
                    'required' => false,
                    'label' => _('Schedule'),
                ],
                'accommodation' => [
                    'types' => [ScheduleGroupType::Accommodation],
                    'required' => false,
                    'label' => _('Accommodation'),
                ],
            ]
        ];
    }

    /**
     * @phpstan-return array<string, array<string, mixed>>
     */
    final protected function getParticipantFieldsDefinition(): array
    {
        return [
            'lunch_count' => ['required' => false],
        ];
    }
}

// @var ReferencedId<PersonModel> $personContainer
// $personContainer = $form->getComponent('person_id');

// @var ScheduleContainer $dsefMorning
// $dsefMorning = $personContainer->referencedContainer['person_schedule'][ScheduleGroupType::DSEF_MORNING];
// @var ScheduleContainer $dsefAfternoon
// $dsefAfternoon = $personContainer->referencedContainer['person_schedule'][ScheduleGroupType::DSEF_AFTERNOON];
// @var ScheduleContainer $dsefAllDay
// $dsefAllDay = $personContainer->referencedContainer['person_schedule'][ScheduleGroupType::DSEF_ALL_DAY];
// $halfDayComponents = [];
// foreach ($dsefMorning->getComponents() as $morningSelect) {
//     $halfDayComponents[] = $morningSelect;
// }
// foreach ($dsefAfternoon->getComponents() as $afternoonSelect) {
//     $halfDayComponents[] = $afternoonSelect;
// }
// @var SelectBox $allDaySelect
// TODO!!!!
// foreach ($dsefAllDay->getComponents() as $allDaySelect) {
// @var SelectBox[] $halfDayComponents
// foreach ($halfDayComponents as $halfDayComponent) {
//     $allDaySelect->addConditionOn($halfDayComponent, Form::Filled)
//         ->addRule(
//             Form::Blank,
//             _('You must register both morning and afternoon groups or only the all day group.')
//         );
//     $allDaySelect->addConditionOn($halfDayComponent, Form::Blank)
//         ->addRule(
//             Form::Filled,
//             _('You must register both morning and afternoon groups or only the all day group.')
//         );
//     $halfDayComponent->addConditionOn($allDaySelect, Form::Filled)
//         ->addRule(
//             Form::Blank,
//             _('You must register both morning and afternoon groups or only the all day group.')
//         );
//     $halfDayComponent->addConditionOn($allDaySelect, Form::Blank)
//         ->addRule(
//             Form::Filled,
//             _('You must register both morning and afternoon groups or only the all day group.')
//         );
// }
// }
