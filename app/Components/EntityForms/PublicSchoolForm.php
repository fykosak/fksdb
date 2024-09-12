<?php

declare(strict_types=1);

namespace FKSDB\Components\EntityForms;

use FKSDB\Components\Forms\Containers\ModelContainer;
use FKSDB\Components\Forms\Controls\CaptchaBox;
use FKSDB\Components\Forms\Referenced\Address\AddressDataContainer;
use FKSDB\Components\Forms\Referenced\Address\AddressHandler;
use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Models\ORM\Columns\OmittedControlException;
use FKSDB\Models\ORM\Models\SchoolModel;
use FKSDB\Models\ORM\Services\SchoolService;
use Fykosak\NetteORM\Model\Model;
use Fykosak\Utils\Logging\Message;
use Nette\Application\ForbiddenRequestException;
use Nette\DI\Container;
use Nette\Forms\Controls\SubmitButton;
use Nette\Forms\Form;

/**
 * @phpstan-extends ModelForm<SchoolModel,array{school:array{
 *      name_full:string,
 *      name:string,
 *      name_abbrev:string,
 *      description:string,
 *      email:string,
 *      note:string,
 *      address:array{
 *           target?:string|null,
 *           city?:string|null,
 *           country_id?:int|null,
 *           postal_code:string|null,
 *  },
 *  }}>
 */
final class PublicSchoolForm extends ModelForm
{
    public function __construct(Container $container)
    {
        parent::__construct($container, null);
    }

    public const CONT_ADDRESS = 'address';
    public const CONT_SCHOOL = 'school';

    private SchoolService $schoolService;

    final public function injectPrimary(SchoolService $schoolService): void
    {
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
        $container->addField('note', ['required' => false]);

        $address = new AddressDataContainer($this->container, false, true, false);
        $address->setOption('label', _('Address'));
        $container->addComponent($address, 'address');
        $form->addComponent($container, self::CONT_SCHOOL);
        $form->addComponent(new CaptchaBox(), 'captcha');
    }

    protected function appendSubmitButton(Form $form): SubmitButton
    {
        return $form->addSubmit('send', _('Create school'));
    }

    protected function setDefaults(Form $form): void
    {
    }

    protected function innerSuccess(array $values, Form $form): SchoolModel
    {
        $handler = new AddressHandler($this->container);
        $schoolData = $values[self::CONT_SCHOOL];
        $address = $handler->store($schoolData['address']);
        $schoolData['address_id'] = $address->address_id;
        $schoolData['verified'] = false;
        /** @var SchoolModel $school */
        $school = $this->schoolService->storeModel($schoolData);
        return $school;
    }

    protected function successRedirect(Model $model): void
    {
        $this->getPresenter()->flashMessage(
            _('School has been created'),
            Message::LVL_SUCCESS
        );
        $this->getPresenter()->redirect('success');
    }
}
