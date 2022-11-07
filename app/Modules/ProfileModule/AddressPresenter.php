<?php

declare(strict_types=1);

namespace FKSDB\Modules\ProfileModule;

use FKSDB\Components\EntityForms\AddressFormComponent;
use FKSDB\Models\ORM\Models\PostContactType;
use Fykosak\Utils\UI\PageTitle;

class AddressPresenter extends BasePresenter
{
    public function titleDefault(): PageTitle
    {
        return new PageTitle(null, _('Change my addresses'), 'fa fa-cogs');
    }

    public function renderDefault(): void
    {
        $this->template->permanentAddress = $this->getLoggedPerson()->getAddress(PostContactType::Permanent);
        $this->template->deliveryAddress = $this->getLoggedPerson()->getAddress(PostContactType::Delivery);
    }

    protected function createComponentDeliveryPostContactForm(): AddressFormComponent
    {
        return $this->createComponentPostContactForm(PostContactType::Delivery);
    }

    protected function createComponentPermanentPostContactForm(): AddressFormComponent
    {
        return $this->createComponentPostContactForm(PostContactType::Permanent);
    }

    private function createComponentPostContactForm(PostContactType $type): AddressFormComponent
    {
        return new AddressFormComponent($this->getContext(), $type, $this->getLoggedPerson());
    }
}
