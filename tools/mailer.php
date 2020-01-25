<?php

use FKSDB\ORM\Models\ModelEmailMessage;
use FKSDB\ORM\Services\ServiceEmailMessage;
use Nette\DI\Container;
use Nette\Mail\IMailer;

const MESSAGE_LIMIT = 20;

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
$query = $serviceEmailMessage->getMessagesToSend($argv[1] ?: MESSAGE_LIMIT);
/**
 * @var ModelEmailMessage $model
 */
foreach ($query as $model) {
    try {
        $message = $model->toMessage();
        $mailer->send($message);
        $model->update(['state' => ModelEmailMessage::STATE_SENT, 'sent' => new DateTime()]);
    } catch (Exception $e) {
        $model->update(['state' => ModelEmailMessage::STATE_FAILED]);
    }
}
