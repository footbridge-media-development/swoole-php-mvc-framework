<?php

	require_once __DIR__ . "/../../classes/ControllerBase.php";
	require_once __DIR__ . "/../../classes/RenderEngine/Renderer.php";

	use Swoole\Http\Request;
	use Swoole\Http\Response;

	class HomeController extends ControllerBase{

		/**
		* @param ViewSettings $viewSettings
		*/
		public function __construct($viewSettings){
			$this->viewSettings = $viewSettings;
			parent::__construct($viewSettings);
		}

		/**
		* @param Swoole\Http\Request $request
		* @param Swoole\Http\Response $response
		* @return string
		*/
		#[Route("GET", "/")]
		public function homePage(Request $request, Response $response, Swoole\Coroutine\Channel $routerChannel){
			$response->header("content-type", "text/html");

			// Get the view file
			$renderer = new RenderEngine\Renderer($this->getViewFilePath("home.php"), $this->viewSettings->viewsFolder);
			$renderer->renderViewFile($routerChannel);
		}

	}
