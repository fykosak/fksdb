<?php

declare(strict_types=1);

namespace FKSDB\Models\Email\Transitions;

use FKSDB\Models\ORM\Models\EmailMessageState;
use FKSDB\Models\ORM\Services\EmailMessageService;
use FKSDB\Models\Transitions\Holder\EmailHolder;
use FKSDB\Models\Transitions\Statement;
use Nette\Mail\Mailer;
use Nette\Mail\Message;
use Nette\Utils\DateTime;

/**
 * @phpstan-implements Statement<void,EmailHolder>
 */
final class SendEmail implements Statement
{
    private EmailMessageService $emailMessageService;
    private Mailer $mailer;

    public function __construct(
        EmailMessageService $emailMessageService,
        Mailer $mailer
    ) {
        $this->mailer = $mailer;
        $this->emailMessageService = $emailMessageService;
    }

    public function __invoke(...$args)
    {
        /** @var EmailHolder $holder */
        [$holder] = $args;
        $model = $holder->getModel();
        $message = new Message();
        $message->setSubject($model->subject);

        // check if is allowed by user preferences
        if (isset($model->recipient_person_id)) {
            $message->addTo($model->person->getInfo()->email);
        } else {
            $message->addTo($model->recipient);
        }
// check BCC and CC
        if (!is_null($model->blind_carbon_copy)) {
            $message->addBcc($model->blind_carbon_copy);
        }
        if (!is_null($model->carbon_copy)) {
            $message->addCc($model->carbon_copy);
        }
        $message->setFrom($model->sender);
        $message->addReplyTo($model->reply_to);


        $message->setHtmlBody($model->text);
// send email
        $this->mailer->send($message);
// change state to sent
        $this->emailMessageService->storeModel([
            'state' => EmailMessageState::Sent->value,
            'sent' => new DateTime(),
        ], $model);
    }
}
