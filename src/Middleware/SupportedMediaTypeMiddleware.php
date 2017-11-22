<?php

namespace FreddieGar\Base\Middleware;

use Closure;
use Illuminate\Translation\Translator;
use Symfony\Component\HttpKernel\Exception\UnsupportedMediaTypeHttpException;

/**
 * Class MediaTypeMiddleware
 * @package FreddieGar\Base\Http\Middlewares
 */
class SupportedMediaTypeMiddleware
{
    const MEDIA_TYPE_SUPPORTED = 'application/vnd.api+json';

    /**
     * @var Translator
     */
    protected $translator;

    public function __construct(Translator $translator)
    {
        $this->translator = $translator;
    }

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
            throw new UnsupportedMediaTypeHttpException($this->translator->trans('exceptions.unsupported_media_type', ['media_type' => self::MEDIA_TYPE_SUPPORTED]));
        }

        return $next($request);
    }
}
