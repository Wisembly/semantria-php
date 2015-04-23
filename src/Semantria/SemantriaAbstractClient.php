<?php

namespace Semantria;

use \InvalidArgumentException;

use Guzzle\Service\Client,
    Guzzle\Common\Event,
    Guzzle\Http\Message\Request,
    Guzzle\Service\Description\ServiceDescription;

use Semantria\Exception\SemantriaException;

abstract class SemantriaAbstractClient extends Client
{
    /** @var string */
    const DEFAULT_CONTENT_TYPE = 'application/json';

    /** @var string */
    const DEFAULT_ACCEPT_HEADER = 'application/json';

    /** @var string */
    const USER_AGENT = 'semantria-php/0.0.1';

    protected function configure($config)
    {
        $this->setDescription($this->getServiceDescriptionFromFile($config->get('service_description')));
        $this->setErrorHandler();
    }

    public function getServiceDescriptionFromFile($description_file)
    {
        if (!file_exists($description_file) || !is_readable($description_file)) {
            throw new InvalidArgumentException('Unable to read API definition schema');
        }
        return ServiceDescription::factory($description_file);
    }

    /**
     * Overrides the error handling in Guzzle so that when errors are encountered we throw
     * Semantria errors, not Guzzle ones.
     *
     */
    private function setErrorHandler()
    {
        $this->getEventDispatcher()->addListener(
            'request.error',
            function (Event $event) {
                // Stop other events from firing when you override 401 responses
                $event->stopPropagation();
                if ($event['response']->getStatusCode() >= 400 && $event['response']->getStatusCode() < 600) {
                    $e = SemantriaException::factory($event['request'], $event['response']);
                    $event['request']->setState(Request::STATE_ERROR, array('exception' => $e) + $event->toArray());
                    throw $e;
                }
            }
        );
    }

    public static function getDefaultConfig()
    {
        return [
            'service_description' => __DIR__ . '/Service/config/semantria.json',
            'application_name'    => 'semantria-php',
            'use_compression'     => false,
            'headers' => [
                'Content-Type'  => self::DEFAULT_CONTENT_TYPE,
                'Accept'        => self::DEFAULT_ACCEPT_HEADER,
                'User-Agent'    => self::USER_AGENT
            ]
        ];
    }
}
