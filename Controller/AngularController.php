<?php

namespace CoreSys\CommonBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

/**
 * Class AngularController
 * @package CoreSys\CommonBundle\Controller
 * @Route("/common/angular")
 */
class AngularController extends Controller
{

    /**
     * @Route("/app/{pid}", name="common_angular_app", defaults={"pid"=null,"dependencies"=null})
     * @Route("/app/{pid}/{dependencies}", name="common_angular_app_dependencies", defaults={"pid"=null,"dependencies"=null})
     * @Template()
     */
    public function appAction( $pid, $dependencies )
    {
        if ( !empty( $dependencies ) ) {
            $dependencies = explode( ',', $dependencies );
        }
        header( 'Content-Type: application/javascript' );
        echo $this->renderView( 'CoreSysCommonBundle:Angular:app.js.twig', array( 'pid' => $pid, 'dependencies' => json_encode( $dependencies ) ) );

        return;
    }
}
