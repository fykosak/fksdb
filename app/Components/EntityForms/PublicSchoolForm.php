<?php

declare(strict_types=1);

namespace FKSDB\Components\EntityForms;

use FKSDB\Components\Forms\Containers\Models\ContainerWithOptions;
use FKSDB\Components\Forms\Controls\CaptchaBox;
use FKSDB\Components\Forms\Referenced\Address\AddressDataContainer;
use FKSDB\Components\Forms\Referenced\Address\AddressHandler;
use FKSDB\Models\ORM\Models\SchoolModel;
use FKSDB\Models\ORM\Services\SchoolService;
use FKSDB\Models\Utils\FormUtils;
use Fykosak\Utils\Logging\Message;
use Nette\DI\Container;
use Nette\Forms\Controls\SubmitButton;
use Nette\Forms\Form;

/**
 * @phpstan-extends EntityFormComponent<SchoolModel>
 */
final class PublicSchoolForm extends EntityFormComponent
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

    protected function configureForm(Form $form): void
    {
        $container = new ContainerWithOptions($this->container);
        $container->addText('name_full', _('Full name of school'))
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

        $container->addText('note', _('Note'))->setOption('description', _('Webpage or another additionial info'));
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
         *     note:string,
         *     address:array{
         *          target?:string|null,
         *          city?:string|null,
         *          country_id?:int|null,
         *          postal_code:string|null,
         * },
         * }} $values
         */
        $values = $form->getValues('array');
        $handler = new AddressHandler($this->container);

        $schoolData = FormUtils::emptyStrToNull2($values[self::CONT_SCHOOL]);
        $address = $handler->store($schoolData['address']);
        $schoolData['address_id'] = $address->address_id;
        $this->schoolService->storeModel($schoolData);
        $this->getPresenter()->flashMessage(
            _('School has been created'),
            Message::LVL_SUCCESS
        );
        $this->getPresenter()->redirect('list');
    }

    protected function setDefaults(Form $form): void
    {
    }
}
