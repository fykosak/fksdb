<?php

declare(strict_types=1);

namespace FKSDB\Modules\OrgModule;

use FKSDB\Components\PDFGenerators\Envelopes\PageComponent;
use FKSDB\Components\PDFGenerators\Providers\AbstractProviderComponent;
use FKSDB\Components\PDFGenerators\Providers\DefaultProviderComponent;

class PdfGeneratorPresenter extends BasePresenter
{
    protected function createComponentTest(): DefaultProviderComponent
    {
        return new DefaultProviderComponent(
            new PageComponent($this->getContext()),
            AbstractProviderComponent::FORMAT_B5_LANDSCAPE,
            [$this->getUser()->getIdentity()->getPerson()],
            $this->getContext()
        );
    }
}
