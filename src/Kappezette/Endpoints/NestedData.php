<?php declare(strict_types=1);

namespace Kappezette\Endpoints;


final class NestedData implements ArrayData
{

	public function __construct(
		public readonly mixed $data,
	) {}



	public function format(ResponseContentType $responseContentType): string
	{
		return $responseContentType->formatBody($this->data);
	}

}
