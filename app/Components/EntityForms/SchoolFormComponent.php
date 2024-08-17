<?php

declare(strict_types=1);

namespace FKSDB\Components\EntityForms;

use FKSDB\Components\Forms\Containers\ModelContainer;
use FKSDB\Components\Forms\Controls\ReferencedId;
use FKSDB\Components\Forms\Referenced\Address\AddressDataContainer;
use FKSDB\Components\Forms\Referenced\Address\AddressHandler;
use FKSDB\Components\Forms\Referenced\Address\AddressSearchContainer;
use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Models\ORM\Columns\OmittedControlException;
use FKSDB\Models\ORM\Models\SchoolModel;
use FKSDB\Models\ORM\Services\AddressService;
use FKSDB\Models\ORM\Services\SchoolService;
use Fykosak\NetteORM\Model\Model;
use Fykosak\Utils\Logging\Message;
use Nette\Application\ForbiddenRequestException;
use Nette\Forms\Form;

/**
 * @phpstan-extends ModelForm<SchoolModel,array{school:array{
 *      name_full:string,
 *      name:string,
 *      name_abbrev:string,
 *      description:string,
 *      email:string,
 *      ic:string,
 *      izo:string,
 *      active:bool,
 *      note:string,
 *      address_id:int,
 *  }}>
 */
class SchoolFormComponent extends ModelForm
{
    public const CONT_ADDRESS = 'address';
    public const CONT_SCHOOL = 'school';

    private SchoolService $schoolService;
    private AddressService $addressService;

    final public function injectPrimary(
        AddressService $addressService,
        SchoolService $schoolService
    ): void {
        $this->addressService = $addressService;
        $this->schoolService = $schoolService;
    }

    /**
     * @throws BadTypeException
     * @throws OmittedControlException
     * @throws ForbiddenRequestException
     */
    protected function configureForm(Form $form): void
    {
        $container = new ModelContainer($this->container, 'school');
        $container->addField('name_full', ['required' => false]);
        $container->addField('name', ['required' => true]);
        $container->addField('name_abbrev', ['required' => true]);
        $container->addField('email', ['required' => false]);
        $container->addField('ic', ['required' => false]);
        $container->addField('izo', ['required' => false]);
        $container->addField('note', ['required' => false]);
        $container->addField('active', ['required' => false]);
        $container->addField('study_h', ['required' => false]);
        $container->addField('study_p', ['required' => false]);
        $container->addField('study_u', ['required' => false]);
        $container->addField('verified', ['required' => false]);
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

    protected function setDefaults(Form $form): void
    {
        if (isset($this->model)) {
            $form->setDefaults([self::CONT_SCHOOL => $this->model->toArray()]);
        } else {
            $form->setDefaults([self::CONT_SCHOOL => ['address_id' => ReferencedId::VALUE_PROMISE]]);
        }
    }

    protected function innerSuccess(array $values, Form $form): SchoolModel
    {
        /** @var SchoolModel $school */
        $school = $this->schoolService->storeModel($values[self::CONT_SCHOOL], $this->model);
        return $school;
    }

    protected function successRedirect(Model $model): void
    {
        $this->getPresenter()->flashMessage(
            isset($this->model) ? _('School has been updated') : _('School has been created'),
            Message::LVL_SUCCESS
        );
        $this->getPresenter()->redirect('default');
    }
}
