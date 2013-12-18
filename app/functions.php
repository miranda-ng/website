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

/**
 * Catch PHP notices as exceptions
 */
/*set_error_handler(function($severity, $message, $file, $line) {
    if (($severity & error_reporting()) === $severity) {
        throw new ErrorException($message, 0, $severity, $file, $line);
    }
    return FALSE;
});*/