<?php declare(strict_types=1);

namespace Kappezette\Endpoints\DI;

use Kappezette\Endpoints;
use Nette;


final class Extension extends Nette\DI\CompilerExtension
{

	public function getConfigSchema(): Nette\Schema\Schema
	{
		return Nette\Schema\Expect::from(new ExtensionConfig());
	}



	public function loadConfiguration(): void
	{
		$containerBuilder = $this->getContainerBuilder();

		/** @var ExtensionConfig $config */
		$config = $this->getConfig();

		$containerBuilder->addDefinition($this->prefix('accessControl'))
			->setFactory(Endpoints\AccessControl::class, [
				'configuration' => $this->prefix('@defaultAccessControlConfiguration'),
			]);

		$containerBuilder->addDefinition($this->prefix('errorHandler'))
			->setFactory($config->errorHandler);

		$containerBuilder->addDefinition($this->prefix('defaultAccessControlConfiguration'))
			->setFactory(Endpoints\StaticAccessControlConfiguration::class, array_filter([
				'allowedHeaderPatterns' => $config->accessControlAllowedHeaderPatterns,
				'allowedHeaders' => $config->accessControlAllowedHeaders !== null
					? array_map('strtolower', $config->accessControlAllowedHeaders)
					: null,
				'allowedMethods' => $config->accessControlAllowedMethods !== null
					? array_map(
						static fn ($method) => Endpoints\HttpMethod::from($method),
						$config->accessControlAllowedMethods,
					)
					: null,
				'allowedOriginPatterns' => $config->accessControlAllowedOriginPatterns,
				'allowedOrigins' => $config->accessControlAllowedOrigins,
				'credentialsAllowed' => $config->accessControlCredentialsAllowed,
				'exposedHeaders' => $config->accessControlExposedHeaders !== null
					? array_map('strtolower', $config->accessControlExposedHeaders)
					: null,
				'maxAge' => $config->accessControlMaxAge,
			], static fn ($value) => $value !== null));

		$containerBuilder->addDefinition($this->prefix('requestBodyProvider'))
			->setFactory(Endpoints\RequestBodyProvider::class, [
				'implicitMimeType' => $config->requestBodyImplicitMimeType,
				'supportedMimeTypes' => array_combine(
					array_keys($config->requestBodySupportedMimeTypes),
					array_map(
						fn ($requestContentType, $mimeType) => $containerBuilder
							->addDefinition($this->prefix('supportedRequestBodyMimeType.' . strtr($mimeType, ['/' => ''])))
							->setFactory($requestContentType)
							->setAutowired(false),
						$config->requestBodySupportedMimeTypes,
						array_keys($config->requestBodySupportedMimeTypes),
					),
				),
			]);

		$containerBuilder->addDefinition($this->prefix('responseBodyFormatter'))
			->setFactory(Endpoints\ResponseBodyFormatter::class, [
				'defaultMimeType' => $config->responseBodyDefaultMimeType,
				'supportedMimeTypes' => array_combine(
					array_keys($config->responseBodySupportedMimeTypes),
					array_map(
						fn ($responseContentType, $mimeType) => $containerBuilder
							->addDefinition($this->prefix('supportedResponseBodyMimeType.' . strtr($mimeType, ['/' => ''])))
							->setFactory($responseContentType)
							->setAutowired(false),
						$config->responseBodySupportedMimeTypes,
						array_keys($config->responseBodySupportedMimeTypes),
					),
				),
			]);
	}



	public function beforeCompile(): void
	{
		$containerBuilder = $this->getContainerBuilder();

		/** @var Nette\DI\Definitions\ServiceDefinition $presenterService */
		foreach ($containerBuilder->findByType(Endpoints\ApiPresenter::class) as $presenterService) {
			$presenterService->addSetup('setAccessControl', [$this->prefix('@accessControl')]);
			$presenterService->addSetup('setErrorHandler', [$this->prefix('@errorHandler')]);
			$presenterService->addSetup('setHttpRequest');
			$presenterService->addSetup('setRequestBodyProvider', [$this->prefix('@requestBodyProvider')]);
			$presenterService->addSetup('setResponseBodyFormatter', [$this->prefix('@responseBodyFormatter')]);
		}
	}

}
