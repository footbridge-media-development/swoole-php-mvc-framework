<?php

	require_once __DIR__ . "/../classes/ControllerBase.php";
	require_once __DIR__ . "/../interfaces/ControllerInterface.php";
	require_once __DIR__ . "/../classes/FileBuffer.php";

	use Swoole\Http\Request;
	use Swoole\Http\Response;

	class HomeController extends ControllerBase implements ControllerInterface{

		private $viewName = "home.php";
		public $routes = [
			"/",
		];

		/**
		* @param ViewSettings $viewSettings
		*/
		public function __construct($viewSettings){
			parent::__construct($viewSettings);
		}

		/**
		* @param Swoole\Http\Request $request
		* @param Swoole\Http\Response $response
		* @return string
		*/
		public function getResult(Request $request, Response $response){
			$response->header("content-type", "text/html");
			$buffer = new FileBuffer($this->getViewFilePath($this->viewName));
			$buffer->buffer();

			return $buffer->getResult();
		}

	}
