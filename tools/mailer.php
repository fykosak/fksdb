<?php

declare(strict_types=1);

use FKSDB\Models\ORM\Models\ModelEmailMessage;
use FKSDB\Models\ORM\Services\Exceptions\UnsubscribedEmailException;
use FKSDB\Models\ORM\Services\ServiceEmailMessage;
use FKSDB\Models\ORM\Services\ServiceUnsubscribedEmail;
use Nette\DI\Container;
use Nette\Mail\Mailer;
use Tracy\Debugger;

const SAFE_LIMIT = 500;

/** @var Container $container */
$container = require __DIR__ . '/bootstrap.php';
set_time_limit(60);
if (!$container->getParameters()['spamMailer'] || !$container->getParameters()['spamMailer']['enabled']) {
    exit(0);
}
/** @var Mailer $mailer */
$mailer = $container->getByType(Mailer::class);

/** @var ServiceEmailMessage $serviceEmailMessage */
$serviceEmailMessage = $container->getByType(ServiceEmailMessage::class);
$serviceUnsubscribedEmail = $container->getByType(ServiceUnsubscribedEmail::class);
$argv = $_SERVER['argv'];
$query = $serviceEmailMessage->getMessagesToSend($argv[1] ?(int)$argv[1]:(int) $container->getParameters()['spamMailer']['defaultLimit']);
$counter = 0;
/** @var ModelEmailMessage $model */
foreach ($query as $model) {
    $counter++;
    if ($counter > SAFE_LIMIT) {
        Debugger::log('Message limit reached.', 'mailer-exceptions');
        break;
    }
    try {
        $serviceUnsubscribedEmail->checkEmail($model->recipient);
        $message = $model->toMessage();
        $mailer->send($message);
        $serviceEmailMessage->updateModel($model, ['state' => ModelEmailMessage::STATE_SENT, 'sent' => new DateTime()]);
    } catch (UnsubscribedEmailException $exception) {
        $serviceEmailMessage->updateModel($model, ['state' => ModelEmailMessage::STATE_REJECTED]);
        Debugger::log($exception, 'mailer-exceptions-unsubscribed');
    } catch (Throwable $exception) {
        $serviceEmailMessage->updateModel($model, ['state' => ModelEmailMessage::STATE_FAILED]);
        Debugger::log($exception, 'mailer-exceptions');
    }
}
