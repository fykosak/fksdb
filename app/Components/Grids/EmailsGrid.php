<?php

declare(strict_types=1);

namespace FKSDB\Components\Grids;

use FKSDB\Components\Grids\Components\BaseGrid;
use FKSDB\Components\Grids\Components\Referenced\TemplateItem;
use FKSDB\Models\ORM\Models\EmailMessageModel;
use FKSDB\Models\ORM\Services\EmailMessageService;
use Fykosak\NetteORM\Selection\TypedSelection;
use Fykosak\Utils\UI\Title;

/**
 * @phpstan-extends BaseGrid<EmailMessageModel,array{}>
 */
final class EmailsGrid extends BaseGrid
{
    private EmailMessageService $service;

    public function inject(EmailMessageService $service): void
    {
        $this->service = $service;
    }

    /**
     * @phpstan-return TypedSelection<EmailMessageModel>
     */
    protected function getModels(): TypedSelection
    {
        return $this->service->getTable()->order('email_message_id DESC');
    }

    protected function configure(): void
    {
        $this->paginate = true;
        $this->counter = false;
        $this->filtered = false;
        $this->addSimpleReferencedColumns([
            '@email_message.email_message_id',
        ]);
        /** @phpstan-ignore-next-line */
        $this->table->addColumn(new TemplateItem(
            $this->container,
            '@email_message.recipient/@person.full_name',
            '@email_message.recipient:title'
        ), 'recipient');
        $this->addSimpleReferencedColumns([
            '@email_message.subject',
            '@email_message.state',
            '@email_message.lang',
            '@email_message.topic',
            '@email_message.sent',
        ]);
        $this->addPresenterButton(
            'detail',
            'detail',
            new Title(null, _('button.detail')),
            false,
            ['id' => 'email_message_id']
        );
    }
}
