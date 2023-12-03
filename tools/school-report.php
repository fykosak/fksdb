<?php

declare(strict_types=1);

use FKSDB\Components\DataTest\DataTestFactory;
use FKSDB\Models\ORM\Tests\School\SchoolsProviderAdapter;
use FKSDB\Models\ORM\Tests\School\VerifiedSchoolTest;
use FKSDB\Models\Mail\MailTemplateFactory;
use FKSDB\Models\ORM\Services\EmailMessageService;
use FKSDB\Models\ORM\Services\SchoolService;
use FKSDB\Modules\Core\Language;
use Fykosak\Utils\Logging\MemoryLogger;
use Nette\DI\Container;

/** @var Container $container */
$container = require __DIR__ . '/bootstrap.php';

set_time_limit(-1);
$service = $container->getByType(SchoolService::class);
$dataTestFactory = $container->getByType(DataTestFactory::class);
$tests = [
    new SchoolsProviderAdapter(new VerifiedSchoolTest($container), $container),
];

$logger = new MemoryLogger();
$school = $service->getTable()->fetch();
foreach ($tests as $test) {
    $test->run($logger, $school);
}
$mailService = $container->getByType(EmailMessageService::class);
$mailTemplateFactory = $container->getByType(MailTemplateFactory::class);
$mailService->addMessageToSend([
    'recipient' => 'schola.novum@fykos.cz',
    'sender' => 'fksdb@fykos.cz',
    'reply_to' => 'noreply@fykos.cz',
    'subject' => 'Seznam chyb',
    'text' => $mailTemplateFactory->renderReport(['logger' => $logger], Language::from(Language::CS)),
    'priority' => 0,
]);
