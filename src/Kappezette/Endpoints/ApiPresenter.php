<?php declare(strict_types=1);

namespace Kappezette\Endpoints;

use LogicException;
use Nette;
use Throwable;


abstract class ApiPresenter implements Nette\Application\IPresenter
{

	public AccessControl $accessControl;
	public ErrorHandler $errorHandler;
	public Nette\Http\IRequest $httpRequest;
	public RequestBodyProvider $requestBodyProvider;
	public ResponseBodyFormatter $responseBodyFormatter;

	/** @var array<string, (callable(ApiRequest, ApiResponse): (ArrayData|TextData|null))|null> */
	private array $methodHandlersCache = [];



	public function setAccessControl(AccessControl $accessControl): void
	{
		$this->accessControl = $accessControl;
	}



	public function setErrorHandler(ErrorHandler $errorHandler): void
	{
		$this->errorHandler = $errorHandler;
	}



	public function setHttpRequest(Nette\Http\IRequest $httpRequest): void
	{
		$this->httpRequest = $httpRequest;
	}



	public function setRequestBodyProvider(RequestBodyProvider $requestBodyProvider): void
	{
		$this->requestBodyProvider = $requestBodyProvider;
	}



	public function setResponseBodyFormatter(ResponseBodyFormatter $responseBodyFormatter): void
	{
		$this->responseBodyFormatter = $responseBodyFormatter;
	}



	public function setup(HttpMethod $httpMethod): void {}



	final public function run(Nette\Application\Request $request): Nette\Application\Response
	{
		$httpMethod = $request->getMethod();

		if ($httpMethod !== null) {
			$httpMethod = HttpMethod::tryFrom($httpMethod);
		}

		if ($httpMethod !== null) {
			$this->setup($httpMethod);
		}

		$apiResponse = new ApiResponse();

		$acceptedResponseMimeTypes = array_values(
			array_filter(
				array_map(
					static fn ($value) => trim(
						str_contains($value, ';')
							? explode(';', $value)[0]
							: $value,
					),
					explode(',', $this->httpRequest->getHeader('Accept') ?? ''),
				),
				static fn ($value) => $value !== '',
			),
		);

		try {
			$responseBody = $this->doRun($request, $httpMethod, $acceptedResponseMimeTypes, $apiResponse);
		} catch (ClientErrorException $e) {
			if ($e->statusCode !== null) {
				$apiResponse->setStatusCode($e->statusCode);
			}

			$apiResponse->addHttpHeaders($e->httpHeaders);

			$responseBody = $this->formatData(
				$apiResponse,
				$this->responseBodyFormatter->getErrorResponseContentType($acceptedResponseMimeTypes),
				$e->getData(),
			);
		}

		return new NetteApplicationResponse(
			$apiResponse,
			$responseBody,
		);
	}



	/**
	 * @param list<string> $acceptedResponseMimeTypes
	 * @throws ClientErrorException
	 */
	private function doRun(
		Nette\Application\Request $request,
		?HttpMethod $httpMethod,
		array $acceptedResponseMimeTypes,
		ApiResponse $apiResponse,
	): ?string
	{
		$origin = $this->httpRequest->getHeader('Origin');

		if ($origin !== null) {
			$apiResponse->addHttpHeaders(
				$this->accessControl->createMainHeaders($origin),
			);
		}

		if ($httpMethod === null) {
			$this->processUnsupportedMethod($request, $apiResponse);
		}

		$handler = $this->getMethodHandler($httpMethod, $request);

		if ($handler === null) {
			$this->processUnsupportedMethod($request, $apiResponse);
		}

		$apiRequest = new ApiRequest(
			$request,
			$httpMethod->canHaveRequestBody()
				? $this->requestBodyProvider->getRequestBodyProvider($this->httpRequest, $apiResponse)
				: static function () use ($httpMethod): never {
					throw new LogicException("HTTP method '{$httpMethod->value}' can't have body");
				},
		);

		$responseContentType = $this->responseBodyFormatter->getResponseContentType($acceptedResponseMimeTypes);

		if ($responseContentType === null) {
			throw ClientErrorException::create('accept_not_supported')
				->withErrorMessage("Response content type isn't supported")
				->withStatusCode(Nette\Http\IResponse::S406_NotAcceptable);
		}

		try {
			$data = $handler($apiRequest, $apiResponse);
		} catch (Throwable $e) {
			if ($e instanceof ClientErrorException) {
				throw $e;
			}

			if ($e instanceof InvalidRequestBodyException) {
				throw ClientErrorException::create('invalid_request_body')
					->withErrorMessage("Request body isn't valid")
					->withErrorContext('errors', $e->errors)
					->withStatusCode(Nette\Http\IResponse::S400_BadRequest);
			}

			if ($e instanceof InvalidRequestParamsException) {
				throw ClientErrorException::create('invalid_request_params')
					->withErrorMessage("Request params aren't valid")
					->withErrorContext('errors', $e->errors)
					->withStatusCode(Nette\Http\IResponse::S400_BadRequest);
			}

			$this->errorHandler->handleError($e);

			$apiResponse->setStatusCode(Nette\Http\IResponse::S500_InternalServerError);
			$data = new ErrorData(
				errorCode: 'unexpected_error',
				errorMessage: null,
				errorContext: [],
			);
		}

		return $this->formatData(
			$apiResponse,
			$responseContentType,
			$data,
		);
	}



	private function formatData(
		ApiResponse $apiResponse,
		ResponseContentType $responseContentType,
		ArrayData|TextData|null $data,
	): ?string
	{
		if ($data === null) {
			if (
				$apiResponse->getStatusCode() === null
				|| $apiResponse->getStatusCode() === Nette\Http\IResponse::S200_OK
			) {
				$apiResponse->setStatusCode(Nette\Http\IResponse::S204_NoContent);
			}

			return null;
		}

		if ($data instanceof TextData) {
			$apiResponse->setHttpHeader('Content-Type', $data->mimeType);
			return $data->content;
		}

		$apiResponse->setHttpHeader('Content-Type', $responseContentType->getContentType());
		return $data->format($responseContentType);
	}



	/**
	 * @return (callable(ApiRequest, ApiResponse): (ArrayData|TextData|null))|null
	 */
	private function getMethodHandler(
		HttpMethod $method,
		Nette\Application\Request $request,
	): ?callable
	{
		if (array_key_exists($method->value, $this->methodHandlersCache)) {
			return $this->methodHandlersCache[$method->value];
		}

		if ($this instanceof MethodAware) {
			$result = $this->getDynamicMethodHandler($method, $request);

			return match (true) {
				$result !== null => $result,
				$method === HttpMethod::Head => match ($this->getDynamicMethodHandler(HttpMethod::Get, $request) !== null) {
					true => $this->processDefaultHead(...),
					false => null,
				},
				$method === HttpMethod::Options => $this->processDefaultOptions(...),
				default => null,
			};
		}

		return match ($method) {
			HttpMethod::Delete => $this instanceof MethodDelete ? $this->processDelete(...) : null,
			HttpMethod::Get => $this instanceof MethodGet ? $this->processGet(...) : null,
			HttpMethod::Head => match (true) {
				$this instanceof MethodHead => $this->processHead(...),
				$this instanceof MethodGet => $this->processDefaultHead(...),
				default => null,
			},
			HttpMethod::Options => $this instanceof MethodOptions ? $this->processOptions(...) : $this->processDefaultOptions(...),
			HttpMethod::Patch => $this instanceof MethodPatch ? $this->processPatch(...) : null,
			HttpMethod::Post => $this instanceof MethodPost ? $this->processPost(...) : null,
			HttpMethod::Put => $this instanceof MethodPut ? $this->processPut(...) : null,
		};
	}



	/**
	 * @throws ClientErrorException
	 */
	final protected function processDefaultHead(
		ApiRequest $apiRequest,
		ApiResponse $apiResponse,
	): null
	{
		try {
			$getMethodHandler = $this->getMethodHandler(
				HttpMethod::Get,
				$apiRequest->applicationRequest,
			) ?? throw new LogicException(
				__METHOD__ . " can't be called if GET isn't implemented",
			);

			$getMethodHandler($apiRequest, $apiResponse);
		} catch (ClientErrorException $e) {
			if ($e->statusCode !== null) {
				$apiResponse->setStatusCode($e->statusCode);
			}

			$apiResponse->addHttpHeaders($e->httpHeaders);
		} finally {
			return null;
		}
	}



	final protected function processDefaultOptions(
		ApiRequest $apiRequest,
		ApiResponse $apiResponse,
	): null
	{
		$apiResponse->setStatusCode(Nette\Http\IResponse::S204_NoContent);
		$apiResponse->addHttpHeader('Accept', $this->requestBodyProvider->listSupportedMimeTypes());

		$accessControlRequestMethod = $this->httpRequest->getHeader('Access-Control-Request-Method');

		if ($accessControlRequestMethod !== null) {
			$apiResponse->addHttpHeaders(
				$this->accessControl->createPreflightHeaders(
					$this->listAllowedHttpMethods($apiRequest->applicationRequest),
					$accessControlRequestMethod,
					$this->httpRequest->getHeader('Access-Control-Request-Headers'),
				),
			);
		}

		return null;
	}



	/**
	 * @throws ClientErrorException
	 */
	private function processUnsupportedMethod(
		Nette\Application\Request $request,
		ApiResponse $apiResponse,
	): never
	{
		throw ClientErrorException::create('method_not_allowed')
			->withErrorMessage('Method not allowed')
			->withStatusCode(Nette\Http\IResponse::S405_MethodNotAllowed)
			->withHttpHeader('Allow', join(', ', array_map(
				static fn ($method) => $method->value,
				$this->listAllowedHttpMethods($request),
			)));
	}



	/**
	 * @return list<HttpMethod>
	 */
	private function listAllowedHttpMethods(
		Nette\Application\Request $request,
	): array
	{
		return array_values(
			array_filter(
				HttpMethod::cases(),
				fn ($method) => $this->getMethodHandler($method, $request) !== null,
			),
		);
	}

}
