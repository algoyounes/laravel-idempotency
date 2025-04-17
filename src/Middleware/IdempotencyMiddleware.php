<?php

namespace AlgoYounes\Idempotency\Middleware;

use AlgoYounes\Idempotency\Attributes\IdempotencyAttributes;
use AlgoYounes\Idempotency\Config\IdempotencyConfig;
use AlgoYounes\Idempotency\Entities\Idempotency;
use AlgoYounes\Idempotency\Entities\IdempotentRequest;
use AlgoYounes\Idempotency\Exceptions\CheckSumMismatchIdempotencyException;
use AlgoYounes\Idempotency\Exceptions\DuplicateIdempotencyException;
use AlgoYounes\Idempotency\Exceptions\LockWaitExceededException;
use AlgoYounes\Idempotency\Exceptions\PathMismatchIdempotencyException;
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
     * @throws LockWaitExceededException|DuplicateIdempotencyException
     */
    public function handle(Request $request, Closure $next): Response|JsonResponse
    {
        if ($this->config->isNotEnabled() && $this->isEnforcedVerb($request) === false) {
            // @phpstan-ignore-next-line
            return $next($request);
        }

        $idempotencyKey = $this->getIdempotencyKey($request);
        if (! $idempotencyKey) {
            // @phpstan-ignore-next-line
            return $next($request);
        }

        $userId = UserIdResolver::resolve();

        $idempotency = $this->idempotencyManager->getIdempotency($idempotencyKey, $userId);
        if ($idempotency instanceof Idempotency) {
            return $this->processIdempotentRequest($idempotency, $request);
        }

        if (! $this->idempotencyManager->acquireLock($idempotencyKey, $userId)) {
            $this->idempotencyManager->waitForLock($idempotencyKey, $userId);
        }

        /** @var Response|JsonResponse $response */
        $response = $next($request);

        $response->headers->set($this->config->getRelayedHeader(), $idempotencyKey);

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
     * @throws DuplicateIdempotencyException
     * @throws CheckSumMismatchIdempotencyException|PathMismatchIdempotencyException
     */
    private function processIdempotentRequest(Idempotency $idempotency, Request $request): Response
    {
        $idempotentRequest = $idempotency->getIdempotentRequest();
        if ($idempotentRequest->isPathMismatched($request->path())) {
            throw new PathMismatchIdempotencyException(
                $idempotency->getIdempotencyKey(),
                $idempotency->getUserId(),
                $idempotentRequest
            );
        }

        if ($this->isChecksumMismatched($idempotentRequest, $request)) {
            throw new CheckSumMismatchIdempotencyException(
                $idempotency->getIdempotencyKey(),
                $idempotency->getUserId(),
                $idempotentRequest
            );
        }

        if ($this->config->isDuplicateHandlingException()) {
            throw new DuplicateIdempotencyException(
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

    private function isChecksumMismatched(IdempotentRequest $idempotentRequest, Request $request): bool
    {
        $existingChecksum = $idempotentRequest->getChecksum();
        $currentChecksum = IdempotentRequest::createFromRequest($request)->getChecksum();

        return $existingChecksum->notEquals($currentChecksum);
    }
}
