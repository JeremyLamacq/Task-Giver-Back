<?php

namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Catch all exceptions and turn them into json message.
 */
class ApiExceptionSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::EXCEPTION => 'onKernelException'
        ];
    }

    public function onKernelException(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();
        $data = [
            'status' => Response::HTTP_INTERNAL_SERVER_ERROR,
            'message' => $exception->getMessage()
        ];

        if ($exception instanceof HttpException) {
            $data['status'] = $exception->getStatusCode();
        }

        $event->setResponse(new JsonResponse($data, $data['status']));
    }
}
