<?php

declare(strict_types=1);

namespace FKSDB\Modules\CoreModule;

use FKSDB\Components\EntityForms\AddressFormComponent;
use FKSDB\Models\ORM\Models\PersonModel;
use FKSDB\Models\ORM\Models\PostContactType;
use Fykosak\Utils\UI\PageTitle;

class MyProfilePresenter extends BasePresenter
{
    public function titleDefault(): PageTitle
    {
        return new PageTitle(null, _('My profile'), 'fa fa-cogs');
    }

    public function titleEdit(): PageTitle
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
        /** @var PersonModel $person */
        $person = $this->getUser()->getIdentity()->person;
        return new AddressFormComponent(
            $this->getContext(),
            $type,
            $person
        );
    }
}
