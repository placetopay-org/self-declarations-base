<?php

namespace FreddieGar\Base\Middleware;

use Closure;
use Illuminate\Cache\RateLimiter;
use Illuminate\Translation\Translator;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class ThrottleRequestMiddleware
 * @package FreddieGar\Base\Http\Middlewares
 */
class ThrottleRequestMiddleware
{
    /**
     * The rate limiter instance.
     *
     * @var RateLimiter
     */
    protected $limiter;

    /**
     * @var Translator
     */
    protected $translator;

    /**
     * Create a new request throttler.
     *
     * @param  RateLimiter $limiter
     * @param  Translator $translator
     */
    public function __construct(RateLimiter $limiter, Translator $translator)
    {
        $this->limiter = $limiter;
        $this->translator = $translator;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure $next
     * @param  int $maxAttempts
     * @param  int $decayMinutes
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function handle($request, Closure $next, $maxAttempts = 60, $decayMinutes = 1)
    {
        $key = $this->resolveRequestSignature($request);
        if ($this->limiter->tooManyAttempts($key, $maxAttempts, $decayMinutes)) {
            return $this->buildResponse($key, $maxAttempts);
        }
        $this->limiter->hit($key, $decayMinutes);
        $response = $next($request);

        $response->headers->add($this->getHeaders(
            $maxAttempts,
            $this->calculateRemainingAttempts($key, $maxAttempts)
        ));

        return $response;
    }

    /**
     * Resolve request signature.
     *
     * @param  \Illuminate\Http\Request $request
     * @return string
     */
    protected function resolveRequestSignature($request)
    {
        return shaN(implode('|', [
                $request->method(),
                $request->server('SERVER_NAME'),
                $request->path(),
                $request->ip()]
        ));
    }

    /**
     * Create a 'too many attempts' response.
     *
     * @param  string $key
     * @param  int $maxAttempts
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    protected function buildResponse($key, $maxAttempts)
    {
        $retryAfter = $this->limiter->availableIn($key);
        $message = $this->translator->trans('exceptions.max_attempts', ['seconds' => $retryAfter]);

        $response = [
            'status' => Response::HTTP_TOO_MANY_REQUESTS,
            'title' => $message,
            'detail' => $message,
        ];

        $headers = $this->getHeaders(
            $maxAttempts,
            $this->calculateRemainingAttempts($key, $maxAttempts, $retryAfter),
            $retryAfter
        );

        return responseJsonApiError($response, $headers);
    }

    /**
     * Add the limit header information to the given response.
     *
     * @param  int $maxAttempts
     * @param  int $remainingAttempts
     * @param  int|null $retryAfter
     * @return array
     */
    protected function getHeaders($maxAttempts, $remainingAttempts, $retryAfter = null)
    {
        $headers = [
            'X-RateLimit-Limit' => $maxAttempts,
            'X-RateLimit-Remaining' => $remainingAttempts,
        ];
        if (!is_null($retryAfter)) {
            $headers['Retry-After'] = $retryAfter;
        }
        return $headers;
    }

    /**
     * Calculate the number of remaining attempts.
     *
     * @param  string $key
     * @param  int $maxAttempts
     * @param  int|null $retryAfter
     * @return int
     */
    protected function calculateRemainingAttempts($key, $maxAttempts, $retryAfter = null)
    {
        if (!is_null($retryAfter)) {
            return 0;
        }
        return $this->limiter->retriesLeft($key, $maxAttempts);
    }
}
