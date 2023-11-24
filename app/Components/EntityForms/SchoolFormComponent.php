<?php

declare(strict_types=1);

namespace FKSDB\Components\EntityForms;

use FKSDB\Components\Forms\Controls\ReferencedId;
use FKSDB\Components\Forms\Factories\SingleReflectionFormFactory;
use FKSDB\Components\Forms\Referenced\Address\AddressDataContainer;
use FKSDB\Components\Forms\Referenced\Address\AddressHandler;
use FKSDB\Components\Forms\Referenced\Address\AddressSearchContainer;
use FKSDB\Models\ORM\Models\SchoolModel;
use FKSDB\Models\ORM\Services\AddressService;
use FKSDB\Models\ORM\Services\SchoolService;
use FKSDB\Models\Utils\FormUtils;
use Fykosak\Utils\Logging\Message;
use Nette\Forms\Form;

/**
 * @phpstan-extends EntityFormComponent<SchoolModel>
 */
class SchoolFormComponent extends EntityFormComponent
{
    public const CONT_ADDRESS = 'address';
    public const CONT_SCHOOL = 'school';

    private SchoolService $schoolService;
    private AddressService $addressService;
    private SingleReflectionFormFactory $reflectionFormFactory;

    final public function injectPrimary(
        AddressService $addressService,
        SchoolService $schoolService,
        SingleReflectionFormFactory $reflectionFormFactory
    ): void {
        $this->addressService = $addressService;
        $this->schoolService = $schoolService;
        $this->reflectionFormFactory = $reflectionFormFactory;
    }

    protected function configureForm(Form $form): void
    {
        $container = $this->reflectionFormFactory->createContainerWithMetadata('school', [
            'name_full' => ['required' => false],
            'name' => ['required' => true],
            'name_abbrev' => ['required' => true],
            'email' => ['required' => false],
            'ic' => ['required' => false],
            'izo' => ['required' => false],
            'note' => ['required' => false],
            'active' => ['required' => false],
            'study_h' => ['required' => false],
            'study_p' => ['required' => false],
            'study_u' => ['required' => false],
            'verified' => ['required' => false],
        ]);
        $address = new ReferencedId(
            new AddressSearchContainer($this->container),
            new AddressDataContainer($this->container, false, true),
            $this->addressService,
            new AddressHandler($this->container)
        );
        $container->addComponent($address, 'address_id');
        $form->addComponent($container, self::CONT_SCHOOL);
    }

    protected function getTemplatePath(): string
    {
        return __DIR__ . '/school.latte';
    }

    /**
     * @throws \PDOException
     */
    protected function handleFormSuccess(Form $form): void
    {
        /** @phpstan-var array{school:array{
         *     name_full:string,
         *     name:string,
         *     name_abbrev:string,
         *     description:string,
         *     email:string,
         *     ic:string,
         *     izo:string,
         *     active:bool,
         *     note:string,
         *     address_id:int,
         * }} $values
         */
        $values = $form->getValues('array');
        $schoolData = FormUtils::emptyStrToNull2($values[self::CONT_SCHOOL]);
        $this->schoolService->storeModel($schoolData, $this->model);
        $this->getPresenter()->flashMessage(
            isset($this->model) ? _('School has been updated') : _('School has been created'),
            Message::LVL_SUCCESS
        );
        $this->getPresenter()->redirect('list');
    }

    protected function setDefaults(Form $form): void
    {
        if (isset($this->model)) {
            $form->setDefaults([self::CONT_SCHOOL => $this->model->toArray()]);
        } else {
            $form->setDefaults([self::CONT_SCHOOL => ['address_id' => ReferencedId::VALUE_PROMISE]]);
        }
    }
}
