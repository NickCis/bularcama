<?php
require_once __INCLUDE_PATH__ . "/spyc.php";
require_once __INCLUDE_PATH__ . "/Handlebars/Autoloader.php";

Handlebars\Autoloader::register();

use Handlebars\Handlebars;

class Bularcama {
	protected $base_dir = ".";
	protected $site_dir = "site";
	protected $layout_dir = "layout";
	protected $static_dir = "static";
	protected $out_dir = "_out";
	protected $ignore_site_files = array(
		"/^\..*?$/"
	);
	protected $engine;
	public $error = "";
	protected $default_context = array();

	public function Bularcama($conf=array()){
		if(is_array($conf)){
			if(array_key_exists("base_dir", $conf))
				$this->base_dir = $conf["base_dir"];
			if(array_key_exists("site_dir", $conf))
				$this->site_dir = $conf["site_dir"];
			if(array_key_exists("layout_dir", $conf))
				$this->layout_dir = $conf["layout_dir"];
			if(array_key_exists("out_dir", $conf))
				$this->out_dir = $conf["out_dir"];
			if(array_key_exists("static_dir", $conf))
				$this->static_dir = $conf["static_dir"];
			if(array_key_exists("ignore_site_file", $conf) && is_array($conf["ignore_site_file"]))
				$this->ignore_site_files = $conf["ignore_site_file"];

			$this->default_context = array(
				"__base_dir__" => $this->static_dir,
				"__site_dir__" => $this->site_dir,
				"__layout_dir__" => $this->layout_dir,
				"__static_dir__" => $this->static_dir,
				"__out_dir__" => $this->out_dir,
			);

			if(array_key_exists("default_context", $conf) && is_array($conf["default_context"])){
				foreach($conf["default_context"] as $name => $value)
					$this->default_context[$name] = $value;
			}
		}

		$this->engine = new Handlebars;
	}

	private function file_get_contents($path){
		if(is_array($path)){
			$str_path = $this->base_dir;
			foreach($path as $p){
				$str_path .= "/" . $p;
			}

			return $this->file_get_contents($str_path);
		}

		return file_get_contents($path);
	}

	/**
	 */
	function parse_file($path){
		$file = file_get_contents($path);
		$matches = array();
		$num_matches = preg_match_all('/<!--\s?BEGIN\s?(\S*)\s?-->\n?(.*?)\n?<!--\s?END\s?\1\s?-->/ms', $file, $matches, PREG_SET_ORDER);

		if(!$num_matches){
			$this->error = "No se matcheo ningun tag de informacion en el archivo \"" . $path . "\"";
			return false;
		}

		$name_matches = array();

		foreach($matches as $match){
			$name_matches[$match[1]] = $match[2];
		}

		$metadata = spyc_load($name_matches["metadata"]);
		$context = array_key_exists("vars", $metadata) ? $metadata["vars"] : array();
		$output = array(
			"file" => "",
			"template" => array(),
			"context" => array(),
			"vars" => $context
		);

		foreach($name_matches as $name => $value){
			if($name == "metadata")
				continue;

			$output["template"][$name] = $value;

			$local_context = array();

			if(array_key_exists("context", $metadata) && array_key_exists($name, $metadata["context"]))
				$local_context = json_decode(file_get_contents($metadata["context"][$name]), true);

			$output["context"][$name] = $local_context;

			$context[$name] = $this->engine->render($value, $local_context);
		}

		foreach($this->default_context as $key => $val){
			if(!array_key_exists($key, $context))
				$context[$key] = $val;
		}

		$output["file"] = $this->engine->render($this->get_layout($metadata["layout"]), $context);

		return $output;
	}

	function ignore_site_file($file, $extra=array()){
		foreach($this->ignore_site_files as $ig){
			if(preg_match($ig, $file))
				return true;
		}

		if(is_array($extra)){
			foreach($extra as $ig){
				if(preg_match($ig, $file))
					return true;
			}
		}

		return false;
	}

	function build_site(){
		if(!$this->copy_dir($this->static_dir, $this->out_dir))
			return false;
		if(!$this->build_site_dir($this->site_dir, $this->out_dir))
			return false;
		return true;
	}

	protected function build_site_dir($dir, $out){
		if(! is_dir($out)){
			if(!mkdir($out)){
				$this->error = "Problema al crear el directorio: \"" . $out . "\"";
				return false;
			}
		}

		$dir_fd = opendir($dir);
		while(false !== ($file = readdir($dir_fd))){
			if($this->ignore_site_file($file))
				continue;

			if(is_dir($dir."/".$file)){
				if(! $this->build_site_dir($dir."/".$file, $out."/".$file)){
					return false;
				}

				continue;
			}

			$file_parsed = $this->parse_file($dir . "/" . $file);

			if(!file_put_contents($out."/".$file, $file_parsed["file"])){
				$this->error = "Error escrbiendo el archivo[1]: \"" .$out . "/" . $file."\"";
				return false;
			}

			/*foreach( $file_parsed["template"] as $name => $template){
				$path_parts = pathinfo($file);
				$template_path = $out."/".$path_parts['filename']."_".$name.".template";

				if(!file_put_contents( $template_path, $template)){
					$this->error = "Error escrbiendo el archivo: \"" .$template_path."\"";
					return false;
				}
			}

			foreach( $file_parsed["context"] as $name => $data){
				$path_parts = pathinfo($file);
				$json_path = $out."/".$path_parts['filename']."_".$name.".json";

				if(!file_put_contents( $json_path, json_encode($data))){
					$this->error = "Error escrbiendo el archivo: \"" .$json_path."\"";
					return false;
				}
			}

			if($file_parsed["vars"]){
				$path_parts = pathinfo($file);
				$json_path = $out."/".$path_parts['filename'].".ctx.json";

				if(!file_put_contents( $json_path, json_encode($file_parsed["vars"]))){
					$this->error = "Error escrbiendo el archivo: \"" .$json_path."\"";
					return false;
				}
			}*/

			$path_parts = pathinfo($file);
			$file_path = $out."/".$path_parts['filename'].".template";
			if(!file_put_contents( $file_path, json_encode($file_parsed["template"]))){
				$this->error = "Error escrbiendo el archivo[2]: \"" .$file_path."\"";
				return false;
			}

			if(array_key_exists("vars", $file_parsed) && $file_parsed["vars"])
				$file_parsed["context"]["vars"] = $file_parsed["vars"];

			$file_path = $out."/".$path_parts['filename'].".json";
			if(!file_put_contents( $file_path, json_encode($file_parsed["context"]))){
				$this->error = "Error escrbiendo el archivo[3]: \"" .$file_path."\"";
				return false;
			}

		}

		return true;
	}

	protected function copy_dir($dir, $dest){
		if(! is_dir($dest)){
			if(!mkdir($dest)){
				$this->error = "Problema al crear el directorio: \"" . $dest . "\"";
				return false;
			}
		}

		$dir_fd = opendir($dir);
		while(false !== ($file = readdir($dir_fd))){
			if($this->ignore_site_file($file))
				continue;

			if(is_dir($dir."/".$file)){
				if(! $this->copy_dir($dir."/".$file, $dest."/".$file)){
					return false;
				}
				continue;
			}

			if(!copy($dir."/".$file, $dest."/".$file)){
				$this->error = "Problema al copiar archivo: \"" . $dir."/".$file . "\"";
				return false;
			}
		}

		return true;
	}

	function get_layout($layout){
		return $this->file_get_contents(array($this->layout_dir, $layout));
	}
}
?>
