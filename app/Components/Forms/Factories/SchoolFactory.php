<?php

declare(strict_types=1);

namespace FKSDB\Components\Forms\Factories;

use FKSDB\Components\Forms\Containers\ModelContainer;
use FKSDB\Components\Forms\Controls\Autocomplete\AutocompleteSelectBox;
use FKSDB\Components\Forms\Controls\Autocomplete\SchoolProvider;
use FKSDB\Components\Forms\Controls\ReferencedId;
use FKSDB\Components\Forms\Referenced\Address\AddressDataContainer;
use FKSDB\Components\Forms\Referenced\Address\AddressHandler;
use FKSDB\Components\Forms\Referenced\Address\AddressSearchContainer;
use FKSDB\Models\ORM\Services\AddressService;
use Nette\DI\Container;
use Nette\Forms\Form;

class SchoolFactory
{

    private SchoolProvider $schoolProvider;
    private AddressService $addressService;
    private Container $container;

    public function __construct(SchoolProvider $schoolProvider, AddressService $addressService, Container $container)
    {
        $this->schoolProvider = $schoolProvider;
        $this->addressService = $addressService;
        $this->container = $container;
    }

    public function createContainer(): ModelContainer
    {
        $container = new ModelContainer($this->container);
        $container->addText('name_full', _('Full name'))
            ->addRule(Form::MAX_LENGTH, _('Max length reached'), 255)
            ->setOption('description', _('Full name of the school.'));

        $container->addText('name', _('Name'))
            ->addRule(Form::MAX_LENGTH, _('Max length reached'), 255)
            ->addRule(Form::FILLED, _('Name is required.'))
            ->setOption('description', _('Envelope name.'));

        $container->addText('name_abbrev', _('Abbreviated name'))
            ->addRule(
                Form::MAX_LENGTH,
                _('The length of the abbreviated name is restricted to a maximum %d characters.'),
                32
            )
            ->addRule(Form::FILLED, _('Short name is required.'))
            ->setOption('description', _('Very short name.'));

        $container->addText('email', _('Contact e-mail'))
            ->addCondition(Form::FILLED)
            ->addRule(Form::EMAIL);

        $container->addText('ic', _('IČ (Czech schools only)'))
            ->addRule(Form::MAX_LENGTH, _('The length of IČ is restricted to %d characters.'), 8);

        $container->addText('izo', _('IZO (Czech schools only)'))
            ->addRule(Form::MAX_LENGTH, _('The length of IZO is restricted to %d characters.'), 32);

        $container->addCheckbox('active', _('Active record'))
            ->setDefaultValue(true);

        $container->addText('note', _('Note'));
        $address = new ReferencedId(
            new AddressSearchContainer($this->container),
            new AddressDataContainer($this->container, false, true),
            $this->addressService,
            new AddressHandler($this->container)
        );
        $container->addComponent($address, 'address_id');

        return $container;
    }

    /**
     * @phpstan-return AutocompleteSelectBox<SchoolProvider>
     */
    public function createSchoolSelect(bool $showUnknownSchoolHint = false): AutocompleteSelectBox
    {
        /** @phpstan-var AutocompleteSelectBox<SchoolProvider> $schoolElement */
        $schoolElement = new AutocompleteSelectBox(true, _('School'), 'school');
        $schoolElement->setDataProvider($this->schoolProvider);
        if ($showUnknownSchoolHint) {
            $schoolElement->setOption(
                'description',
                sprintf(_('If you cannot find the school, ask on e-mail %s.'), 'schola.novum () fykos.cz')
            );
        }
        return $schoolElement;
    }
}
