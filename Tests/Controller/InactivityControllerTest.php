<?php

namespace Bvisonl\InactivityBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class InactivityControllerTest extends WebTestCase
{
    public function testIndex()
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/bvisonl/inactivity/ping');
        $this->assertEquals(204, $client->getResponse()->getStatusCode());
    }
}
