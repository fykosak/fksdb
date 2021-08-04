<?php

declare(strict_types=1);

namespace FKSDB\Components\PDFGenerators\Envelopes;

use FKSDB\Components\PDFGenerators\Providers\AbstractPageComponent;
use FKSDB\Models\ORM\Models\ModelPerson;

class PageComponent extends AbstractPageComponent
{
    /**
     * @param ModelPerson $row
     */
    public function render($row): void
    {
        $postContact = $row->getPermanentPostContact(true);
        if ($postContact) {
            $this->template->person = $row;
            $this->template->address = $postContact->getAddress();
        }
        $this->template->render(__DIR__ . DIRECTORY_SEPARATOR . 'envelope.fykos.latte');
    }
}
