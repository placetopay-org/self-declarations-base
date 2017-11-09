<?php

namespace FreddieGar\Base\Middlewares;

use Closure;
use Symfony\Component\HttpKernel\Exception\UnsupportedMediaTypeHttpException;

/**
 * Class MediaTypeMiddleware
 * @package FreddieGar\Base\Http\Middlewares
 */
class SupportedMediaTypeMiddleware
{
    const MEDIA_TYPE_SUPPORTED = 'application/vnd.api+json';

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (strtolower($request->headers->get('Content-Type')) !== self::MEDIA_TYPE_SUPPORTED) {
            throw new UnsupportedMediaTypeHttpException(trans('exceptions.unsopported_media_type', ['media_type' => self::MEDIA_TYPE_SUPPORTED]));
        }

        return $next($request);
    }
}
