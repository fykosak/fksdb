<?php

declare(strict_types=1);

namespace FKSDB\Components\EntityForms\Warehouse;

use FKSDB\Components\EntityForms\EntityFormComponent;
use FKSDB\Components\Forms\Factories\SingleReflectionFormFactory;
use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Models\ORM\Models\Warehouse\ItemModel;
use FKSDB\Models\ORM\Models\ContestModel;
use FKSDB\Models\ORM\Models\Warehouse\ProductModel;
use FKSDB\Models\ORM\OmittedControlException;
use FKSDB\Models\ORM\Services\Warehouse\ProductService;
use FKSDB\Models\ORM\Services\Warehouse\ItemService;
use FKSDB\Models\Utils\FormUtils;
use Fykosak\Utils\Logging\Message;
use Nette\DI\Container;
use Nette\Forms\Controls\SelectBox;
use Nette\Forms\Form;

class ItemFormComponent extends EntityFormComponent
{
    protected ProductService $productService;
    protected ItemService $itemService;
    private ContestModel $contest;
    protected SingleReflectionFormFactory $singleReflectionFormFactory;

    public const CONTAINER = 'container';

    public function __construct(Container $container, ContestModel $contest, ?ItemModel $model)
    {
        parent::__construct($container, $model);
        $this->contest = $contest;
    }

    public function injectServiceProducer(
        ProductService $productService,
        ItemService $itemService,
        SingleReflectionFormFactory $singleReflectionFormFactory
    ): void {
        $this->productService = $productService;
        $this->itemService = $itemService;
        $this->singleReflectionFormFactory = $singleReflectionFormFactory;
    }

    protected function handleFormSuccess(Form $form): void
    {
        /** @var array $values */
        $values = $form->getValues();
        $data = FormUtils::emptyStrToNull2($values[self::CONTAINER]);

        if (!isset($data['contest_id'])) {
            $data['contest_id'] = $this->contest->contest_id;
        }

        $this->itemService->storeModel($data, $this->model);
        $this->getPresenter()->flashMessage(
            isset($this->model) ? _('Item has been updated.') : _('Item has been created.'),
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
        $container = $this->singleReflectionFormFactory->createContainer('warehouse_item', [
            'state',
            'description_cs',
            'description_en',
            'data',
            'purchase_price',
            'purchase_currency',
            'placement',
            'note'
        ]);
        $products = [];
        /** @var ProductModel $product */
        foreach ($this->productService->getTable() as $product) {
            $products[$product->product_id] = $product->name_cs;
        }
        $container->addComponent(new SelectBox(_('Product'), $products), 'product_id', 'state');
        $form->addComponent($container, self::CONTAINER);
    }
}
