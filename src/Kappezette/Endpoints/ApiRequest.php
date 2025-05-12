<?php declare(strict_types=1);

namespace Kappezette\Endpoints;

use Nette;


final class ApiRequest
{

	/** @var callable(): mixed */
	private mixed $bodyPayloadProvider;



	/**
	 * @param callable(): mixed $bodyPayloadProvider
	 */
	public function __construct(
		public readonly Nette\Application\Request $applicationRequest,
		callable $bodyPayloadProvider,
	)
	{
		$this->bodyPayloadProvider = $bodyPayloadProvider;
	}



	/**
	 * @return array<string, mixed>
	 */
	public function getUnverifiedParameters(): array
	{
		return $this->applicationRequest->getParameters();
	}



	public function getUnverifiedBodyPayload(): mixed
	{
		return ($this->bodyPayloadProvider)();
	}

}
