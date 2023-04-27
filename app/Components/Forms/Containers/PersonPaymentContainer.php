<?php

declare(strict_types=1);

namespace FKSDB\Components\Forms\Containers;

use FKSDB\Components\Forms\Containers\Models\ContainerWithOptions;
use FKSDB\Models\Authorization\EventRole\FyziklaniTeamMemberRole;
use FKSDB\Models\Authorization\EventRole\FyziklaniTeamTeacherRole;
use FKSDB\Models\ORM\Models\EventModel;
use FKSDB\Models\ORM\Models\Fyziklani\TeamModel2;
use FKSDB\Models\ORM\Models\LoginModel;
use FKSDB\Models\ORM\Models\PaymentModel;
use FKSDB\Models\ORM\Models\PersonModel;
use FKSDB\Models\ORM\Models\Schedule\PersonScheduleModel;
use FKSDB\Models\ORM\Models\Schedule\ScheduleGroupType;
use FKSDB\Models\ORM\Models\Schedule\SchedulePaymentModel;
use FKSDB\Models\ORM\Services\Schedule\PersonScheduleService;
use Fykosak\Utils\Localization\GettextTranslator;
use Nette\DI\Container;
use Nette\Forms\Controls\Checkbox;
use Nette\Security\User;
use Nette\Utils\Html;

class PersonPaymentContainer extends ContainerWithOptions
{
    private PersonScheduleService $personScheduleService;
    private User $user;
    private bool $isOrg;
    private ?PaymentModel $model;
    private EventModel $event;

    private GettextTranslator $translator;

    /**
     * @throws \Exception
     */
    public function __construct(
        Container $container,
        EventModel $event,
        bool $isOrg,
        ?PaymentModel $model
    ) {
        parent::__construct($container);
        $this->isOrg = $isOrg;
        $this->event = $event;
        $this->model = $model;
        $this->configure();
    }

    final public function injectServicePersonSchedule(
        User $user,
        PersonScheduleService $personScheduleService,
        GettextTranslator $translator
    ): void {
        $this->user = $user;
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
        if (!$this->isOrg) {
            /** @var LoginModel $login */
            $login = $this->user->getIdentity();
            $roles = $login->person->getEventRoles($this->event);
            $teams = [];
            foreach ($roles as $role) {
                if ($role instanceof FyziklaniTeamTeacherRole) {
                    $teams += $role->teams;
                }
                if ($role instanceof FyziklaniTeamMemberRole) {
                    $teams[] = $role->member->fyziklani_team;
                }
            }
            $persons = [];
            /** @var TeamModel2 $team */
            foreach ($teams as $team) {
                $persons += $team->getPersons();
            }
            $query->where('person.person_id', array_map(fn(PersonModel $person): int => $person->person_id, $persons));
        }
        $query->order('person.family_name ,person_id');
        $lastPersonId = null;
        $container = null;
        /** @var PersonScheduleModel $model */
        foreach ($query as $model) {
            if (
                !$model->schedule_item->isPayable() ||
                !in_array(
                    $model->schedule_item->schedule_group->schedule_group_type,
                    [ScheduleGroupType::ACCOMMODATION, ScheduleGroupType::WEEKEND] // TODO to event params
                )
            ) {
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
                $model->getLabel($this->translator->lang)
                . ' ('
                . $model->schedule_item->getPrice()->__toString()
                . ')'
            );
            if (
                $model->getPayment()
                && isset($this->model)
                && $model->getPayment()->payment_id !== $this->model->payment_id
            ) {
                $checkBox->setDisabled();
                $checkBox->setOption(
                    'description',
                    Html::el('small')->addHtml(
                        Html::el('i')->addAttributes(['class' => 'fa fas fa-info me-2 text-info'])
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
            $component = $this['person' . $row->person_schedule->person_id][$row->person_schedule_id];
            $component->setDefaultValue(true);
        }
    }
}
