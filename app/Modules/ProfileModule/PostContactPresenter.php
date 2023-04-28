<?php

declare(strict_types=1);

namespace FKSDB\Modules\ProfileModule;

use FKSDB\Components\EntityForms\AddressFormComponent;
use FKSDB\Models\ORM\Models\PersonModel;
use FKSDB\Models\ORM\Models\PostContactType;
use FKSDB\Modules\CoreModule\BasePresenter;
use Fykosak\Utils\UI\PageTitle;

class PostContactPresenter extends BasePresenter
{
    public function titleDefault(): PageTitle
    {
        return new PageTitle(null, _('Update my profile'), 'fa fa-cogs');
    }

    protected function createComponentDeliveryPostContactForm(): AddressFormComponent
    {
        return $this->createComponentPostContactForm(PostContactType::tryFrom(PostContactType::DELIVERY));
    }

    protected function createComponentPermanentPostContactForm(): AddressFormComponent
    {
        return $this->createComponentPostContactForm(PostContactType::tryFrom(PostContactType::PERMANENT));
    }

    private function createComponentPostContactForm(PostContactType $type): AddressFormComponent
    {
        return new AddressFormComponent(
            $this->getContext(),
            $type,
            $this->getPerson()
        );
    }
}
