<?php
	require_once __DIR__ . "/MimeTypes.php";

	class StaticFileHandler{
		public $staticDirectory = "";

		public function setStaticFilesDirectory(string $directoryPath){
			$this->staticDirectory = $directoryPath;
		}

		public function getFullStaticFilePath(string $filePath){
			return sprintf("%s/%s", $this->staticDirectory, $filePath);
		}

		/**
		* Whether or not a static file exists at the path
		* @param string $filePath
		* @return bool
		*/
		public function doesStaticFileExist(string $filePath){
			$fullPath = $this->getFullStaticFilePath($filePath);
			return file_exists($fullPath) && !is_dir($fullPath);
		}

		/**
		* Gets the mime type of the file based on the extension
		* @param string $filePath
		* @return string|null
		*/
		public function getStaticFileMime(string $filePath){
			$extension = pathinfo($filePath, PATHINFO_EXTENSION);
			if ($extension !== ""){
				if (isset(MimeTypes::RECOGNIZED_EXTENSIONS[$extension])){
					return MimeTypes::RECOGNIZED_EXTENSIONS[$extension];
				}else{
					return null;
				}
			}else{
				return null;
			}
		}

		/**
		* Gets the mime type of the file based on the extension
		* @param string $filePath
		* @param Swoole\Coroutine\Channel $channel The file contents will be pushed onto the coroutines stack
		*/
		public function getStaticFileContents(string $filePath, Swoole\Coroutine\Channel $channel){
			$fullPath = $this->getFullStaticFilePath($filePath);

			go(function() use ($fullPath, $channel){
				$result = Co\System::readFile($fullPath);
				$channel->push($result);
			});

		}
	}
