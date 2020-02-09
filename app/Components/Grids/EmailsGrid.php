<?php

namespace FKSDB\Components\Grids;

use FKSDB\Components\Forms\Factories\TableReflectionFactory;
use FKSDB\ORM\Models\ModelEmailMessage;
use FKSDB\ORM\Services\ServiceEmailMessage;
use NiftyGrid\DataSource\NDataSource;
use NiftyGrid\DuplicateColumnException;
use SQL\SearchableDataSource;

/**
 * Class EmailsGrid
 * @package FKSDB\Components\Grids
 */
class EmailsGrid extends BaseGrid {

    /**
     * @var ServiceEmailMessage
     */
    private $serviceEmailMessage;

    /**
     * EmailsGrid constructor.
     * @param ServiceEmailMessage $serviceEmailMessage
     * @param TableReflectionFactory $tableReflectionFactory
     */
    function __construct(ServiceEmailMessage $serviceEmailMessage, TableReflectionFactory $tableReflectionFactory) {
        parent::__construct($tableReflectionFactory);
        $this->serviceEmailMessage = $serviceEmailMessage;
    }

    /**
     * @param $presenter
     * @throws DuplicateColumnException
     */
    protected function configure($presenter) {
        parent::configure($presenter);
        $emails = $this->serviceEmailMessage->getTable();
        $source = new NDataSource($emails);
        //  $source = new SearchableDataSource($emails);
        $this->setDataSource($source);


        $this->addColumns([
            'email_message.email_message_id',
            'email_message.subject',
            'email_message.sender',
            'email_message.reply_to',
            'email_message.state',

        ]);
        $this->paginate = false;
    }

    /**
     * @return string
     */
    protected function getModelClassName(): string {
        return ModelEmailMessage::class;
    }
}
