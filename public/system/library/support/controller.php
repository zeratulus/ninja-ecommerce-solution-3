<?php
/**
 * Created by PhpStorm.
 * User: ailus
 * Date: 22.09.19
 * Time: 16:31
 */

namespace Support;

class Controller extends \Controller {

    public function __construct(\Registry $registry)
    {
        parent::__construct($registry);
    }

}