<?php

namespace App\Exceptions;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of exception types with their corresponding custom log levels.
     *
     * @var array<class-string<\Throwable>, \Psr\Log\LogLevel::*>
     */
    protected $levels = [
        //
    ];

    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<\Throwable>>
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }

    /**
     * @param Request $request
     * @param Throwable $e
     * @return mixed
     * @throws Throwable
     */
    public function render($request, Throwable $e): mixed
    {
        if ($e instanceof MethodNotAllowedHttpException) {
            throw new HttpResponseException(response()->json([
                'errors' => ['text' => [$e->getMessage()]],
                'status' => true
            ], Response::HTTP_METHOD_NOT_ALLOWED));
        }

        if ($e instanceof ModelNotFoundException) {
            throw new HttpResponseException(response()->json([
                'errors' => ['text' => [__('main.no_resource_was_found')]],
                'status' => true
            ], Response::HTTP_NOT_FOUND));
        }

        return parent::render($request, $e);
    }
}
