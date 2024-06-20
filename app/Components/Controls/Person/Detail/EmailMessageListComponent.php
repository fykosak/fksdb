<?php

declare(strict_types=1);

namespace FKSDB\Components\Controls\Person\Detail;

use FKSDB\Components\Grids\Components\Container\RowContainer;
use FKSDB\Components\Grids\Components\Referenced\TemplateItem;
use FKSDB\Components\Grids\Components\Renderer\RendererItem;
use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Models\ORM\FieldLevelPermissionValue;
use FKSDB\Models\ORM\Models\EmailMessageModel;
use Fykosak\Utils\UI\Title;
use Nette\Database\Table\Selection;

class EmailMessageListComponent extends DetailComponent
{
    protected function getMinimalPermission(): FieldLevelPermissionValue
    {
        return FieldLevelPermissionValue::Full;
    }

    protected function getModels(): Selection
    {
        return $this->person->getMessages();
    }

    protected function getHeadline(): Title
    {
        return new Title(null, _('Emails'));
    }

    /**
     * @return void
     * @throws BadTypeException
     * @throws \ReflectionException
     */
    protected function configure(): void
    {
        $this->setTitle(new TemplateItem($this->container, '@email_message.subject'));
        $this->classNameCallback = fn(EmailMessageModel $model) => 'alert alert-' . $model->state->getBehaviorType();
        $row = new RowContainer($this->container);
        $this->addRow($row, 'row');
        $row->addComponent(new TemplateItem($this->container, '@email_message.state'), 'state');
        $row->addComponent(
            new RendererItem(
                $this->container,
                fn(EmailMessageModel $model): string => $model->sent ? (_('Sent ') .
                    $model->sent->format('d. M Y H:i:s')) : '',
                new Title(null, '')
            ),
            'sent'
        );
    }
}
