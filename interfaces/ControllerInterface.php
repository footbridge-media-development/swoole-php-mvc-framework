<?php

	use Swoole\Http\Request;
	use Swoole\Http\Response;

	interface ControllerInterface{

		public function getResult(Request $request, Response $response);

	}
