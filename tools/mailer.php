<?php

declare(strict_types=1);

use FKSDB\Models\ORM\Models\EmailMessageModel;
use FKSDB\Models\ORM\Models\EmailMessageState;
use FKSDB\Models\ORM\Services\EmailMessageService;
use FKSDB\Models\Transitions\TransitionsMachineFactory;
use Nette\DI\Container;
use Tracy\Debugger;

const SAFE_LIMIT = 500;

/** @var Container $container */
$container = require __DIR__ . '/bootstrap.php';
set_time_limit(60);
if (!$container->getParameters()['spamMailer'] || !$container->getParameters()['spamMailer']['enabled']) {
    exit(0);
}
/** @var EmailMessageService $serviceEmailMessage */
$serviceEmailMessage = $container->getByType(EmailMessageService::class);
$argv = $_SERVER['argv'];
$query = $serviceEmailMessage->getMessagesToSend(
    $argv[1] ? (int)$argv[1] : (int)$container->getParameters()['spamMailer']['defaultLimit']
);

$machine = $container->getByType(TransitionsMachineFactory::class)->getEmailMachine();
$transition = $machine->getTransitions()
    ->filterBySource(EmailMessageState::Waiting)
    ->filterByTarget(EmailMessageState::Sent)
    ->select();
$counter = 0;
/** @var EmailMessageModel $model */
foreach ($query as $model) {
    $counter++;
    if ($counter > SAFE_LIMIT) {
        Debugger::log('Message limit reached.', 'mailer-exceptions');
        break;
    }
    $holder = $machine->createHolder($model);
    $transition->execute($holder);
}
