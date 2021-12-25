<?php

declare(strict_types=1);

namespace FKSDB\Components\EntityForms;

use FKSDB\Components\Forms\Containers\ModelContainer;
use FKSDB\Components\Forms\Factories\SingleReflectionFormFactory;
use FKSDB\Models\Exceptions\BadTypeException;
use Fykosak\Utils\Logging\Message;
use FKSDB\Models\ORM\Models\ModelContest;
use FKSDB\Models\ORM\Models\ModelOrg;
use FKSDB\Models\ORM\OmittedControlException;
use FKSDB\Models\ORM\Services\ServiceOrg;
use FKSDB\Models\Utils\FormUtils;
use Nette\DI\Container;
use Nette\Forms\Form;

/**
 * @property ModelOrg|null $model
 */
class OrgFormComponent extends AbstractEntityFormComponent
{
    use ReferencedPersonTrait;

    public const CONTAINER = 'org';

    private ServiceOrg $serviceOrg;
    private ModelContest $contest;
    private SingleReflectionFormFactory $singleReflectionFormFactory;

    public function __construct(Container $container, ModelContest $contest, ?ModelOrg $model)
    {
        parent::__construct($container, $model);
        $this->contest = $contest;
    }

    final public function injectPrimary(
        SingleReflectionFormFactory $singleReflectionFormFactory,
        ServiceOrg $serviceOrg
    ): void {
        $this->singleReflectionFormFactory = $singleReflectionFormFactory;
        $this->serviceOrg = $serviceOrg;
    }

    /**
     * @throws BadTypeException
     * @throws OmittedControlException
     */
    protected function configureForm(Form $form): void
    {
        $container = $this->createOrgContainer();
        $personInput = $this->createPersonSelect();
        if (!$this->isCreating()) {
            $personInput->setDisabled(true);
        }
        $container->addComponent($personInput, 'person_id', 'since');
        $form->addComponent($container, self::CONTAINER);
    }

    protected function handleFormSuccess(Form $form): void
    {
        $data = FormUtils::emptyStrToNull($form->getValues()[self::CONTAINER], true);
        if (!isset($data['contest_id'])) {
            $data['contest_id'] = $this->contest->contest_id;
        }
        $this->serviceOrg->storeModel($data, $this->model);
        $this->getPresenter()->flashMessage(
            isset($this->model) ? _('Org has been updated.') : _('Org has been created.'),
            Message::LVL_SUCCESS
        );
        $this->getPresenter()->redirect('list');
    }

    /**
     * @throws BadTypeException
     */
    protected function setDefaults(): void
    {
        if (isset($this->model)) {
            $this->getForm()->setDefaults([self::CONTAINER => $this->model->toArray()]);
        }
    }

    /**
     * @throws BadTypeException
     * @throws OmittedControlException
     */
    private function createOrgContainer(): ModelContainer
    {
        $container = new ModelContainer();

        foreach (['since', 'until'] as $field) {
            $control = $this->singleReflectionFormFactory->createField(
                'org',
                $field,
                $this->contest->getFirstYear(),
                $this->contest->getLastYear()
            );
            $container->addComponent($control, $field);
        }

        foreach (['role', 'tex_signature', 'domain_alias', 'order', 'contribution'] as $field) {
            $control = $this->singleReflectionFormFactory->createField('org', $field);
            $container->addComponent($control, $field);
        }
        return $container;
    }
}
