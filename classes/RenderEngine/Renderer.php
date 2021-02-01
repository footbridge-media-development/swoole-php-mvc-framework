<?php
	namespace RenderEngine;

	require_once __DIR__ . "/Parser.php";

	class Renderer{

		public ?\Swoole\Coroutine\Channel $renderChannel;
		public ?string $fileLocation;
		public ?string $viewsFolder;

		public function __construct(string $fileLocation, string $viewsFolder){
			$this->renderChannel = new \Swoole\Coroutine\Channel(1);
			$this->fileLocation = $fileLocation;
			$this->viewsFolder = $viewsFolder;
		}

		public function renderViewFile(\Swoole\Coroutine\Channel $routerChannel){
			$renderChannel = $this->renderChannel;
			$fileLocation = $this->fileLocation;
			go(function() use ($routerChannel, $fileLocation, $renderChannel){
				$parser = new Parser($fileLocation, $renderChannel);
				$fileContents = $renderChannel->pop();
				$parser->setFileContents($fileContents);
				$parser->parse();

				$layoutFileRelativeToViews = $parser->directives['@Layout'];
				$layoutFilePath = $this->viewsFolder . $layoutFileRelativeToViews;

				if (!realpath($layoutFilePath)){
					$routerChannel->push("The view file path $layoutFileRelativeToViews does not exist in the current View context.");
					return;
				}

				$viewResult = "";
				$htmlBody = $parser->directives['@Body'];
				$htmlHead = $parser->directives['@Head'];
				ob_start();
				include($layoutFilePath);
				$viewResult = ob_get_contents();
				ob_end_clean();

				// Push the parsed view
				$routerChannel->push($viewResult);
			});
		}
	}
