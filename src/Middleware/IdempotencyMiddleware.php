<?php

namespace AlgoYounes\Idempotency\Middleware;

use AlgoYounes\Idempotency\Attributes\IdempotencyAttributes;
use AlgoYounes\Idempotency\Config\IdempotencyConfig;
use AlgoYounes\Idempotency\Entities\Idempotency;
use AlgoYounes\Idempotency\Entities\IdempotentRequest;
use AlgoYounes\Idempotency\Exceptions\DuplicateIdempotencyRequestException;
use AlgoYounes\Idempotency\Exceptions\LockWaitExceededException;
use AlgoYounes\Idempotency\Managers\IdempotencyManager;
use AlgoYounes\Idempotency\Resolvers\UserIdResolver;
use Closure;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class IdempotencyMiddleware
{
    public function __construct(
        private readonly IdempotencyManager $idempotencyManager,
        private readonly ResponseFactory $responseFactory,
        private readonly IdempotencyConfig $config
    ) {}

    /**
     * @throws LockWaitExceededException|DuplicateIdempotencyRequestException
     */
    public function handle(Request $request, Closure $next): Response|JsonResponse
    {
        if ($this->config->isNotEnabled() && $this->isEnforcedVerb($request) === false) {
            return $next($request);
        }

        $idempotencyKey = $this->getIdempotencyKey($request);
        if (! $idempotencyKey) {
            return $next($request);
        }

        $userId = UserIdResolver::resolve();

        $idempotency = $this->idempotencyManager->getIdempotency($idempotencyKey, $userId);
        if ($idempotency instanceof Idempotency) {
            return $this->processIdempotentRequest($idempotency, $request, $next);
        }

        if (! $this->idempotencyManager->acquireLock($idempotencyKey, $userId)) {
            $this->idempotencyManager->waitForLock($idempotencyKey, $userId);
        }

        /** @var Response $response */
        $response = $next($request);

        if (! $response->isSuccessful() || $response->isServerError()) {
            $this->idempotencyManager->releaseLock($idempotencyKey, $userId);

            return $response;
        }

        $idempotencyAttributes = IdempotencyAttributes::createFromHttpComponents($request, $response);

        $this->idempotencyManager->create($idempotencyKey, $userId, $idempotencyAttributes);

        $this->idempotencyManager->releaseLock($idempotencyKey, $userId);

        return $response;
    }

    private function isEnforcedVerb(Request $request): bool
    {
        return in_array($request->getMethod(), $this->config->getEnforcedVerbs(), true);
    }

    private function getIdempotencyKey(Request $request): ?string
    {
        $idempotencyKey = $request->header($this->config->getIdempotencyHeader());

        return is_string($idempotencyKey) ? $idempotencyKey : null;
    }

    /**
     * @throws DuplicateIdempotencyRequestException
     */
    private function processIdempotentRequest(Idempotency $idempotency, Request $request, Closure $next): Response
    {
        $idempotentRequest = $idempotency->getIdempotentRequest();
        if ($this->isPathMismatched($idempotentRequest, $request)) {
            return $next($request);
        }

        if ($this->isChecksumMismatched($idempotentRequest, $request)) {
            return $next($request);
        }

        if ($this->config->isDuplicateHandlingException()) {
            throw new DuplicateIdempotencyRequestException(
                $idempotency->getIdempotencyKey(),
                $idempotency->getUserId(),
                $idempotentRequest
            );
        }

        return $this->responseFactory->make(
            $idempotency->getIdempotentResponse()->getBody(),
            $idempotency->getIdempotentResponse()->getStatus(),
            [
                ...$idempotency->getIdempotentResponse()->getHeaders(),
                $this->config->getRelayedHeader() => $idempotency->getIdempotencyKey(),
            ]
        );
    }

    private function isPathMismatched(IdempotentRequest $idempotentRequest, Request $request): bool
    {
        return $idempotentRequest->getPath() !== $request->path();
    }

    private function isChecksumMismatched(IdempotentRequest $idempotentRequest, Request $request): bool
    {
        $existingChecksum = $idempotentRequest->getChecksum();
        $currentChecksum = IdempotentRequest::createFromRequest($request)->getChecksum();

        return $existingChecksum->notEquals($currentChecksum);
    }
}
