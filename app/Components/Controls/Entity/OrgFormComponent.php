<?php

namespace FKSDB\Components\Controls\Entity;

use FKSDB\Components\Forms\Containers\ModelContainer;
use FKSDB\Components\Forms\Factories\SingleReflectionFormFactory;
use FKSDB\Models\ORM\Columns\AbstractColumnException;
use FKSDB\Models\ORM\OmittedControlException;
use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Models\Messages\Message;
use FKSDB\Models\ORM\Models\ModelContest;
use FKSDB\Models\ORM\Models\ModelOrg;
use FKSDB\Models\ORM\Services\ServiceOrg;
use FKSDB\Models\Utils\FormUtils;
use FKSDB\Models\YearCalculator;
use Nette\Application\AbortException;
use Nette\DI\Container;
use Nette\Forms\Form;
use Nette\InvalidStateException;

/**
 * Class OrgForm
 * @author Michal Červeňák <miso@fykos.cz>
 * @property ModelOrg $model
 */
class OrgFormComponent extends AbstractEntityFormComponent {
    use ReferencedPersonTrait;

    public const CONTAINER = 'org';

    private ServiceOrg $serviceOrg;
    private ModelContest $contest;
    private SingleReflectionFormFactory $singleReflectionFormFactory;
    private YearCalculator $yearCalculator;

    public function __construct(Container $container, ModelContest $contest,?ModelOrg $model) {
        parent::__construct($container, $model);
        $this->contest = $contest;
    }

    final public function injectPrimary(SingleReflectionFormFactory $singleReflectionFormFactory, ServiceOrg $serviceOrg, YearCalculator $yearCalculator): void {
        $this->singleReflectionFormFactory = $singleReflectionFormFactory;
        $this->serviceOrg = $serviceOrg;
        $this->yearCalculator = $yearCalculator;
    }

    /**
     * @param Form $form
     * @return void
     * @throws AbstractColumnException
     * @throws BadTypeException
     * @throws OmittedControlException
     * @throws InvalidStateException
     */
    protected function configureForm(Form $form): void {
        $container = $this->createOrgContainer();
        $personInput = $this->createPersonSelect();
        if (!$this->isCreating()) {
            $personInput->setDisabled(true);
        }
        $container->addComponent($personInput, 'person_id', 'since');
        $form->addComponent($container, self::CONTAINER);
    }

    /**
     * @param Form $form
     * @return void
     * @throws AbortException
     */
    protected function handleFormSuccess(Form $form): void {
        $data = FormUtils::emptyStrToNull($form->getValues()[self::CONTAINER], true);
        if (!isset($data['contest_id'])) {
            $data['contest_id'] = $this->contest->contest_id;
        }
        $this->serviceOrg->store($this->model ?? null, $data);
        $this->getPresenter()->flashMessage(!isset($this->model) ? _('Org has been created.') : _('Org has been updated.'), Message::LVL_SUCCESS);
        $this->getPresenter()->redirect('list');
    }

    /**
     * @return void
     * @throws BadTypeException
     */
    protected function setDefaults(): void {
        if (isset($this->model)) {
            $this->getForm()->setDefaults([self::CONTAINER => $this->model->toArray()]);
        }
    }

    /**
     * @return ModelContainer
     * @throws AbstractColumnException
     * @throws BadTypeException
     * @throws OmittedControlException
     * @throws InvalidStateException
     */
    private function createOrgContainer(): ModelContainer {
        $container = new ModelContainer();
        $min = $this->yearCalculator->getFirstYear($this->contest);
        $max = $this->yearCalculator->getLastYear($this->contest);

        foreach (['since', 'until'] as $field) {
            $control = $this->singleReflectionFormFactory->createField('org', $field, $min, $max);
            $container->addComponent($control, $field);
        }

        foreach (['role', 'tex_signature', 'domain_alias', 'order', 'contribution'] as $field) {
            $control = $this->singleReflectionFormFactory->createField('org', $field);
            $container->addComponent($control, $field);
        }
        return $container;
    }
}
