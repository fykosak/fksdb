<?php

declare(strict_types=1);

namespace FKSDB\Components\Applications\Single\OpenForms;

use FKSDB\Components\Forms\Containers\Models\ContainerWithOptions;
use FKSDB\Components\Forms\Containers\Models\ReferencedPersonContainer;
use FKSDB\Components\Schedule\Input\ScheduleContainer;
use FKSDB\Components\Schedule\Input\ScheduleSelectBox;
use FKSDB\Models\ORM\Models\Schedule\ScheduleGroupType;
use FKSDB\Models\Schedule\PaymentDeadlineStrategy\ConstantIntervalStrategy;
use FKSDB\Modules\Core\BasePresenter;
use Fykosak\Utils\Localization\LocalizedString;
use Nette\Forms\Form;

/**
 * @method BasePresenter getPresenter($need = true)
 * @phpstan-import-type TMeta from ScheduleContainer
 * @phpstan-import-type EvaluatedFieldsDefinition from ReferencedPersonContainer
 */
final class DSEFForm extends OpenApplicationForm
{
    private const HalfDayIds = [196, 197];
    private const FullDayIds = [198];
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

        ];
    }

    protected function getScheduleDefinition(): ?array
    {
        return [
            'excursion' => [
                'types' => [ScheduleGroupType::from(ScheduleGroupType::Excursion)],
                'required' => false,
                'label' => _('Excursion'),
            ],
            'accommodation' => [
                'types' => [ScheduleGroupType::from(ScheduleGroupType::Accommodation)],
                'required' => false,
                'label' => _('Accommodation'),
                'paymentDeadline' => new ConstantIntervalStrategy(
                    \DateInterval::createFromDateString('+14days')
                )
            ],
            'food' => [
                'types' => [ScheduleGroupType::from(ScheduleGroupType::Food)],
                'required' => false,
                'label' => _('Food'),
                'paymentDeadline' => new ConstantIntervalStrategy(
                    \DateInterval::createFromDateString('+14days')
                )
            ],
        ];
    }

    protected function configureForm(Form $form): void
    {
        parent::configureForm($form);;
        /**  @var ScheduleContainer $scheduleContainer */
        $scheduleContainer = $form['event_participant'][self::ScheduleContainer]['excursion']; // @phpstan-ignore-line
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
        /**  @var ScheduleSelectBox $allDaySelect */
        foreach ($fullDayComponents as $allDaySelect) {
            /**  @var ScheduleSelectBox[] $halfDayComponents */
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
        return [];
    }
}
