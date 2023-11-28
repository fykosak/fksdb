<?php

declare(strict_types=1);

use FKSDB\Components\DataTest\DataTestFactory;
use FKSDB\Models\Mail\MailTemplateFactory;
use FKSDB\Models\ORM\Services\EmailMessageService;
use FKSDB\Models\ORM\Services\EventService;
use FKSDB\Modules\Core\Language;
use Fykosak\Utils\Logging\MemoryLogger;
use Nette\DI\Container;

/** @var Container $container */
$container = require __DIR__ . '/bootstrap.php';

set_time_limit(-1);
$service = $container->getByType(EventService::class);
$dataTestFactory = $container->getByType(DataTestFactory::class);
$tests = $dataTestFactory->getEventTests();
$logger = new MemoryLogger();
$event = $service->findByPrimary(+$argv[1]);
foreach ($tests as $test) {
    $test->run($logger, $event);
}
$mailService = $container->getByType(EmailMessageService::class);
$mailTemplateFactory = $container->getByType(MailTemplateFactory::class);
$mailService->addMessageToSend(
    array_merge(
        [
            'recipient' => 'fyziklani@fykos.cz',
            'sender' => 'fksdb@fykos.cz',
            'reply_to' => 'noreply@fykos.cz',
            'priority' => 0,
        ],
        $mailTemplateFactory->renderWithParameters(
            __DIR__ . '/report.latte',
            ['logger' => $logger],
            Language::from(Language::CS)
        )
    )
);
