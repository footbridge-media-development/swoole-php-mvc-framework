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
		public static function process(Router $router, Request $request, Response $response){
			$serverData = $request->server;
			$requestURI = $serverData['request_uri'];
			$clientIP = $serverData['remote_addr'];
			$requestType = $serverData['request_method'];

			if ($requestType === "GET"){
				$viewResponse = $router->route($requestURI, $request, $response);
				if ($viewResponse !== null){
					$response->end($viewResponse);
				}else{
					// TODO
					// Static file?
					$response->end("404\n");
				}
			}elseif ($requestType === "POST"){

			}
		}

	}
