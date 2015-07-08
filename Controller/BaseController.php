<?php

namespace CoreSys\CommonBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

class BaseController extends Controller
{

    protected $version = '0.0.1';

    function controllerVersion()
    {
        return $this->version;
    }
}
