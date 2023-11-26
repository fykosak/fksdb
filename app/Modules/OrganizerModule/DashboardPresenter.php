<?php

declare(strict_types=1);

namespace FKSDB\Modules\OrganizerModule;

use FKSDB\Models\Mail\MailTemplateFactory;
use FKSDB\Modules\Core\Language;
use Fykosak\Utils\Logging\MemoryLogger;
use Fykosak\Utils\UI\PageTitle;
use Tracy\Debugger;

final class DashboardPresenter extends BasePresenter
{
    public function authorizedDefault(): bool
    {
        return true;
    }

    public function titleDefault(): PageTitle
    {
        return new PageTitle(null, _('Organizer\'s dashboard'), 'fas fa-chalkboard');
    }

    public function inject(MailTemplateFactory $mailTemplateFactory): void
    {
        $logger = new MemoryLogger();
        $report = $mailTemplateFactory->renderReport(['logger' => $logger], Language::from(Language::CS));
        Debugger::barDump($report);
    }
}
