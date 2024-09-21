<?php

declare(strict_types=1);

namespace FKSDB\Components\Applications\Single\OpenForms;

use FKSDB\Components\Forms\Containers\ModelContainer;
use FKSDB\Components\Forms\Containers\Models\ContainerWithOptions;
use FKSDB\Components\Forms\Containers\Models\ReferencedPersonContainer;
use FKSDB\Components\Forms\Controls\ReferencedId;
use FKSDB\Components\Schedule\Input\ScheduleContainer;
use FKSDB\Components\Schedule\Input\ScheduleGroupField;
use FKSDB\Models\ORM\Models\PersonModel;
use FKSDB\Models\ORM\Models\Schedule\ScheduleGroupType;
use FKSDB\Modules\Core\BasePresenter;
use Fykosak\Utils\Localization\LocalizedString;
use Nette\Forms\Form;

/**
 * @method BasePresenter getPresenter($need = true)
 * @phpstan-import-type EvaluatedFieldsDefinition from ReferencedPersonContainer
 */
final class DSEFForm extends OpenApplicationForm
{
    private const HalfDayIds = [149, 150];
    private const FullDayIds = [152];
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
                'born' => [
                    'required' => true,
                    'reason' => new LocalizedString([
                        'cs' => 'Kvôli vstupu na pracoviská a pripadnému ubytovaniu',
                        'en' => '',
                    ])
                ],
                'id_number' => [
                    'required' => true,
                    'reason' => new LocalizedString([
                        'cs' => 'Kvôli vstupu na pracoviská a pripadnému ubytovaniu',
                        'en' => '',
                    ])
                ],
            ],
            'person_schedule' => [
                'schedule' => [
                    'types' => [
                        ScheduleGroupType::Excursion,
                    ],
                    'required' => false,
                    'label' => _('Excursion'),
                ],
                'accommodation' => [
                    'types' => [ScheduleGroupType::Accommodation],
                    'required' => false,
                    'label' => _('Accommodation'),
                ],
                'food' => [
                    'types' => [ScheduleGroupType::Food],
                    'required' => false,
                    'label' => _('Food'),
                ],
            ]
        ];
    }

    protected function configureForm(Form $form): void
    {
        parent::configureForm($form);
        /** @var ModelContainer $container */
        $container = $form->getComponent('event_participant');
        /**  @var ReferencedId<PersonModel> $personContainer */
        $personContainer = $container->getComponent('person_id');
        /**  @var ScheduleContainer $scheduleContainer */
        $scheduleContainer = $personContainer->referencedContainer['person_schedule']['schedule'];
        $halfDayComponents = [];
        $fullDayComponents = [];
        /** @var ContainerWithOptions $component */
        foreach ($scheduleContainer->getComponents() as $component) {
            foreach (self::HalfDayIds as $id) {
                $halfDayComponents[] = $component->getComponent((string)$id);
            }
            foreach (self::FullDayIds as $id) {
                $fullDayComponents[] = $component->getComponent((string)$id);
            }
        }
        /**  @var ScheduleGroupField $allDaySelect */
        foreach ($fullDayComponents as $allDaySelect) {
            /**  @var ScheduleGroupField[] $halfDayComponents */
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
     * @phpstan-return array<string, array<string, mixed>>
     */
    final protected function getParticipantFieldsDefinition(): array
    {
        return [
            'lunch_count' => ['required' => false],
        ];
    }
}

