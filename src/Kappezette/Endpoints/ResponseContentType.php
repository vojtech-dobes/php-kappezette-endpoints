<?php declare(strict_types=1);

namespace Kappezette\Endpoints;


interface ResponseContentType
{

	function getContentType(): string;



	function formatBody(mixed $data): string;

}
