<?php

declare(strict_types=1);

namespace FKSDB\Models\Email\Source\Sous;

use FKSDB\Models\Email\UIEmailSource;
use FKSDB\Models\ORM\Models\EmailMessageTopic;
use FKSDB\Models\ORM\Models\EventModel;
use FKSDB\Models\ORM\Models\EventParticipantModel;
use FKSDB\Models\ORM\Models\EventParticipantStatus;
use FKSDB\Models\ORM\Services\EventParticipantService;
use FKSDB\Models\ORM\Services\EventService;
use FKSDB\Modules\Core\Language;
use Fykosak\Utils\Localization\LocalizedString;
use Fykosak\Utils\UI\Title;
use Nette\DI\Container;
use Nette\Forms\Form;

/**
 * @phpstan-extends UIEmailSource<array{
 *      model: EventParticipantModel,
 * },array{event_id:int}>
 */
final class ReminderEmail extends UIEmailSource
{
    private EventParticipantService $eventParticipantService;
    private EventService $eventService;

    private int $number;

    public function __construct(Container $container, int $number)
    {
        parent::__construct($container);
        $this->number = $number;
    }

    public function injectService(
        EventParticipantService $eventParticipantService,
        EventService $eventService
    ): void {
        $this->eventParticipantService = $eventParticipantService;
        $this->eventService = $eventService;
    }

    public function creatForm(Form $form): void
    {
        $events = [];
        /** @var EventModel $event */
        foreach ($this->eventService->getTable()->where('event_type_id', [4, 5])->order('begin DESC') as $event) {
            $events[$event->event_id] = sprintf(
                '(%d) %s',
                $event->begin->format('Y'),
                $event->getName()->getText('cs')
            );
        }
        $form->addSelect('event_id', _('Event'), $events);
    }
    protected function getSource(array $params): array
    {
        $source = $this->eventParticipantService->getTable()
            ->where('event_id', $params['event_id'])
            ->where('status', [EventParticipantStatus::Invited->value, EventParticipantStatus::Spare->value]);
        $data = [];
        /** @var EventParticipantModel $participant */
        foreach ($source as $participant) {
            $data[] = [
                'template' => [
                    'file' => __DIR__ . DIRECTORY_SEPARATOR . 'reminder' . $this->number . '.latte',
                    'data' => [
                        'model' => $participant,
                    ],
                ],
                'data' => [
                    'recipient_person_id' => $participant->person_id,
                    'blind_carbon_copy' => 'Soustředění FYKOSu <soustredeni@fykos.cz>',
                    'sender' => 'Soustředění FYKOSu <soustredeni@fykos.cz>',
                    'topic' => EmailMessageTopic::from(EmailMessageTopic::Fykos),
                    'lang' => Language::from(Language::CS),
                ],
            ];
        }
        return $data;
    }

    public function title(): Title
    {
        return new Title(null, sprintf(_('Reminder %d'), $this->number));
    }

    public function description(): LocalizedString
    {
        return new LocalizedString(['cs' => '', 'en' => '']);
    }
}
