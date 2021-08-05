<?php

declare(strict_types=1);

namespace FKSDB\Modules\OrgModule;

use FKSDB\Components\PDFGenerators\Envelopes\ContestToPerson\PageComponent;
use FKSDB\Components\PDFGenerators\Providers\AbstractProviderComponent;
use FKSDB\Components\PDFGenerators\Providers\DefaultProviderComponent;

class PdfGeneratorPresenter extends BasePresenter
{
    protected function createComponentTest(): DefaultProviderComponent
    {
    }
}
