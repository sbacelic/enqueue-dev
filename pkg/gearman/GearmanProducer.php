<?php

namespace Enqueue\Gearman;

use Interop\Queue\CompletionListener;
use Interop\Queue\InvalidDestinationException;
use Interop\Queue\InvalidMessageException;
use Interop\Queue\PsrDestination;
use Interop\Queue\PsrMessage;
use Interop\Queue\PsrProducer;

class GearmanProducer implements PsrProducer
{
    /**
     * @var CompletionListener
     */
    private $completionListener;

    /**
     * @var float
     */
    private $deliveryDelay = PsrMessage::DEFAULT_DELIVERY_DELAY;

    /**
     * @var \GearmanClient
     */
    private $client;

    /**
     * @param \GearmanClient $client
     */
    public function __construct(\GearmanClient $client)
    {
        $this->client = $client;
    }

    /**
     * {@inheritdoc}
     *
     * @param GearmanDestination $destination
     * @param GearmanMessage     $message
     */
    public function send(PsrDestination $destination, PsrMessage $message)
    {
        InvalidDestinationException::assertDestinationInstanceOf($destination, GearmanDestination::class);
        InvalidMessageException::assertMessageInstanceOf($message, GearmanMessage::class);

        $this->client->doBackground($destination->getName(), json_encode($message));

        $code = $this->client->returnCode();
        if (\GEARMAN_SUCCESS !== $code) {
            throw new \GearmanException(sprintf('The return code is not %s (GEARMAN_SUCCESS) but %s', \GEARMAN_SUCCESS, $code));
        }
    }

    /**
     * {@inheritdoc}
     */
    public function setCompletionListener(CompletionListener $listener = null)
    {
        $this->completionListener = $listener;
    }

    /**
     * @return CompletionListener|null
     */
    public function getCompletionListener()
    {
        return $this->completionListener;
    }

    /**
     * {@inheritdoc}
     */
    public function getDeliveryDelay()
    {
        return $this->deliveryDelay;
    }

    /**
     * {@inheritdoc}
     */
    public function setDeliveryDelay($deliveryDelay)
    {
        $this->deliveryDelay = $deliveryDelay;
    }
}