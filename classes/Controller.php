<?php

	use Swoole\Http\Request;
	use Swoole\Http\Response;

	class Controller{
		protected $viewsFolder = __DIR__ . "/../../views";

		public function getViewFilePath(string $fileName){
			return sprintf("%s/%s", $this->viewsFolder, $fileName);
		}

		public function getResult(Request $request, Response $response){}
	}
