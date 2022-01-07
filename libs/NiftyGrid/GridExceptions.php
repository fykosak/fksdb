<?php

declare(strict_types=1);

namespace NiftyGrid;

/**
 * NiftyGrid - DataGrid for Nette
 *
 * @author    Jakub Holub
 * @copyright    Copyright (c) 2012 Jakub Holub
 * @license     New BSD Licence
 * @link        http://addons.nette.org/cs/niftygrid
 */
class GridException extends \Exception
{
}

class DuplicateColumnException extends GridException
{
}

class DuplicateButtonException extends GridException
{
}

class DuplicateGlobalButtonException extends GridException
{
}


class InvalidOrderException extends GridException
{
}
