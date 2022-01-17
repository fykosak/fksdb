<?php

declare(strict_types=1);

namespace FKSDB\Components\EntityForms\Warehouse;

use FKSDB\Components\EntityForms\EntityFormComponent;
use FKSDB\Components\Forms\Factories\SingleReflectionFormFactory;
use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Models\ORM\Models\Warehouse\ModelProducer;
use FKSDB\Models\ORM\OmittedControlException;
use FKSDB\Models\ORM\Services\Warehouse\ServiceProducer;
use FKSDB\Models\ORM\Services\Warehouse\ServiceProduct;
use FKSDB\Models\Utils\FormUtils;
use Fykosak\Utils\Logging\Message;
use Nette\Forms\Controls\SelectBox;
use Nette\Forms\Form;

class ProductFormComponent extends EntityFormComponent
{

    protected ServiceProducer $serviceProducer;
    protected ServiceProduct $serviceProduct;
    protected SingleReflectionFormFactory $singleReflectionFormFactory;

    public const CONTAINER = 'container';

    public function injectServiceProducer(
        ServiceProducer $serviceProducer,
        ServiceProduct $serviceProduct,
        SingleReflectionFormFactory $singleReflectionFormFactory
    ): void {
        $this->serviceProducer = $serviceProducer;
        $this->serviceProduct = $serviceProduct;
        $this->singleReflectionFormFactory = $singleReflectionFormFactory;
    }

    protected function handleFormSuccess(Form $form): void
    {
        /** @var array $values */
        $values = $form->getValues();
        $data = FormUtils::emptyStrToNull($values[self::CONTAINER], true);

        $this->serviceProduct->storeModel($data, $this->model);
        $this->getPresenter()->flashMessage(
            isset($this->model) ? _('Product has been updated.') : _('Product has been created.'),
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
    protected function configureForm(Form $form): void
    {
        $container = $this->singleReflectionFormFactory->createContainer('warehouse_product', [
            'name_cs',
            'name_en',
            'description_cs',
            'description_en',
            'category',
            'note',
        ]);
        $producers = [];
        /** @var ModelProducer $producer */
        foreach ($this->serviceProducer->getTable() as $producer) {
            $producers[$producer->producer_id] = $producer->name;
        }
        $container->addComponent(new SelectBox(_('Producer'), $producers), 'producer_id', 'name_cs');
        $container->addText('url', _('URL'))->addRule(Form::URL);
        $form->addComponent($container, self::CONTAINER);
    }
}
