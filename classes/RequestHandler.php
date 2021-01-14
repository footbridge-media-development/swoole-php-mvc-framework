<?php

	require_once __DIR__ . "/ViewSettings.php";
	require_once __DIR__ . "/Router.php";

	use Swoole\Http\Request;
	use Swoole\Http\Response;

	class RequestHandler{

		/**
		* Processes and routes a Swoole request
		* @param ViewSettings $viewSettings
		* @param Router $router
		* @param Swoole\Http\Request $request
		* @param Swoole\Http\Request $request
		* @param Swoole\Http\Response $response
		*/
		public static function process(Router $router, StaticFileHandler $staticFileHandler, Request $request, Response $response){
			$serverData = $request->server;
			$requestURI = $serverData['request_uri'];
			$clientIP = $serverData['remote_addr'];
			$requestType = $serverData['request_method'];

			if ($requestType === "GET"){

				// Check for a static file
				if ($staticFileHandler->doesStaticFileExist($requestURI)){
					$mimeType = $staticFileHandler->getStaticFileMime($requestURI);
					if ($mimeType === null){
						$mimeType = "text/plain";
					}

					$response->header("content-type", $mimeType);
					$channel = new Swoole\Coroutine\Channel(1);
					$staticFileHandler->getStaticFileContents($requestURI, $channel);

					// Start the async function
					go(function() use ($channel, $response){
						// pop() will wait until something is on the coroutine stack
						// which will be whenever file contents are done being read
						// it holds this coroutine until the $channel gets something
						// pushed to it.
						$fileContents = $channel->pop();
						$response->end($fileContents);
					});
					
				}else{
					$viewResponse = $router->route($requestURI, $request, $response);
					if ($viewResponse !== null){
						$response->end($viewResponse);
					}else{
						// Not found
						$response->end("404\n");
					}
				}
			}elseif ($requestType === "POST"){

			}
		}

	}
