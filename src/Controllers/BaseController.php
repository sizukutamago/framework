<?php
/**
 * Created by PhpStorm.
 * User: sizukutamago
 * Date: 2017/07/27
 * Time: 17:11
 */

namespace SizukuBBS\Controllers;


abstract class BaseController
{

    public $twig;

    final public function __construct($twig)
    {
        $this->twig = $twig;
    }

    public function response($content)
    {
        if (!headers_sent()) {
            header('Content-Type: text/html; charset=utf-8');
            header('Content-Length: ' . strlen($content));
        }

        echo $content;
    }
}
