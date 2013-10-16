<?php

namespace FKSDB\Components\Forms\Factories;

use FKSDB\Components\Forms\Containers\ModelContainer;
use FKSDB\Components\Forms\Controls\SQLConsole;
use Kdyby\Extension\Forms\Replicator\Replicator;
use ModelStoredQuery;
use ModelStoredQueryParameter;
use Nette\Application\UI\Form;
use Nette\Forms\Container;
use Nette\Forms\ControlGroup;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 * 
 * @author Michal Koutný <michal@fykos.cz>
 */
class StoredQueryFactory {

    public function createConsole($options = 0, ControlGroup $group = null) {
        $container = new ModelContainer();
        $container->setCurrentGroup($group);

        $control = new SQLConsole('SQL');
        $container->addComponent($control, 'sql');

        return $container;
    }

    public function createMetadata($options = 0, ControlGroup $group = null) {
        $container = new ModelContainer();
        $container->setCurrentGroup($group);

        $container->addText('name', 'Název')
                ->addRule(Form::FILLED, 'Název dotazu je třeba vyplnit.')
                ->addRule(Form::MAX_LENGTH, 'Název dotazu je moc dlouhý.', 32);

        $container->addText('qid', 'QID')
                ->setOption('description', 'Dotazy s QIDem nelze smazat a QID lze použít pro práva a trvalé odkazování.')
                ->addCondition(Form::FILLED)
                ->addRule(Form::MAX_LENGTH, 'Název dotazu je moc dlouhý.', 16)
                ->addRule(Form::REGEXP, 'QID může být jen z písmen anglické abecedy a číslic a tečky.', '/^[a-z][a-z0-9.]*$/i');


        $container->addTextArea('description', 'Popis dotazu');

        return $container;
    }

    public function createParametersMetadata($options = 0, ControlGroup $group = null) {
        $that = $this;
        $replicator = new Replicator(function($replContainer) use($that, $group) {
                    $that->buildParameterMetadata($replContainer, $group);

                    $replContainer->addSubmit('remove', 'Odebrat')->addRemoveOnClick();
                }, 0, true);
        $replicator->containerClass = 'FKSDB\Components\Forms\Containers\ModelContainer';
        $replicator->setCurrentGroup($group);
        $replicator->addSubmit('addParam', 'Přidat parametr')
                ->setValidationScope(false)
                ->addCreateOnClick();

        return $replicator;
    }

    /**
     * @internal
     * @param Container $container
     * @param type $group
     */
    public function buildParameterMetadata(Container $container, $group) {
        $container->setCurrentGroup($group);

        $container->addText('name', 'Název')
                ->addRule(Form::FILLED, 'Název parametru musí být vyplněn.')
                ->addRule(Form::MAX_LENGTH, 'Název parametru je moc dlouhý.', 16)
                ->addRule(Form::REGEXP, 'Název parametru může být jen z písmen anglické abecedy a číslic.', '/^[a-z][a-z0-9]*$/');

        $container->addText('description', 'Popis');

        $container->addSelect('type', 'Datový typ')
                ->setItems(array(
                    ModelStoredQueryParameter::TYPE_INT => 'integer',
                    ModelStoredQueryParameter::TYPE_STR => 'string',
        ));

        $container->addText('default', 'Výchozí hodnota');
    }

    public function createParametersValues(ModelStoredQuery $queryPattern, $options = 0, ControlGroup $group = null) {
        $container = new Container();
        $container->setCurrentGroup($group);

        foreach ($queryPattern->getParameters() as $parameter) {
            $name = $parameter->name;
            $subcontainer = $container->addContainer($name);

            $valueElement = $subcontainer->addText('value', $name);
            $valueElement->setOption('description', $parameter->description);
            if ($parameter->type == ModelStoredQueryParameter::TYPE_INT) {
                $valueElement->addRule(Form::INTEGER, 'Parametr %label je číselný.');
            }

            $valueElement->setDefaultValue($parameter->getDefaultValue());
        }

        return $container;
    }

}
