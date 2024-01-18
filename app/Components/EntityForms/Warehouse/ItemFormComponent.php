<?php

declare(strict_types=1);

namespace FKSDB\Components\EntityForms\Warehouse;

use FKSDB\Components\EntityForms\EntityFormComponent;
use FKSDB\Components\Forms\Containers\ModelContainer;
use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Models\ORM\Columns\OmittedControlException;
use FKSDB\Models\ORM\Models\ContestModel;
use FKSDB\Models\ORM\Models\Warehouse\ItemModel;
use FKSDB\Models\ORM\Models\Warehouse\ProductModel;
use FKSDB\Models\ORM\Services\Warehouse\ItemService;
use FKSDB\Models\ORM\Services\Warehouse\ProductService;
use FKSDB\Models\Utils\FormUtils;
use Fykosak\Utils\Logging\Message;
use Nette\Application\ForbiddenRequestException;
use Nette\DI\Container;
use Nette\Forms\Controls\SelectBox;
use Nette\Forms\Form;

/**
 * @phpstan-extends EntityFormComponent<ItemModel>
 */
final class ItemFormComponent extends EntityFormComponent
{

    private ProductService $productService;
    private ItemService $itemService;
    private ContestModel $contest;

    public const CONTAINER = 'container';

    public function __construct(Container $container, ContestModel $contest, ?ItemModel $model)
    {
        parent::__construct($container, $model);
        $this->contest = $contest;
    }

    public function injectServiceProducer(
        ProductService $productService,
        ItemService $itemService
    ): void {
        $this->productService = $productService;
        $this->itemService = $itemService;
    }

    protected function handleFormSuccess(Form $form): void
    {
        /**
         * @phpstan-var array{container:array{
         *      state:string,
         *      description_cs:string,
         *      description_en:string,
         *      data:string,
         *      purchase_price:float,
         *      purchase_currency:string,
         *      placement:string,
         *      note:string,
         *     contest_id?:int,
         * }} $values
         */
        $values = $form->getValues('array');
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
    protected function configureForm(Form $form): void
    {
        $container = new ModelContainer($this->container, 'warehouse_item');
        $container->addField('state', ['required' => true]);
        $container->addField('description_cs', ['required' => true]);
        $container->addField('description_en', ['required' => true]);
        $container->addField('data', ['required' => true]);
        $container->addField('purchase_price', ['required' => true]);
        $container->addField('purchase_currency', ['required' => true]);
        $container->addField('placement', ['required' => true]);
        $container->addField('note', ['required' => true]);
        $products = [];
        /** @var ProductModel $product */
        foreach ($this->productService->getTable() as $product) {
            $products[$product->product_id] = $product->name_cs;
        }
        $container->addComponent(new SelectBox(_('Product'), $products), 'product_id', 'state');
        $form->addComponent($container, self::CONTAINER);
    }
}
