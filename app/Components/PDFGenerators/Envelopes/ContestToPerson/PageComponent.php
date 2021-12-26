<?php

declare(strict_types=1);

namespace FKSDB\Components\PDFGenerators\Envelopes\ContestToPerson;

use FKSDB\Components\PDFGenerators\Providers\AbstractPageComponent;
use FKSDB\Components\PDFGenerators\Providers\PageFormat;
use FKSDB\Models\ORM\Models\ModelContest;
use FKSDB\Models\ORM\Models\ModelPerson;
use Nette\DI\Container;

class PageComponent extends AbstractPageComponent
{

    public function __construct(
        private ModelContest $contest,
        Container $container,
        private PageFormat $format = PageFormat::FORMAT_B5_LANDSCAPE
    ) {
        parent::__construct($container);
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

    public function getPagesTemplatePath(): string
    {
        return $this->formatPathByFormat($this->format);
    }
}
