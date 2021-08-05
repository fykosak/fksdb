<?php

declare(strict_types=1);

namespace FKSDB\Components\PDFGenerators\Envelopes\ContestToPerson;

use FKSDB\Components\PDFGenerators\Providers\AbstractPageComponent;
use FKSDB\Models\ORM\Models\ModelContest;
use FKSDB\Models\ORM\Models\ModelPerson;
use Nette\DI\Container;

class PageComponent extends AbstractPageComponent
{
    private ModelContest $contest;

    public function __construct(ModelContest $contest, Container $container)
    {
        parent::__construct($container);
        $this->contest = $contest;
    }

    /**
     * @param ModelPerson $row
     */
    public function render($row): void
    {
        $postContact = $row->getDeliveryPostContact();
        if (!$postContact) {
            $postContact = $row->getPermanentPostContact();
        }
        if ($postContact) {
            $this->template->person = $row;
            $this->template->address = $postContact->getAddress();
        }
        $this->template->contest = $this->contest;
        $this->template->render(__DIR__ . DIRECTORY_SEPARATOR . 'envelope.latte');
    }
}
