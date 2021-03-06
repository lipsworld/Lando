<?php

class Controller {
	public $config;
	private $Model;
	public $theme_vars;
	
	public function __construct() {
		//make config available to methods and helper functions
		global $config;
		$this->config = $config;
		
		$this->Model = new Model();
		
		$this->define_theme_vars();
	}
	
	private function define_theme_vars() {
		$vars["site_title"] 			= stripslashes(htmlspecialchars($this->config["site_title"], ENT_NOQUOTES));
		$vars["site_description"] = stripslashes(htmlspecialchars($this->config["site_description"], ENT_NOQUOTES));
		$vars["theme_dir"] 				= $this->config["site_root"]."/themes/".$this->config["theme"];
		
		$vars["site_root"] = $this->config["site_root"];
		if(!$this->config["pretty_urls"])
			$vars["site_root"] .= "/index.php";
		
		$this->theme_vars = $vars;
	}
	
	private function get_content_path($url=null) {
		if(!$url)
			$url = current_path();
		
		if($url == "/")
			$url .= "home";

		$url = explode("/", trim_slashes($url));

		if(isset($url[0]) && in_array($url[0], array("posts", "drafts")))
			$root = array_shift($url);
		else
			$root = "pages";

		array_unshift($url, $root);

		return implode("/", $url);;
	}
	
	public function get_content($type=null, $names=null) {
		if(!$type && !$names) //get content for current page
			return $this->Model->get_single($this->get_content_path());
		
		if(!$type) //must provide a content type
			return false;
	
		if(!$names) //get unfiltered
			return $this->Model->get_all($type);
		
		if(is_string($names)) //filter to individual content
			return $this->Model->get_single("$type/$names");
			
		if(is_array($names)) { //filter to multiple content
			$items = array();
		
			foreach($names as $name)
				$items[] = $this->Model->get_single("$type/$name");
			
			return $items;
		}
		
		return false;
	}
	
	private function loose_match($a, $b) {
		if(is_object($a))
			$a = get_object_vars($a);
	
		if(is_object($b))
			$b = get_object_vars($b);
	
		//if comparing value to array
		if(!is_array($a) && is_array($b)) {
			//if string, explode A as CSV and compare to B
			if(is_string($a)) {
				$arr = explode(",", $a);
				foreach($arr as $i => $str)
					$arr[$i] = trim($str);
				return $this->loose_match($arr, $b);
			}
			else //otherwise look for A in B
				return in_array($a, $b);
		}
		
		//if comparing array to value, fail
		elseif(is_array($a) && !is_array($b))
			return false;
		
		//if comparing arrays, B must contain A but any order
		elseif(is_array($a) && is_array($b))
			return (sizeof(array_udiff($a, $b, 'strcasecmp')) == 0);
		
		//otherwise convert to string and do case-insensitive comparison
		else
			return (strcasecmp($a, $b) == 0);
	}
	
	private function filter_by_props($content, $filter=array()) {
		if(is_callable($filter))
			return array_filter($content, $filter);

		if(is_array($filter) && !empty($filter)) {
			$filtered = array();
		
			foreach($content as $Item) {
				$match = true;
			
				foreach($filter as $key => $val) {
					if((!isset($Item->$key) || !$this->loose_match($val, $Item->$key())) &&
						 (!isset($Item->manual_metadata[$key]) || !$this->loose_match($val, $Item->metadata($key)))) {
							$match = false;
							break;
					}
				}
				
				if($match)
					$filtered[] = $Item;
			}
			
			$content = $filtered;
		}
		
		return $content;
	}
	
	public function filter_content($content, $limit=0, $offset=0, $filter=array(), $year=0, $month=0, $day=0) {
		if(!is_array($content) || empty($content))
			return $content;
		
		$content = $this->filter_by_props($content, $filter);

		$year 	= (int)$year;
		$month 	= (int)$month;
		$day 		= (int)$day;

		if($year) {
			$date_types = array("published", "created");
			
			foreach($content as $Item) {
				foreach($date_types as $type) {
					if(isset($Item->$type))
						$date = $Item->$type;
				}
				
				if(isset($date))
					$by_date[date('Y', $date)][date('n', $date)][date('d', $date)][] = $Item;
			}
			
			//flatten down array to filter level
			if(!isset($by_date[$year]))
				$by_date = array();
			elseif($month) {
				if(!isset($by_date[$year][$month]))
					$by_date = array();
				elseif($day) {
					if(!isset($by_date[$year][$month][$day]))
						$by_date = array();
					else
						$by_date = array_flatten($by_date[$year][$month][$day]);
				}
				else
					$by_date = array_flatten($by_date[$year][$month]);
			}
			else
				$by_date = array_flatten($by_date[$year]);
			
			$content = $by_date;
		}
		
		return array_offset_limit($content, $offset, $limit);
	}
	
	public function filter_collection($Collection, $limit=0, $offset=0, $filter=array()) {
		if(empty($Collection->files))
			return $Collection;
		
		$Collection->files = $this->filter_by_props($Collection->files, $filter);
		$Collection->files = array_offset_limit($Collection->files, $offset, $limit);
		
		return $Collection;
	}
	
	public function get_file($path, $thumb=false) {
		return $this->Model->get_file($path, $thumb);
	}
	
	public function install_content($local_path, $host_path="", $log_file=false) {
		$this->Model->install_content($local_path, $host_path, $log_file);
	}

	public function publish_draft($slug) {
		return $this->Model->publish_draft($slug);
	}

	public function get_all_fresh($type) {
		return $this->Model->get_all($type, 0);
	}
	
	public function get_share_url($path, $short_url=false) {
		$path = resolve_path($path, $this->get_content_path());
		return $this->Model->get_share_url($path, $short_url);
	}
	
	public function get_download_url($path) {
		return preg_replace('~://www.~', "://dl.", $this->get_share_url($path, false));
	}
		
	public function register_include_functions($functions) {
		if(!is_array($functions))
			$functions = array($functions);
		
		if(!isset($this->config["custom_include_functions"]))
			$this->config["custom_include_functions"] = array();
		
		foreach($functions as $function) {
			if(gettype($function) == "string")
				$this->config["custom_include_functions"][] = $function;
		}
	}
}

