<?php

use FKSDB\ORM\Models\ModelEmailMessage;
use FKSDB\ORM\Services\ServiceEmailMessage;
use Nette\DI\Container;
use Nette\Mail\IMailer;
use Tracy\Debugger;

const DEFAULT_MESSAGE_LIMIT = 20;
const SAFE_LIMIT = 250;

/**
 * @var Container $container
 */
$container = require './bootstrap.php';
/**
 * @var IMailer $mailer
 */
$mailer = $container->getByType(IMailer::class);
/**
 * @var ServiceEmailMessage $serviceEmailMessage
 */
$serviceEmailMessage = $container->getByType(ServiceEmailMessage::class);
$argv = $_SERVER['argv'];
$query = $serviceEmailMessage->getMessagesToSend($argv[1] ?: DEFAULT_MESSAGE_LIMIT);
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
