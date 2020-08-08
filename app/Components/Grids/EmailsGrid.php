<?php

namespace FKSDB\Components\Grids;

use FKSDB\Exceptions\BadTypeException;
use FKSDB\ORM\Services\ServiceEmailMessage;
use Nette\Application\UI\Presenter;
use NiftyGrid\DataSource\IDataSource;
use NiftyGrid\DataSource\NDataSource;
use NiftyGrid\DuplicateButtonException;
use NiftyGrid\DuplicateColumnException;

/**
 * Class EmailsGrid
 * @author Michal Červeňák <miso@fykos.cz>
 */
class EmailsGrid extends BaseGrid {

    /** @var ServiceEmailMessage */
    private $serviceEmailMessage;

    /***
     * @param ServiceEmailMessage $serviceEmailMessage
     * @return void
     */
    public function injectServiceEmailMessage(ServiceEmailMessage $serviceEmailMessage) {
        $this->serviceEmailMessage = $serviceEmailMessage;
    }

    protected function getData(): IDataSource {
        $emails = $this->serviceEmailMessage->getTable()->order('created DESC');
        //->where('state!=? OR created > ?', [ModelEmailMessage::STATE_SENT, (new \DateTime())->modify('-1 month')]);
        return new NDataSource($emails);
    }

    /**
     * @param Presenter $presenter
     * @return void
     * @throws BadTypeException
     * @throws DuplicateButtonException
     * @throws DuplicateColumnException
     */
    protected function configure(Presenter $presenter) {
        parent::configure($presenter);

        $this->addColumns([
            'email_message.email_message_id',
            'email_message.recipient',
            'email_message.subject',
            'email_message.state',
        ]);
        $this->addLinkButton('detail', 'detail', _('Detail'), false, ['id' => 'email_message_id']);
        $this->paginate = true;
    }
}
