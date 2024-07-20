<?php

declare(strict_types=1);

namespace FKSDB\Models\Email;

use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Models\ORM\Models\AuthTokenType;
use FKSDB\Models\ORM\Models\EmailMessageModel;
use FKSDB\Models\ORM\Models\EmailMessageState;
use FKSDB\Models\ORM\Models\PersonEmailPreferenceModel;
use FKSDB\Models\ORM\Services\AuthTokenService;
use FKSDB\Models\ORM\Services\EmailMessageService;
use FKSDB\Models\ORM\Services\Exceptions\RejectedEmailException;
use FKSDB\Models\ORM\Services\LoginService;
use FKSDB\Models\ORM\Services\PersonService;
use FKSDB\Models\ORM\Services\UnsubscribedEmailService;
use Nette\DI\Container;
use Nette\InvalidStateException;
use Nette\Mail\Mailer;
use Nette\Mail\Message;
use Nette\Utils\DateTime;
use Tracy\Debugger;

class SenderFactory
{
    private UnsubscribedEmailService $unsubscribedEmailService;
    private EmailMessageService $emailMessageService;
    private TemplateFactory $templateFactory;
    private AuthTokenService $authTokenService;
    private LoginService $loginService;
    private Container $container;
    private Mailer $mailer;
    private PersonService $personService;

    public function __construct(
        UnsubscribedEmailService $unsubscribedEmailService,
        EmailMessageService $emailMessageService,
        TemplateFactory $templateFactory,
        AuthTokenService $authTokenService,
        LoginService $loginService,
        Container $container,
        Mailer $mailer,
        PersonService $personService
    ) {
        $this->templateFactory = $templateFactory;
        $this->unsubscribedEmailService = $unsubscribedEmailService;
        $this->authTokenService = $authTokenService;
        $this->loginService = $loginService;
        $this->container = $container;
        $this->mailer = $mailer;
        $this->emailMessageService = $emailMessageService;
        $this->personService = $personService;
    }

    public function send(EmailMessageModel $model): void
    {
        try {
            $message = $this->createMessage($model);
            $this->mailer->send($message);
            $this->emailMessageService->storeModel([
                'state' => EmailMessageState::SENT,
                'sent' => new DateTime(),
            ], $model);
        } catch (RejectedEmailException $exception) {
            $this->emailMessageService->storeModel(['state' => EmailMessageState::REJECTED], $model);
            Debugger::log($exception, 'mailer-exceptions-unsubscribed');
        } catch (\Throwable $exception) {
            $this->emailMessageService->storeModel(['state' => EmailMessageState::FAILED], $model);
            Debugger::log($exception, 'mailer-exceptions');
        }
    }

    /**
     * @throws RejectedEmailException
     * @throws BadTypeException
     */
    private function createMessage(EmailMessageModel $model): Message
    {
        $message = new Message();
        $message->setSubject($model->subject);
        // check if email is not in unsubscribed or use no allowed this type
        $this->resolveRecipient($model);
        $this->resolvePreferences($model);
        $message->addTo($this->getEmailAddress($model));

        if (!is_null($model->blind_carbon_copy)) {
            $message->addBcc($model->blind_carbon_copy);
        }
        if (!is_null($model->carbon_copy)) {
            $message->addCc($model->carbon_copy);
        }
        $message->setFrom($model->sender);
        $message->addReplyTo($model->reply_to);
        $text = $this->resolveText($model);
        $message->setHtmlBody($text);

        return $message;
    }

    /**
     * @throws BadTypeException
     */
    private function resolveText(EmailMessageModel $model): string
    {
        if ($model->topic->isSpam()) {
            if ($model->person) {
                $login = $model->person->getLogin();
                if (!$login) {
                    $login = $this->loginService->createLogin($model->person);
                }
                $token = $this->authTokenService->createToken(
                    $login,
                    AuthTokenType::from(AuthTokenType::UNSUBSCRIBE),
                    null,
                );
            } else {
                $code = openssl_encrypt(
                    $model->recipient,
                    'aes-256-cbc',
                    $this->container->getParameters()['spamHash']
                );
                if ($code === false) {
                    throw new InvalidStateException(_('Cannot encrypt code'));
                }
            }
        }
        return $this->templateFactory->addContainer($model, [
            'text' => $model->text,
            'topic' => $model->topic,
            'token' => $token ?? null,
            'code' => $code ?? null,
        ]);
    }

    public function resolveRecipient(EmailMessageModel $model): void
    {
        if ($model->recipient) {
            $person = $this->personService->findByEmail($model->recipient);
            if ($person) {
                $this->emailMessageService->storeModel([
                    'recipient_person_id' => $person->person_id,
                    'recipient' => null,
                ], $model);
            }
        }
    }
    /**
     * @throws RejectedEmailException
     */
    private function resolvePreferences(EmailMessageModel $model): void
    {
        if (isset($model->recipient_person_id)) {
            $preferenceType = $model->topic->mapToPreference();
            if ($preferenceType) {
                /** @var PersonEmailPreferenceModel|null $preference */
                $preference = $model->person->getEmailPreferences()->where('option', $preferenceType)->fetch();
                if ($preference && !$preference->value) {
                    throw new RejectedEmailException();
                }
            }
        } else {
            $row = $this->unsubscribedEmailService->getTable()
                ->where('email_hash = SHA1(?)', $model->recipient)
                ->fetch();
            if ($row) {
                throw new RejectedEmailException();
            }
        }
    }

    private function getEmailAddress(EmailMessageModel $model): string
    {
        if (isset($model->recipient_person_id)) {
            return $model->person->getInfo()->email;
        } else {
            return $model->recipient;
        }
    }
}
