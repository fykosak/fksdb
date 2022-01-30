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
    private string $format;

    public function __construct(ModelContest $contest, Container $container, string $format = self::FORMAT_B5_LANDSCAPE)
    {
        parent::__construct($container);
        $this->contest = $contest;
        $this->format = $format;
    }

    /**
     * @param ModelPerson $row
     */
    public function render($row, array $params = []): void
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

    public function getPageFormat(): string
    {
        return $this->format;
    }
}
