<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * prods
 *
 * Wrapper library for prods, part of iRods
 *
 */


Class Prods
{
    public function __construct()
    {
        require_once APPPATH.'third_party/prods/src/Prods.inc.php';

        // no initialisation of classed required.
    }
}
