<?php
	#[Attribute(Attribute::TARGET_METHOD)]
	class Route{

		private string $method;
		private string $uri;

		/**
		* @param string $method The HTTP method for this route
		* @param string $uri The URI this route will match
		*/
		public function construct(string $method, string $uri){
			$this->method = $method;
			$this->uri = $uri;
		}
	}
