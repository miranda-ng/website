<?php

use \Nette\Diagnostics\Debugger;

/**
 * Nette\Diagnostics\Debugger::barDump shortcut.
 */
function barDump($var)
{
    foreach (func_get_args() as $arg) {
        Debugger::barDump($arg);
    }
    return $var;
}
