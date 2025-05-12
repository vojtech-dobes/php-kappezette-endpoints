<?php declare(strict_types=1);

namespace Kappezette\Endpoints;


interface ArrayData
{

	function format(ResponseContentType $responseContentType): string;

}
