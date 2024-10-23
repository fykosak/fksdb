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
    private const HALF_DAY_IDS = [196, 197];
    private const FULL_DAY_IDS = [198];
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
                        'cs' => 'Kvůli vstupu na pracoviště a případně ubytování',
                        'en' => 'For entering laboratories and possibly accommodation',
                    ])
                ],
                'id_number' => [
                    'required' => true,
                    'reason' => new LocalizedString([
                        'cs' => 'Kvůli vstupu na pracoviště a případně ubytování',
                        'en' => 'For entering laboratories and possibly accommodation',
                    ])
                ],
            ],

        ];
    }

    protected function getScheduleDefinition(): ?array
    {
        return [
            'excursion' => [
                'types' => [ScheduleGroupType::Excursion],
                'required' => false,
                'label' => _('Excursion'),
            ],
            'accommodation' => [
                'types' => [ScheduleGroupType::Accommodation],
                'required' => false,
                'label' => _('Accommodation'),
                'paymentDeadline' => new ConstantIntervalStrategy(
                    \DateInterval::createFromDateString('+14days'),
                    new \DateTime('2024-10-18 23:59:59')
                )
            ],
            'food' => [
                'types' => [ScheduleGroupType::Food],
                'required' => false,
                'label' => _('Food'),
                'paymentDeadline' => new ConstantIntervalStrategy(
                    \DateInterval::createFromDateString('+14days'),
                    new \DateTime('2024-10-18 23:59:59')
                )
            ],
        ];
    }

    protected function configureForm(Form $form): void
    {
        parent::configureForm($form);
        /**  @var ScheduleContainer $scheduleContainer */
        $scheduleContainer = $form['event_participant'][self::ScheduleContainer]['excursion']; // @phpstan-ignore-line
        $halfDayComponents = [];
        $fullDayComponents = [];
        /** @var ContainerWithOptions $component */
        foreach ($scheduleContainer->getComponents() as $component) {
            foreach (self::HALF_DAY_IDS as $id) {
                $halfDayComponents[] = $component->getComponent((string)$id);
            }
            foreach (self::FULL_DAY_IDS as $id) {
                $fullDayComponents[] = $component->getComponent((string)$id);
            }
        }
        /**  @var ScheduleSelectBox $fullDaySelect */
        foreach ($fullDayComponents as $fullDaySelect) {
            /**  @var ScheduleSelectBox[] $halfDayComponents */
            foreach ($halfDayComponents as $halfDayComponent) {
                $fullDaySelect->addConditionOn($halfDayComponent, Form::Filled)
                    ->addRule(
                        Form::Blank,
                        _('You must register to both morning and afternoon group or only the all-day group.')
                    );
                $fullDaySelect->addConditionOn($halfDayComponent, Form::Blank)
                    ->addRule(
                        Form::Filled,
                        _('You must register to both morning and afternoon group or only the all-day group.')
                    );
                $halfDayComponent->addConditionOn($fullDaySelect, Form::Filled)
                    ->addRule(
                        Form::Blank,
                        _('You must register to both morning and afternoon group or only the all-day group.')
                    );
                $halfDayComponent->addConditionOn($fullDaySelect, Form::Blank)
                    ->addRule(
                        Form::Filled,
                        _('You must register to both morning and afternoon group or only the all-day group.')
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
