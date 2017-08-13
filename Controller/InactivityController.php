<?php

namespace Bvisonl\InactivityBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class InactivityController
 * @package Bvisonl\InactivityBundle\Controller
 */
class InactivityController extends Controller
{
    /**
     * @Route("/ping", name="bvisonl_inactivity_ping", options={"expose"=true})
     */
    public function pingAction(Request $request)
    {
        return new Response("Pong", 204);
    }
}
