<?php
namespace FKSDB\Components\React;


interface IReactComponent {

    function getComponentName();

    function getModuleName();

    function getMode();

    /**
     * @return string
     */
    function getData();
}
