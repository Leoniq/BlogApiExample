<?php

namespace App\Application\EventListener;

use App\Domain\Exception\Exception\DomainBadRequestException;
use App\Domain\Exception\Exception\DomainException;
use App\Domain\Exception\Exception\DomainNotFoundException;
use Hateoas\Representation\VndErrorRepresentation;
use JMS\Serializer\SerializerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;

final class ExceptionListener
{
    private SerializerInterface $serializer;

    /**
     * ExceptionListener constructor.
     * @param SerializerInterface $serializer
     */
    public function __construct(SerializerInterface $serializer)
    {
        $this->serializer = $serializer;
    }

    public function onKernelException(ExceptionEvent $event)
    {
        $exception = $event->getThrowable();

        switch (true) {
            case $exception instanceof DomainException:
                $this->handleDomainException($event, $exception);
                break;
            default:
                $this->handleException($event, $exception);
                break;
        }
    }

    /**
     * @param ExceptionEvent $event
     * @param DomainException $exception
     */
    public function handleDomainException(ExceptionEvent $event, DomainException $exception)
    {
        if ($exception instanceof DomainBadRequestException) {
            $httpCode = Response::HTTP_BAD_REQUEST;
        } elseif ($exception instanceof DomainNotFoundException) {
            $httpCode = Response::HTTP_NOT_FOUND;
        } else {
            $httpCode = Response::HTTP_INTERNAL_SERVER_ERROR;
        }

        $event->setResponse(
            new Response(
                $this->serializer->serialize(
                    new VndErrorRepresentation(
                        $exception->getMessage()
                    ),
                    'json'
                ),
                $httpCode,
                ['Content-Type' => 'application/json']
            )
        );
    }

    /**
     * @param ExceptionEvent $event
     * @param \Exception $exception
     */
    public function handleException(ExceptionEvent $event, \Throwable $exception)
    {
        $event->setResponse(
            new Response(
                $this->serializer->serialize(
                    new VndErrorRepresentation(
                        $exception->getMessage()
                    ),
                    'json'
                ),
                Response::HTTP_INTERNAL_SERVER_ERROR,
                ['Content-Type' => 'application/json']
            )
        );
    }
}
