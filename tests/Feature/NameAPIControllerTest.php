<?php

namespace Tests\Feature;


use SimpleXMLElement;
use Tests\TestCase;

class NameAPIControllerTest extends TestCase
{


    /**
     *
     * APITest
     *
     * @return void
     * @group NameAPI
     * @throws \Exception
     */
    public function test_api()
    {
        $response = $this->get('/api/namesx');
        $response->assertStatus(404);

        $response = $this->get('/api/names');
        $response->assertStatus(200);
        $response->assertHeader('Content-Type','application/xml');
        $xml = $response->content();
        $xml = new SimpleXMLElement($xml);
        $this->assertCount(10,$xml->element);

        $response = $this->get('/api/namesx');
        $response = $this->get('/api/namesx');
        $response = $this->get('/api/namesx');
        $response->assertStatus(429);
    }
}
