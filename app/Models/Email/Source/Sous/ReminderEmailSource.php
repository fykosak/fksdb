<?php

declare(strict_types=1);

namespace FKSDB\Models\Email\Source\Sous;

use FKSDB\Models\ORM\Models\EmailMessageTopic;
use FKSDB\Models\Email\Source\MailSource;
use FKSDB\Models\Email\Source\EmailSource;
use FKSDB\Models\ORM\Models\EventParticipantModel;
use FKSDB\Models\ORM\Models\EventParticipantStatus;
use FKSDB\Models\ORM\Models\PersonModel;
use FKSDB\Models\ORM\Services\EventParticipantService;
use FKSDB\Modules\Core\Language;
use Fykosak\Utils\Localization\LocalizedString;
use Fykosak\Utils\UI\Title;
use Nette\DI\Container;

/**
 * @phpstan-extends EmailSource<array{person:PersonModel,model:EventParticipantModel,token:null},array{event_id:int}>
 */
final class ReminderEmailSource extends EmailSource
{
    protected EventParticipantService $eventParticipantService;
    private int $number;

    public function __construct(Container $container, int $number)
    {
        parent::__construct($container);
        $this->number = $number;
    }

    public function getExpectedParams(): array
    {
        return [
            'event_id' => 'int',
        ];
    }

    public function injectService(EventParticipantService $eventParticipantService): void
    {
        $this->eventParticipantService = $eventParticipantService;
    }

    protected function getSource(array $params): array
    {
        $source = $this->eventParticipantService->getTable()
            ->where('event_id', $params['event_id'])
            ->where('status', [EventParticipantStatus::INVITED, EventParticipantStatus::SPARE]);
        $data = [];
        /** @var EventParticipantModel $participant */
        foreach ($source as $participant) {
            $data[] = [
                'template' => [
                    'file' => __DIR__ . DIRECTORY_SEPARATOR . 'reminder' . $this->number . '.latte',
                    'data' => [
                        'person' => $participant->person,
                        'model' => $participant,
                        'token' => null,
                    ],
                ],
                'lang' => Language::from(Language::CS),
                'data' => [
                    'recipient_person_id' => $participant->person_id,
                    'blind_carbon_copy' => 'Soustředění FYKOSu <soustredeni@fykos.cz>',
                    'sender' => 'Soustředění FYKOSu <soustredeni@fykos.cz>',
                    'topic' => EmailMessageTopic::from(EmailMessageTopic::Contest),
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

    public function description(): LocalizedString//@phpstan-ignore-line
    {
        return new LocalizedString(['cs' => '', 'en' => '']);
    }
}
