<?php

use FKSDB\ORM\Models\ModelEmailMessage;
use FKSDB\ORM\Services\ServiceEmailMessage;
use Nette\DI\Container;
use Nette\Mail\IMailer;
use Nette\Mail\Mailer;
use Tracy\Debugger;

const SAFE_LIMIT = 500;

/**
 * @var Container $container
 */
$container = require __DIR__ . '/bootstrap.php';
set_time_limit(60);
if (!$container->getParameters()['spamMailer'] || !$container->getParameters()['spamMailer']['enabled']) {
    exit(0);
}
/**
 * @var IMailer $mailer
 */
$mailer = $container->getByType(Mailer::class);

/**
 * @var ServiceEmailMessage $serviceEmailMessage
 */
$serviceEmailMessage = $container->getByType(ServiceEmailMessage::class);
$argv = $_SERVER['argv'];
$query = $serviceEmailMessage->getMessagesToSend($argv[1] ?: $container->getParameters()['spamMailer']['defaultLimit']);
$counter = 0;
/**
 * @var ModelEmailMessage $model
 */
foreach ($query as $model) {
    $counter++;
    if ($counter > SAFE_LIMIT) {
        Debugger::log('Message limit reached.', 'mailer-exceptions');
        break;
    }
    try {
        $message = $model->toMessage();
        $mailer->send($message);
        $model->update(['state' => ModelEmailMessage::STATE_SENT, 'sent' => new DateTime()]);
    } catch (Exception $e) {
        $model->update(['state' => ModelEmailMessage::STATE_FAILED]);
        Debugger::log($e, 'mailer-exceptions');
    }
}
