<?php
	use Swoole\Http\Request;
	use Swoole\Http\Response;

	class Router{

		private $controllersFolder = "";
		private $controllers = [];

		/** @property ReflectionMethod[] $routableMethods */
		public $routableMethods = [];

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
				$classReflector = new \ReflectionClass($className);
				$controllerMethods = $classReflector->getMethods(ReflectionMethod::IS_PUBLIC);
				$this->routableMethods[] = [new $className($viewSettings), $controllerMethods];
			}
		}

		/**
		* @return string|null
		*/
		public function route(string $method, string $uri, Request $request, Response $response){
			foreach ($this->routableMethods as $methodData){
				$classInstance = $methodData[0];
				$methods = $methodData[1];
				foreach($methods as $method){
					$attributes = $method->getAttributes();
					foreach ($attributes as $attribute){
						$attrName = $attribute->getName();
						print($attrName);
						if ($attrName === "Route"){
							$arguments = $attribute->getArguments();
							if (strtolower($arguments[0]) === strtolower($method)){
								if ($arguments[1] === $uri){
									print("Method passed");
									$method->invoke($classInstance, $request, $response);
								}
							}
						}
					}
				}
			}
		}
	}
