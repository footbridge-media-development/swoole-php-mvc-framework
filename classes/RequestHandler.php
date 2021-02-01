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

					/**
					* Set the cache-control header if there is a cache config for
					* the given mime type
					*/
					$cacheTime = $staticFileHandler->getCacheTimeForMime($mimeType);
					if ($cacheTime !== null){
						$response->header("cache-control", sprintf("max-age=%d", $cacheTime));
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

					return;
				}
			}

			// If the code made it here, just attempt to route it
			$routerChannel = new Swoole\Coroutine\Channel(1);
			go(function() use ($routerChannel, $response){
				$viewResponse = $routerChannel->pop();
				if ($viewResponse !== null){
					$response->end($viewResponse);
				}else{
					// Not found
					$response->end("404\n");
				}
			});

			$router->route($requestType, $requestURI, $request, $response, $routerChannel);
		}

	}
