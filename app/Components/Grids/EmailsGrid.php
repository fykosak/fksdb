<?php

declare(strict_types=1);

namespace FKSDB\Components\Grids;

use FKSDB\Components\Grids\Components\BaseGrid;
use FKSDB\Models\Exceptions\BadTypeException;
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
        return $this->service->getTable()->order('created DESC');
    }

    /**
     * @throws BadTypeException
     * @throws \ReflectionException
     */
    protected function configure(): void
    {
        $this->paginate = true;
        $this->counter = true;
        $this->filtered = false;
        $this->addSimpleReferencedColumns([
            '@email_message.email_message_id',
            '@email_message.recipient',
            '@person.full_name',
            '@email_message.subject',
            '@email_message.state',
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
