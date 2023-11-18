<?php

declare(strict_types=1);

namespace FKSDB\Components\EntityForms\Single;

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

/**
 * @method BasePresenter getPresenter($need = true)
 * @phpstan-import-type EvaluatedFieldsDefinition from ReferencedPersonContainer
 */
final class DsefFormComponent extends SingleFormComponent
{
    /**
     * @throws BadTypeException
     * @throws OmittedControlException
     */
    protected function configureForm(Form $form): void
    {
        parent::configureForm($form);

        /** @var ReferencedId<PersonModel> $personContainer */
        $personContainer = $form->getComponent('person_id');

        /** @var ScheduleContainer $dsefMorning */
        // @phpstan-ignore-next-line
        //$dsefMorning = $personContainer->referencedContainer['person_schedule'][ScheduleGroupType::DSEF_MORNING];
        /** @var ScheduleContainer $dsefAfternoon */
        // @phpstan-ignore-next-line
        //$dsefAfternoon = $personContainer->referencedContainer['person_schedule'][ScheduleGroupType::DSEF_AFTERNOON];
        /** @var ScheduleContainer $dsefAllDay */
        // @phpstan-ignore-next-line
        //$dsefAllDay = $personContainer->referencedContainer['person_schedule'][ScheduleGroupType::DSEF_ALL_DAY];
        $halfDayComponents = [];
        foreach ($dsefMorning->getComponents() as $morningSelect) {
            $halfDayComponents[] = $morningSelect;
        }
        foreach ($dsefAfternoon->getComponents() as $afternoonSelect) {
            $halfDayComponents[] = $afternoonSelect;
        }
        /** @var SelectBox $allDaySelect */
        // TODO!!!!
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
                        ScheduleGroupType::Schedule,
                    ],
                    'meta' => ['required' => false, 'label' => _('Schedule')],
                ],
                'accommodation' => [
                    'types' => [ScheduleGroupType::Accommodation],
                    'meta' => ['required' => false, 'label' => _('Accommodation')],
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
            'lunch_count' => ['required' => false]
        ];
    }
}
