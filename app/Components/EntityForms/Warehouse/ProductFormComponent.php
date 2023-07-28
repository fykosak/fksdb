<?php

declare(strict_types=1);

namespace FKSDB\Components\EntityForms\Warehouse;

use FKSDB\Components\EntityForms\EntityFormComponent;
use FKSDB\Components\Forms\Factories\SingleReflectionFormFactory;
use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Models\ORM\Models\Warehouse\ProducerModel;
use FKSDB\Models\ORM\Models\Warehouse\ProductModel;
use FKSDB\Models\ORM\OmittedControlException;
use FKSDB\Models\ORM\Services\Warehouse\ProducerService;
use FKSDB\Models\ORM\Services\Warehouse\ProductService;
use FKSDB\Models\Utils\FormUtils;
use Fykosak\Utils\Logging\Message;
use Nette\Forms\Controls\SelectBox;
use Nette\Forms\Form;

/**
 * @phpstan-extends EntityFormComponent<ProductModel>
 */
class ProductFormComponent extends EntityFormComponent
{

    protected ProducerService $producerService;
    protected ProductService $productService;
    protected SingleReflectionFormFactory $singleReflectionFormFactory;

    public const CONTAINER = 'container';

    public function injectServiceProducer(
        ProducerService $producerService,
        ProductService $productService,
        SingleReflectionFormFactory $singleReflectionFormFactory
    ): void {
        $this->producerService = $producerService;
        $this->productService = $productService;
        $this->singleReflectionFormFactory = $singleReflectionFormFactory;
    }

    protected function handleFormSuccess(Form $form): void
    {
        $values = $form->getValues();
        $data = FormUtils::emptyStrToNull2($values[self::CONTAINER]);

        $this->productService->storeModel($data, $this->model);
        $this->getPresenter()->flashMessage(
            isset($this->model) ? _('Product has been updated.') : _('Product has been created.'),
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
     */
    protected function configureForm(Form $form): void
    {
        $container = $this->singleReflectionFormFactory->createContainerWithMetadata('warehouse_product', [
            'name_cs' => ['required' => true],
            'name_en' => ['required' => true],
            'description_cs' => ['required' => true],
            'description_en' => ['required' => true],
            'category' => ['required' => true],
            'note' => ['required' => true],
        ]);
        $producers = [];
        /** @var ProducerModel $producer */
        foreach ($this->producerService->getTable() as $producer) {
            $producers[$producer->producer_id] = $producer->name;
        }
        $container->addComponent(new SelectBox(_('Producer'), $producers), 'producer_id', 'name_cs');
        $container->addText('url', _('URL'))->addRule(Form::URL);
        $form->addComponent($container, self::CONTAINER);
    }
}
