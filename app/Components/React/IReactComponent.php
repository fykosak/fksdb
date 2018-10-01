<?php

namespace FKSDB\Components\React;


interface IReactComponent {
    /**
     * @return string
     */
    function getComponentName();

    /**
     * @return string
     */
    function getModuleName();

    /**
     * @return string
     */
    function getMode();

    /**
     * @return string
     */
    function getData();
}
