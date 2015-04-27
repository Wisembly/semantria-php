<?php

namespace Semantria;

use Guzzle\Tests\GuzzleTestCase,
    Guzzle\Http\Message\Request;

class SemantriaAuthClientTest extends GuzzleTestCase
{
    private $config = [
        'consumer_key'      => 'my-app',
        'consumer_secret'   => '1234'
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

    public function testAuthIsSet()
    {
        $client = SemantriaAuthClient::factory($this->config);
        $this->assertEquals($client::$consumer_key, 'my-app');
        $this->assertEquals($client::$application_name, 'semantria-php');
    }

    public function testGetServiceDescriptionFromFile()
    {
        $client = new SemantriaAuthClient($this->config);
        $sd = $client->getServiceDescriptionFromFile(__DIR__ . '/../../src/Semantria/Service/config/semantria.json');
        $this->assertInstanceOf('Guzzle\Service\Description\ServiceDescription', $sd);
    }

    public function testOAuthRequest()
    {
        $request = new Request('GET', '//foo.bar');
        $client = SemantriaAuthClient::factory($this->config);

        $client->oAuthRequest($request);
        $this->assertEquals($request->getHeaders()->count(), 3);

        $headers = $request->getHeaders()->getAll();
        $this->assertArrayHasKey('authorization', $headers);
        $this->assertArrayHasKey('x-app-name', $headers);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testGetServiceDescriptionFromFileNoFile()
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
        SemantriaAuthClient::factory(['consumer_id' => 'my-app']);
    }
}
