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
		public function route(string $requestMethod, string $uri, Request $request, Response $response){

			// Go through all the methods collected from the controller classes
			foreach ($this->routableMethods as $methodData){
				$classInstance = $methodData[0];
				$methods = $methodData[1];

				// Loop through the methods
				foreach($methods as $method){

					// Get the attributes (if any) of the method
					$attributes = $method->getAttributes();

					// Loop through any and all attributes
					foreach ($attributes as $attribute){
						$attrName = $attribute->getName();

						// Check if this attribute name is "Route"
						if ($attrName === "Route"){
							$arguments = $attribute->getArguments();

							// Check if the first argument (request method arg)
							// matches the server request method
							if (strtolower($arguments[0]) === strtolower($requestMethod)){

								// Check if the second argument (URI) for this method's route
								// matches the server request URI
								if ($arguments[1] === $uri){

									// Invoke the method (ReflectionMethod::invoke)
									return $method->invoke($classInstance, $request, $response);
								}
							}
						}
					}
				}
			}
		}
	}
