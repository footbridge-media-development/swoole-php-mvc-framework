<?php

	require_once __DIR__ . "/ViewSettings.php";

	use Swoole\Http\Request;
	use Swoole\Http\Response;

	class ControllerBase{

		/** @property string $viewSettings */
		public $viewSettings = "";

		public function __construct(ViewSettings $viewSettings){
			$this->viewSettings = $viewSettings;
		}

		public function getViewFilePath(string $fileName){
			return sprintf("%s/%s", $this->viewSettings->viewsFolder, $fileName);
		}

	}
