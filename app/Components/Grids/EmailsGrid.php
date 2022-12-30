<?php

declare(strict_types=1);

namespace FKSDB\Components\Grids;

use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Models\ORM\Services\EmailMessageService;
use Nette\DI\Container;

class EmailsGrid extends EntityGrid
{

    public function __construct(Container $container)
    {
        parent::__construct($container, EmailMessageService::class, [
            'email_message.email_message_id',
            'email_message.recipient',
            'person.full_name',
            'email_message.subject',
            'email_message.state',
        ]);
    }

    /**
     * @throws BadTypeException
     * @throws \ReflectionException
     */
    protected function configure(): void
    {
        parent::configure();
        $this->data->order('created DESC');
        $this->addPresenterButton('detail', 'detail', _('Detail'), false, ['id' => 'email_message_id']);
        $this->paginate = true;
    }
}
