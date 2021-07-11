<?php

namespace FKSDB\Components\EntityForms\Warehouse;

use FKSDB\Components\EntityForms\EntityFormComponent;
use FKSDB\Components\Forms\Containers\ModelContainer;
use FKSDB\Components\Forms\Factories\SingleReflectionFormFactory;
use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Models\Messages\Message;
use FKSDB\Models\ORM\Models\Warehouse\ModelProducer;
use FKSDB\Models\ORM\OmittedControlException;
use FKSDB\Models\ORM\Services\Warehouse\ServiceProducer;
use FKSDB\Models\ORM\Services\Warehouse\ServiceProduct;
use FKSDB\Models\Utils\FormUtils;
use Nette\Forms\Form;

class ProductFormComponent extends EntityFormComponent {

    protected ServiceProducer $serviceProducer;
    protected ServiceProduct $serviceProduct;
    protected SingleReflectionFormFactory $singleReflectionFormFactory;

    public const CONTAINER = 'container';

    public function injectServiceProducer(ServiceProducer $serviceProducer, ServiceProduct $serviceProduct, SingleReflectionFormFactory $singleReflectionFormFactory): void {
        $this->serviceProducer = $serviceProducer;
        $this->serviceProduct = $serviceProduct;
        $this->singleReflectionFormFactory = $singleReflectionFormFactory;
    }

    protected function handleFormSuccess(Form $form): void {
        /** @var array $values */
        $values = $form->getValues();
        $data = FormUtils::emptyStrToNull($values[self::CONTAINER], true);

        $this->serviceProduct->storeModel($data, $this->model);
        $this->getPresenter()->flashMessage(isset($this->model) ? _('Product has been updated.') : _('Product has been created.'), Message::LVL_SUCCESS);
        $this->getPresenter()->redirect('list');
    }

    /**
     * @throws BadTypeException
     */
    protected function setDefaults(): void {
        if (isset($this->model)) {
            $this->getForm()->setDefaults([self::CONTAINER => $this->model->toArray()]);
        }
    }

    /**
     * @param Form $form
     * @throws BadTypeException
     * @throws OmittedControlException
     */
    protected function configureForm(Form $form): void {
        $container = new ModelContainer();
        $producers = [];
        /** @var ModelProducer $producer */
        foreach ($this->serviceProducer->getTable() as $producer) {
            $producers[$producer->producer_id] = $producer->name;
        }
        $container->addSelect('producer_id', _('Producer'), $producers);

        $this->singleReflectionFormFactory->createFieldInto($container, 'warehouse_product', 'name_cs');
        $this->singleReflectionFormFactory->createFieldInto($container, 'warehouse_product', 'name_en');

        $this->singleReflectionFormFactory->createFieldInto($container, 'warehouse_product', 'description_cs');
        $this->singleReflectionFormFactory->createFieldInto($container, 'warehouse_product', 'description_en');

        $this->singleReflectionFormFactory->createFieldInto($container, 'warehouse_product', 'category');

        $this->singleReflectionFormFactory->createFieldInto($container, 'warehouse_product', 'note');

        $container->addText('url', _('URL'))->addRule(Form::URL);

        $form->addComponent($container, self::CONTAINER);
    }
}
