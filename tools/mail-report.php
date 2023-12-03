<?php

declare(strict_types=1);

use FKSDB\Components\DataTest\DataTestFactory;
use FKSDB\Models\Events\Exceptions\EventNotFoundException;
use FKSDB\Models\Exceptions\NotFoundException;
use FKSDB\Models\Mail\MailTemplateFactory;
use FKSDB\Models\ORM\Models\ContestantModel;
use FKSDB\Models\ORM\Models\ContestModel;
use FKSDB\Models\ORM\Models\ContestYearModel;
use FKSDB\Models\ORM\Models\SchoolModel;
use FKSDB\Models\ORM\Services\ContestService;
use FKSDB\Models\ORM\Services\EmailMessageService;
use FKSDB\Models\ORM\Services\EventService;
use FKSDB\Models\ORM\Services\SchoolService;
use FKSDB\Models\ORM\Tests\ContestYear\ContestYearToContestantsAdapter;
use FKSDB\Models\ORM\Tests\School\SchoolsProviderAdapter;
use FKSDB\Models\ORM\Tests\School\VerifiedSchoolTest;
use FKSDB\Modules\Core\Language;
use Nette\DI\Container;

try {
    /** @var Container $container */
    $container = require __DIR__ . '/bootstrap.php';

    set_time_limit(-1);
    $dataTestFactory = $container->getByType(DataTestFactory::class);
    [, $type] = $argv;
    switch ($type) {
        case 'school':
            $tests = [
                new SchoolsProviderAdapter(
                    new VerifiedSchoolTest($container),
                    $container
                ),
            ];
            /** @var SchoolModel $model */
            $model = $container->getByType(SchoolService::class)->getTable()->fetch();
            break;
        case 'event':
            $tests = $dataTestFactory->getEventTests();
            $model = $container->getByType(EventService::class)->findByPrimary(+$argv[2]);//@phpstan-ignore-line
            if (!$model) {
                throw new EventNotFoundException();
            }
            break;
        case 'contest':
            $tests = [
                ...ContestYearModel::getTests($container),
                ...DataTestFactory::applyAdaptor(
                    ContestYearToContestantsAdapter::class,
                    ContestantModel::getTests($container),
                    $container
                ),
            ];
            /** @var ContestModel|null $contest */
            $contest = $container->getByType(ContestService::class)->findByPrimary(+$argv[2]);//@phpstan-ignore-line
            if (!$contest) {
                throw new NotFoundException();
            }
            $model = $contest->getCurrentContestYear();
            if (!$model) {
                throw new NotFoundException();
            }
            break;
        default:
            throw new InvalidArgumentException('Invalid report type');
    }

    $mailService = $container->getByType(EmailMessageService::class);
    $mailTemplateFactory = $container->getByType(MailTemplateFactory::class);
    $text = $mailTemplateFactory->renderReport2(
        [
            'model' => $model,
            'tests' => $tests,
        ],
        Language::from(Language::CS)
    );
    echo $text;
    $mailService->addMessageToSend([
        'recipient' => 'fyziklani@fykos.cz',
        'sender' => 'fksdb@fykos.cz',
        'reply_to' => 'noreply@fykos.cz',
        'subject' => 'Seznam chyb',
        'text' => $text,
        'priority' => 0,
    ]);
} catch (\Throwable $exception) {
    echo $exception->getMessage() . "\n";
    echo $exception->getTraceAsString() . "\n";
}
