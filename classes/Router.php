<?php
	use Swoole\Http\Request;
	use Swoole\Http\Response;

	class Router{

		private $controllersFolder = "";
		private $controllers = [];

		/**
		* Sets the controllers folder
		*/
		public function setControllersFolder(string $path){
			$this->controllersFolder = $path;
		}

		/**
		* Loads the MVC controller classes
		* from the controllers folder
		*/
		public function loadMVCControllers(ViewSettings $viewSettings){
			$fileNames = array_diff(scandir($this->controllersFolder), ['.','..']);

			foreach ($fileNames as $controllerFileName){
				// Ignore the base Controller
				$controllerPath = sprintf("%s/%s", $this->controllersFolder, $controllerFileName);

				// The class name _must_ be the file name minus the extension
				$className = pathinfo($controllerFileName, PATHINFO_FILENAME);
				require($controllerPath);
				$thisController = new $className($viewSettings);
				$this->controllers[] = $thisController;
			}
		}

		/**
		* @return string|null
		*/
		public function route(string $uri, Request $request, Response $response){
			foreach ($this->controllers as $controller){
				$routes = $controller->routes;
				foreach ($routes as $route){
					if ($uri === $route){
						return $controller->getResult($request, $response);
					}
				}
			}
		}
	}
