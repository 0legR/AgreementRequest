<?php

namespace App\Services;

use Exception;

/**
 * 
 */
class ExceptionsService
{
	protected $message;

	function __construct($message)
	{
		$this->message = $message;
	}

	public function JSONString()
	{
		throw new Exception(json_encode(['error' => $this->message]));
	}

	public function JSONArray()
	{
		throw new Exception(json_encode($this->message));
	}
}