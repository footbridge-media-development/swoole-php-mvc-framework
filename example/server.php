<?php

	require_once __DIR__ . "/../classes/ViewSettings.php";
	require_once __DIR__ . "/../classes/ControllerBase.php";
	require_once __DIR__ . "/../classes/RequestHandler.php";
	require_once __DIR__ . "/../classes/StaticFileHandler.php";
	require_once __DIR__ . "/../classes/Router.php";

	use Swoole\Http\Server;
	use Swoole\Http\Request;
	use Swoole\Http\Response;

	/**
	* Set up the directory for static file serving
	*/
	$staticFileHandler = new StaticFileHandler;
	$staticFileHandler->setStaticFilesDirectory(__DIR__ . "/static");

	/**
	* Set the views folder where Controllers
	* will search for view files in the ControllerBase
	*/
	$viewSettings = new ViewSettings;
	$viewSettings->setViewsFolder(__DIR__ . "/views");

	/**
	* Initialize the router and set
	* the folder for Controller classes
	*/
	$router = new Router;
	$router->setControllersFolder(__DIR__ . "/controllers");
	$router->loadMVCControllers($viewSettings);

	/**
	* Begin Swoole's HTTP2 server. SLL required for HTTP2.
	* No example for HTTP1 because you shouldn't be using
	* that anymore.
	*/
	$server = new Swoole\HTTP\Server("0.0.0.0", 9501, SWOOLE_PROCESS, SWOOLE_SOCK_TCP | SWOOLE_SSL);
	$server->set([
		"ssl_cert_file" => __DIR__ . "cert.pem",
		"ssl_key_file" => __DIR__ . "privkey.pem",
		"open_http2_protocol" => true,
	]);

	/**
	* Async event for server start
	*/
	$server->on("start", function (Server $server) {
		echo "Swoole HTTP server is started at on port 9501\n";
	});

	/**
	* When a request comes in. Could be for a route or a static file.
	* RequestHandler will process all of this for us.
	*/
	$server->on("request", function (Request $request, Response $response) use ($router, $staticFileHandler) {
		RequestHandler::process($router, $staticFileHandler, $request, $response);
	});

	$server->start();
