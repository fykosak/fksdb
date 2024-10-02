<?php

declare(strict_types=1);

namespace FKSDB\Components\Forms\Containers;

use FKSDB\Components\Forms\Containers\Models\ContainerWithOptions;
use FKSDB\Models\ORM\Models\EventModel;
use FKSDB\Models\ORM\Models\PaymentModel;
use FKSDB\Models\ORM\Models\PersonModel;
use FKSDB\Models\ORM\Models\Schedule\PersonScheduleModel;
use FKSDB\Models\ORM\Models\Schedule\SchedulePaymentModel;
use FKSDB\Models\ORM\Services\Schedule\PersonScheduleService;
use FKSDB\Modules\Core\Language;
use Fykosak\Utils\Localization\GettextTranslator;
use Nette\DI\Container;
use Nette\Forms\Controls\Checkbox;
use Nette\Utils\Html;

class PersonPaymentContainer extends ContainerWithOptions
{
    private PersonScheduleService $personScheduleService;
    private bool $isOrganizer;
    private ?PaymentModel $model;
    private EventModel $event;
    private ?PersonModel $loggedPerson;
    private GettextTranslator $translator;

    /**
     * @throws \Exception
     */
    public function __construct(
        Container $container,
        EventModel $event,
        ?PersonModel $loggedPerson,
        bool $isOrganizer,
        ?PaymentModel $model
    ) {
        parent::__construct($container);
        $this->isOrganizer = $isOrganizer;
        $this->event = $event;
        $this->loggedPerson = $loggedPerson;
        $this->model = $model;
        $this->configure();
    }

    final public function injectServicePersonSchedule(
        PersonScheduleService $personScheduleService,
        GettextTranslator $translator
    ): void {
        $this->personScheduleService = $personScheduleService;
        $this->translator = $translator;
    }

    /**
     * @throws \Exception
     */
    protected function configure(): void
    {
        $query = $this->personScheduleService->getTable()
            ->where('schedule_item.schedule_group.event_id', $this->event->event_id);
        if (!$this->isOrganizer) {
            $persons = $this->loggedPerson->getEventRelatedPersons($this->event);
            $query->where('person.person_id', array_map(fn(PersonModel $person): int => $person->person_id, $persons));
        }
        $query->order('person.family_name ,person_id');
        $lastPersonId = null;
        $container = null;
        /** @var PersonScheduleModel $model */
        foreach ($query as $model) {
            if (!$model->schedule_item->payable) {
                continue;
            }
            if ($model->person_id !== $lastPersonId) {
                $container = new ContainerWithOptions($this->container);
                $this->addComponent($container, 'person' . $model->person_id);
                $container->setOption('label', $model->person->getFullName());
                $lastPersonId = $model->person_id;
            }

            $checkBox = $container->addCheckbox(
                (string)$model->person_schedule_id,
                $model->getLabel(Language::from($this->translator->lang))
                . ' ('
                . $model->schedule_item->getPrice()->__toString()
                . ')'
            );

            if (
                $model->getPayment()
                && (!isset($this->model) || $model->getPayment()->payment_id !== $this->model->payment_id)
            ) {
                $checkBox->setDisabled();
                $checkBox->setOption(
                    'description',
                    Html::el('small')->addHtml(
                        Html::el('i')->addAttributes(['class' => 'fas fa-info me-2 text-info'])
                    )->addText(
                        _('This item has already assigned another payment')
                    )
                );
            }
        }
    }

    public function setPayment(PaymentModel $payment): void
    {
        /** @var SchedulePaymentModel $row */
        foreach ($payment->getSchedulePayment() as $row) {
            /** @var Checkbox $component */
            /** @phpstan-ignore-next-line */
            $component = $this['person' . $row->person_schedule->person_id][$row->person_schedule_id];
            $component->setDefaultValue(true);
        }
    }
}
