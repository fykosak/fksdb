<?php

use FKSDB\ORM\Models\ModelEmailMessage;
use FKSDB\ORM\Services\ServiceEmailMessage;

use Nette\DI\Container;
use Nette\Mail\IMailer;

const MESSAGE_LIMIT = 10;

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
$argv = $_SERVER['argv']; // TODO is needed?

$query = $serviceEmailMessage->getMessagesToSend(MESSAGE_LIMIT);
/**
 * @var ModelEmailMessage $model
 */
foreach ($query as $model) {
    $message = $model->toMessage();
    $mailer->send($message);

}
