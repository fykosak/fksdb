<?php

declare(strict_types=1);

namespace FKSDB\Components\EntityForms\Warehouse;

use FKSDB\Components\EntityForms\ModelForm;
use FKSDB\Components\Forms\Containers\ModelContainer;
use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Models\ORM\Columns\OmittedControlException;
use FKSDB\Models\ORM\Models\Warehouse\ProducerModel;
use FKSDB\Models\ORM\Models\Warehouse\ProductModel;
use FKSDB\Models\ORM\Services\Warehouse\ProducerService;
use FKSDB\Models\ORM\Services\Warehouse\ProductService;
use Fykosak\NetteORM\Model\Model;
use Fykosak\Utils\Logging\Message;
use Nette\Application\ForbiddenRequestException;
use Nette\Forms\Controls\SelectBox;
use Nette\Forms\Form;

/**
 * @phpstan-extends ModelForm<ProductModel,array{container:array{
 *        name_cs:string,
 *        name_en:string,
 *        description_cs:string,
 *        description_en:string,
 *        category:string,
 *        note:string,
 *  }}>
 */
final class ProductFormComponent extends ModelForm
{
    private ProducerService $producerService;
    private ProductService $productService;

    public const CONTAINER = 'container';

    public function injectServiceProducer(
        ProducerService $producerService,
        ProductService $productService
    ): void {
        $this->producerService = $producerService;
        $this->productService = $productService;
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
        $container = new ModelContainer($this->container, 'warehouse_product');
        $container->addField('name_cs', ['required' => true]);
        $container->addField('name_en', ['required' => true]);
        $container->addField('description_cs', ['required' => true]);
        $container->addField('description_en', ['required' => true]);
        $container->addField('category', ['required' => true]);
        $container->addField('note', ['required' => true]);
        $producers = [];
        /** @var ProducerModel $producer */
        foreach ($this->producerService->getTable() as $producer) {
            $producers[$producer->producer_id] = $producer->name;
        }
        $container->addComponent(new SelectBox(_('Producer'), $producers), 'producer_id', 'name_cs');
        $container->addText('url', _('URL'))->addRule(Form::URL);
        $form->addComponent($container, self::CONTAINER);
    }

    protected function innerSuccess(array $values, Form $form): ProductModel
    {
        /** @var ProductModel $product */
        $product = $this->productService->storeModel($values[self::CONTAINER], $this->model);
        return $product;
    }

    protected function successRedirect(Model $model): void
    {
        $this->getPresenter()->flashMessage(
            isset($this->model) ? _('Product has been updated.') : _('Product has been created.'),
            Message::LVL_SUCCESS
        );
        $this->getPresenter()->redirect('list');
    }
}
