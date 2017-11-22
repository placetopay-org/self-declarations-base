<?php

namespace FreddieGar\Base\Middleware;

use Closure;
use Illuminate\Contracts\Auth\Factory as Auth;
use Illuminate\Http\Response;
use Illuminate\Translation\Translator;
use Illuminate\Validation\UnauthorizedException;

/**
 * Class AuthenticateMiddleware
 * @package FreddieGar\Base\Http\Middlewares
 */
class AuthenticateMiddleware
{
    /**
     * The authentication guard factory instance.
     *
     * @var \Illuminate\Contracts\Auth\Factory
     */
    protected $auth;

    /**
     * @var Translator
     */
    protected $translator;

    /**
     * Create a new middleware instance.
     *
     * Authenticate constructor.
     * @param Auth $auth
     */
    public function __construct(Auth $auth, Translator $translator)
    {
        $this->auth = $auth;
        $this->translator= $translator;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure $next
     * @param  string|null $guard
     * @return mixed
     */
    public function handle($request, Closure $next, $guard = null)
    {
        if ($this->auth->guard($guard)->guest()) {
            throw new UnauthorizedException($this->translator->trans('exceptions.credentials'), Response::HTTP_UNAUTHORIZED);
        }

        return $next($request);
    }
}
