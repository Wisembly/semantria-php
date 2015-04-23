<?php

namespace Semantria;

use Guzzle\Tests\GuzzleTestCase;

class SemantriaAuthClientTest extends GuzzleTestCase
{
    private $config = [
        'consumer_key'      => '1234',
        'consumer_secret'   => 'my-app'
    ];

    public function testFactory()
    {
        $client = SemantriaAuthClient::factory($this->config);
        $this->assertInstanceOf('Semantria\SemantriaAuthClient', $client);
    }

    public function testConstructor()
    {
        $client = new SemantriaAuthClient($this->config);
        $this->assertInstanceOf('Semantria\SemantriaAuthClient', $client);
    }

    // public function testAuthIsSet()
    // {
    //     $client = SemantriaAuthClient::factory($this->config);
    //     $auth = $client->getDefaultOption('auth');
    //     $this->assertEquals(3, count($auth));
    //     $this->assertEquals($this->config['app_id'], $auth[0]);
    //     $this->assertEquals($this->config['api_key'], $auth[1]);
    //     $this->assertEquals('Basic', $auth[2]);
    // }

    function testGetServiceDescriptionFromFile()
    {
        $client = new SemantriaAuthClient($this->config);
        $sd = $client->getServiceDescriptionFromFile(__DIR__ . '/../../src/Semantria/Service/config/semantria.json');
        $this->assertInstanceOf('Guzzle\Service\Description\ServiceDescription', $sd);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    function testGetServiceDescriptionFromFileNoFile()
    {
        $client = new SemantriaAuthClient($this->config);
        $client->getServiceDescriptionFromFile('');
    }

    /**
     * @expectedException \Guzzle\Common\Exception\InvalidArgumentException
     */
    public function testFactoryEmptyArgs()
    {
        SemantriaAuthClient::factory([]);
    }

    /**
     * @expectedException \Guzzle\Common\Exception\InvalidArgumentException
     */
    public function testFactoryMissingArgs()
    {
        SemantriaAuthClient::factory(['app_id' => 'my-app']);
    }
}
