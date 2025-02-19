<?php

namespace AlgoYounes\Idempotency\Attributes;

use AlgoYounes\Idempotency\Entities\IdempotentRequest;
use AlgoYounes\Idempotency\Entities\IdempotentResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class IdempotencyAttributes extends AbstractAttributes
{
    private IdempotentRequest $request;
    private IdempotentResponse $response;

    public static function createFromHttpComponents(Request $request, Response|JsonResponse $response): self
    {
        return (new self)
            ->setRequest(IdempotentRequest::createFromRequest($request))
            ->setResponse(IdempotentResponse::createFromResponse($response));
    }

    public function getRequest(): IdempotentRequest
    {
        return $this->request;
    }

    public function getResponse(): IdempotentResponse
    {
        return $this->response;
    }

    public function setRequest(IdempotentRequest $request): self
    {
        $this->request = $request;

        return $this;
    }

    public function setResponse(IdempotentResponse $response): self
    {
        $this->response = $response;

        return $this;
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    public function getAttributes(): array
    {
        return [
            'request'  => $this->getRequest()->toArray(),
            'response' => $this->getResponse()->toArray(),
        ];
    }
}
