<?php

namespace Freddiegar\Base\Exceptions;

use Exception;
use FreddieGar\Rbac\Exceptions\VerifyPermissionException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Http\Response;
use Illuminate\Validation\UnauthorizedException;
use Illuminate\Validation\ValidationException;
use Laravel\Lumen\Exceptions\Handler as ExceptionHandler;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnsupportedMediaTypeHttpException;

/**
 * Class Handler
 * @package FreddieGar\Base\Exceptions
 */
class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that should not be reported.
     *
     * @var array
     */
    protected $dontReport = [
        AuthorizationException::class,
        HttpException::class,
        ModelNotFoundException::class,
        ValidationException::class,
    ];

    /**
     * Report or log an exception.
     *
     * This is a great spot to send exceptions to Sentry, Bugsnag, etc.
     *
     * @param  \Exception $e
     * @return void
     */
    public function report(Exception $e)
    {
        parent::report($e);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Exception $e
     * @return \Illuminate\Http\Response
     */
    public function render($request, Exception $e)
    {
        $response = null;

        if ($e instanceof UnauthorizedException) {
            $response = [
                'status' => Response::HTTP_UNAUTHORIZED,
                'title' => $e->getMessage(),
                'detail' => $e->getMessage(),
            ];
        }

        if ($e instanceof UnsupportedMediaTypeHttpException) {
            $response = [
                'status' => $e->getStatusCode(),
                'title' => $e->getMessage(),
                'detail' => $e->getMessage(),
            ];
        }

        if ($e instanceof NotFoundHttpException) {
            $response = [
                'status' => Response::HTTP_NOT_FOUND,
                'title' => $e->getMessage() ?: trans('exceptions.not_found'),
                'detail' => $e->getMessage(),
            ];
        }

        if ($e instanceof MethodNotAllowedHttpException) {
            $response = [
                'status' => Response::HTTP_METHOD_NOT_ALLOWED,
                'title' => $e->getMessage() ?: trans('exceptions.method_not_allowed'),
                'detail' => $e->getMessage(),
            ];
        }

        if ($e instanceof ModelNotFoundException) {
            $response = [
                'status' => Response::HTTP_NOT_FOUND,
                'title' => !empty($e->getModel())
                    ? trans('exceptions.model_not_found', ['model' => class_basename($e->getModel())])
                    : $e->getMessage(),
                'detail' => $e->getMessage(),
            ];
        }

        if ($e instanceof ValidationException) {
            $response = [
                'status' => Response::HTTP_UNPROCESSABLE_ENTITY,
                'title' => trans('exceptions.validation'),
                'detail' => ($e->getResponse())->original,
            ];
        }

        if ($e instanceof QueryException) {
            $response = [
                'status' => Response::HTTP_CONFLICT,
                'title' => trans('exceptions.validation'),
                'detail' => is_array($e->errorInfo) ? implode(' ', $e->errorInfo) : $e->errorInfo,
            ];
        }

        if ($e instanceof VerifyPermissionException) {
            $response = [
                'status' => Response::HTTP_UNAUTHORIZED,
                'title' => $e->getMessage(),
                'detail' => $e->getMessage(),
            ];
        }

        // @codeCoverageIgnoreStart
        if (!$response) {
            $response = [
                'status' => Response::HTTP_INTERNAL_SERVER_ERROR,
                'title' => trans('exceptions.internal_server_error'),
                'detail' => $e->getMessage(),
            ];
        }
        // @codeCoverageIgnoreEnd

        if (isDevelopment()) {
            $response['meta'] = [
                'exception' => get_class($e),
                'from' => $e->getFile() . ':' . $e->getLine(),
                'trace' => customizeTrace($e->getTrace()),
            ];
        }

        return responseJsonApiError($response);
    }
}
