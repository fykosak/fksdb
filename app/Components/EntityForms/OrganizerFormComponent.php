<?php

declare(strict_types=1);

namespace FKSDB\Components\EntityForms;

use FKSDB\Components\Forms\Containers\ModelContainer;
use FKSDB\Components\Forms\Containers\Models\ContainerWithOptions;
use FKSDB\Models\Authorization\Authorizators\Authorizator;
use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Models\ORM\Columns\OmittedControlException;
use FKSDB\Models\ORM\Models\ContestYearModel;
use FKSDB\Models\ORM\Models\OrganizerModel;
use FKSDB\Models\ORM\Services\OrganizerService;
use FKSDB\Models\Persons\Resolvers\ContestACLResolver;
use Fykosak\NetteORM\Model\Model;
use Fykosak\Utils\Logging\Message;
use Nette\Application\ForbiddenRequestException;
use Nette\DI\Container;
use Nette\Forms\Form;

/**
 * @phpstan-extends ModelForm<OrganizerModel,array{container:array{
 *       since:int,
 *       until:int|null,
 *       role:string,
 *       tex_signature:string,
 *       domain_alias:string,
 *       order:int,
 *       contribution:string,
 *      contest_id?:int,
 *  }}>
 */
class OrganizerFormComponent extends ModelForm
{
    use ReferencedPersonTrait;

    public const CONTAINER = 'container';
    private ContestYearModel $contestYear;
    private Authorizator $authorizator;
    private OrganizerService $service;

    public function __construct(Container $container, ContestYearModel $contestYear, ?OrganizerModel $model)
    {
        parent::__construct($container, $model);
        $this->contestYear = $contestYear;
    }

    final public function injectPrimary(
        OrganizerService $service,
        Authorizator $contestAuthorizator
    ): void {
        $this->service = $service;
        $this->authorizator = $contestAuthorizator;
    }

    /**
     * @throws BadTypeException
     * @throws OmittedControlException
     * @throws ForbiddenRequestException
     */
    protected function configureForm(Form $form): void
    {
        $container = $this->createContainer();
        $referencedId = $this->createPersonId(
            $this->contestYear,
            !isset($this->model),
            new ContestACLResolver($this->authorizator, $this->contestYear->contest),
            $this->getContext()->getParameters()['forms']['adminOrganizer']
        );
        $container->addComponent($referencedId, 'person_id', 'since');
        $form->addComponent($container, self::CONTAINER);
    }

    protected function innerSuccess(array $values, Form $form): OrganizerModel
    {
        $data = $values[self::CONTAINER];
        $data['contest_id'] = $this->contestYear->contest_id;
        /** @var OrganizerModel $model */
        $model = $this->service->storeModel($data, $this->model);
        return $model;
    }

    protected function successRedirect(Model $model): void
    {
        $this->getPresenter()->flashMessage(
            isset($this->model) ? _('Organizer has been updated.') : _('Organizer has been created.'),
            Message::LVL_SUCCESS
        );
        $this->getPresenter()->redirect('list');
    }

    protected function setDefaults(Form $form): void
    {
        if (isset($this->model)) {
            $form->setDefaults([self::CONTAINER => $this->model->toArray()]);
        }
    }

    /**
     * @throws BadTypeException
     * @throws OmittedControlException
     * @throws ForbiddenRequestException
     */
    private function createContainer(): ContainerWithOptions
    {
        $container = new ModelContainer($this->container, 'org');

        foreach (['since', 'until'] as $field) {
            $container->addField(
                $field,
                [],
                null,
                $this->contestYear->contest->getFirstYear(),
                $this->contestYear->contest->getLastYear()
            );
        }

        foreach (['role', 'tex_signature', 'domain_alias', 'order', 'contribution'] as $field) {
            $container->addField($field);
        }
        return $container;
    }
}
