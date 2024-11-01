<?php

#######################################################################
#
# A front-end class for generating tree views based on a path array
# in various differnt styles
#
#######################################################################
# Part of the Wolfe Candy Creations (C) 2022 plugins

class WlfC_Treeview {

	// class arguments
	var $tree_source = array();
	var $HTML_out = "";
	var $root = "\\";
	var $slug = "";
	var $source = "plugin-folder";
	
	var $manual_source = "";
	
	var $endpoint_filter=""; // CSV - spaces count in search in format .png,.jpg,.pdf (.png or .jpg or .pdf)
	var $tree_filter=""; // CSV - spaces count in search
	var $hidden_filter = "true"; // exclude files or folders starting with "."
	
	// icons can be specified as icon names or special characters/html entities using &xx;
	var $icon_home = ""; 
	var $icon_middle = ""; 
	var $icon_endpoint = ""; 
	
	// endpoint processing
	var $endpoint_name = "";
	var $endpoint_url = "";
	var $calling_url = ""; 			// return URL of calling page without arg set
	var $return_url = "";			// return url of calling page with '?endpoint=[endpoint_name]' set
	
	var $endpoint_image = "true"; // displays an image tile from media in the endpoint
	var $endpoint_image_size = "30"; // size of square image tile
	var $endpoint_link = "true"; // provide link at endpoint
	var $endpoint_link_pattern = "<a href='[endpoint_url]'>[endpoint_name]</a>"; 
#	var $endpoint_link_pattern = "<a href='[return_url]'>[endpoint_name]</a>"; // example to return POST endpoint name to calling page to process using different plugin shortcode
#	var $endpoint_link_pattern = "<a href='https://custom.wordpress.url.slug?endpoint=[endpoint_name]'>[endpoint_name]</a>"; // example to return POST endpoint name to another specified page to process it
#	var $endpoint_link_pattern = "<a href='[calling_url]?ep=[endpoint_name]&epu=[endpoint_url]'>[endpoint_name]</a>"; // example to return custom POST args to the calling page
	
	var $endpoint_integration_call = ""; // alternative function to call to provide the endpoint_url
	
	private $_is_endpoint = false; 	// flag is set if node is an endpoint - internal use only
	private $_endpoint_href = "";	// endpoint link built using pattern and current values
	private $_endpoint_img = "";	// html img tag for any images
	
	// style options
	var $style_template = "elegant2"; // CSS template to use
	var $style_order = "ul"; // or "ol" for numbered list
	var $style_collapse = "true";
	
	
	// class functions
	//----------------------------------------
	
	function AddNodeList($nodes){
	
		$this->tree_source[] .=$nodes;
	}
	
	//----------------------------------------

	function SetDefaults($_atts){
	// import the arguments from shortcode and/or POST/REQUEST

		// get args from URL POST (1st precedent) or shortcode argument	(2nd precedent) or settings (3rd precedent) or factory default (4th precedent)
		// set the default option in case shortcode is not populated
		$defaults = $this->GetDefaults();
		
		$this->manual_source=$_atts["tree_source"];	

		$atts = shortcode_atts( $defaults, $_atts );

		$this->root=$atts["root"];	
		$this->style_template=$atts["style_template"];	
		$this->icon_home=$atts["icon_home"];	
		$this->icon_middle=$atts["icon_middle"];	
		$this->icon_endpoint=$atts["icon_endpoint"];	
		$this->style_collapse=$atts["style_collapse"];	
		$this->source=$atts["source"];	
		$this->endpoint_filter=$atts["endpoint_filter"];	
		$this->tree_filter=$atts["tree_filter"];	
		$this->hidden_filter=$atts["hidden_filter"];	
		$this->endpoint_link=$atts["endpoint_link"];	
		$this->endpoint_image=$atts["endpoint_image"];	
		$this->endpoint_image_size=$atts["endpoint_image_size"];	
		
		// POST / REQUEST takes precendence over shortcode
		if (isset($_REQUEST["root"])){ $this->root=urldecode(sanitize_text_field($_REQUEST["root"]));	}
		if (isset($_REQUEST["style_template"])){ $this->style_template=urldecode(sanitize_text_field($_REQUEST["style_template"]));	}
		if (isset($_REQUEST["icon_home"])){ $this->icon_home=urldecode(sanitize_text_field($_REQUEST["icon_home"]));	}
		if (isset($_REQUEST["icon_middle"])){ $this->icon_middle=urldecode(sanitize_text_field($_REQUEST["icon_middle"]));	}
		if (isset($_REQUEST["icon_endpoint"])){ $this->icon_endpoint=urldecode(sanitize_text_field($_REQUEST["icon_endpoint"]));	}
		if (isset($_REQUEST["style_collapse"])){ $this->style_collapse=urldecode(sanitize_text_field($_REQUEST["style_collapse"]));	}
		if (isset($_REQUEST["source"])){ $this->source=urldecode(sanitize_text_field($_REQUEST["source"]));	}
		if (isset($_REQUEST["endpoint_filter"])){ $this->endpoint_filter=urldecode(sanitize_text_field($_REQUEST["endpoint_filter"]));	}
		if (isset($_REQUEST["tree_filter"])){ $this->tree_filter=urldecode(sanitize_text_field($_REQUEST["tree_filter"]));	}
		if (isset($_REQUEST["hidden_filter"])){ $this->hidden_filter=urldecode(sanitize_text_field($_REQUEST["hidden_filter"]));	}
		if (isset($_REQUEST["endpoint_link"])){ $this->endpoint_link=urldecode(sanitize_text_field($_REQUEST["endpoint_link"]));	}
		if (isset($_REQUEST["endpoint_image"])){ $this->endpoint_image=urldecode(sanitize_text_field($_REQUEST["endpoint_image"]));	}
		if (isset($_REQUEST["endpoint_image_size"])){ $this->endpoint_image_size=urldecode(sanitize_text_field($_REQUEST["endpoint_image_size"]));	}

	}
//------------------------------------------------------------------

	function GetDefaults(){
	// returns the factory default array

		// Factory defaults
		$defaults = array(

		'root' => "\\",
		'style_template' => "default",
		'icon_home' => "",
		'icon_middle' => "",
		'icon_endpoint' => "",
		'style_collapse' => "true",
		'source' => "plugin-folder",
		'endpoint_filter' => "",
		'tree_filter' => "",
		'hidden_filter' => "true",
		'endpoint_link' => "true",
		'endpoint_image' => "true",
		'tree-source' => "",
		'endpoint_image_size' => "30"

		);
		return $defaults;
}

	//----------------------------------------
	
	function SetEndpoint($parent_path, $full_path, $is_endpoint){
	// set return values
	
		$endname = "";
		$full_path1 = $full_path;
		
		if (($is_endpoint) && (preg_match("/(\$)/",$full_path))){
			$full_path1 = substr($full_path, 0, -1);
		}
		
		// set flag if this is an endpoint
		$this->_is_endpoint=$is_endpoint;
		
		// convert path to URL
		$fronte = explode("/wordpress/",site_url())[0];
		$backe = explode("\\wordpress\\",$full_path1)[1];
		$backe = str_replace("\\","/",$backe);
		$endpoint_url= $fronte."/wordpress/".$backe;
		
		// get calling page url
		$current_url =  sanitize_url($_SERVER['HTTP_REFERER']) ;
	
		// remove any previous endpoint values from the URL args
		$current_url = str_replace("&endpoint=","@@ENDPOINT@@",$current_url);
		$current_url = str_replace("?endpoint=","@@ENDPOINT@@",$current_url);
		$clean_url = explode("@@ENDPOINT@@",$current_url);
		$current_url = $clean_url[0];
		$this->calling_url = $current_url;
		
		// add return arg
		$current_url .= strpos($current_url,"?") ? "&" : "?"; 
		$current_url .= "endpoint=";
		
		// get endname
		$endname = str_replace($parent_path,"",$full_path1);

		// create endpoint image
		if ($this->endpoint_image == "true"){
			
			// clear the last output
			$this->_endpoint_img = "";
			
			// list of allowed image types
			$img_list = ".png,.jpg,.jpeg,.gif,.ico";
			//-------------------------------------
			
			$patt = "/(".str_replace(",","$)|(",$img_list)."$)/";			
			if (preg_match($patt, $endname)) {
				$this->_endpoint_img = '<img src="'.$endpoint_url.'" width="'.$this->endpoint_image_size.'" height="'.$this->endpoint_image_size.'">&nbsp;&nbsp;&nbsp;';
			}
		}

		// set values
		$this->endpoint_url = $endpoint_url;		
		$this->return_url = $current_url.urlencode($endname);
		$this->endpoint_name=$endname;
		
		// override endpoint_url with an integration call
		if ($this->$endpoint_integration_call != ""){
			$integration_call = @call_user_func($this->$endpoint_integration_call,$full_path1)."";
			if ($integration_call != ""){ $this->endpoint_url = $integration_call; }
		}
		
		// build endpoint link from specified pattern
		$_endpoint_href = str_replace("[endpoint_name]",urlencode($this->endpoint_name),$this->endpoint_link_pattern);
		$_endpoint_href = str_replace("[endpoint_url]",$this->endpoint_url,$_endpoint_href);
		$_endpoint_href = str_replace("[return_url]",$this->return_url,$_endpoint_href);
		$_endpoint_href = str_replace("[calling_url]",$this->calling_url,$_endpoint_href);
		$this->_endpoint_href= $_endpoint_href;

		return $endname;
	}
	
	//----------------------------------------

	function FilterNode($nodename, $endpoint_flag){
	// apply the filters to the specified node
	
		$match_flag = true;
		
		// filter the entire tree and the endpoint according to the filters
		if ($this->tree_filter != ""){
			$patt = "/(".str_replace(",",")|(",$this->tree_filter).")/";
			if (!preg_match($patt, $nodename)) { $match_flag = false; }
		}
		
		// filter according to the endpoint
		if (($this->endpoint_filter != "")&&($endpoint_flag)){
			$patt = "/(".str_replace(",",")|(",$this->endpoint_filter).")/";
			
			if (!preg_match($patt, $nodename)) { $match_flag = false; }
		}
		
		// ignore hidden files or folders
		if ($this->hidden_filter == "true"){
			if (preg_match("/(^\.)/", $nodename)) { $match_flag = false; }  // ignore hidden files starting with a .
			if (preg_match("/(^\.\.)/", $nodename)) { $match_flag = false; }  // ignore .. endpoints
		}

		return $match_flag;
	}

	//----------------------------------------

	private function _render($endname){
	// render all of the final HTML for the endpoint
	
		$html_out = "";
		
		// include image tile in endpoint
		if (($this->endpoint_image == "true") && ($this->_is_endpoint)){
			$html_out .= $this->_endpoint_img;
		}
		
		// display a link
		if (($this->endpoint_link == "true") && ($this->_is_endpoint)){
			$html_out .= $this->_endpoint_href;
		} else { $html_out .= str_replace("\\","",$endname);} // or output the raw name only
	
		return $html_out;
}
	
	//----------------------------------------

	function BuildTree(){
	// returns HTML and creates HTML_out
	
	// include css style template - sanitise using strip_tags
	$html_css = "<style>".strip_tags(@file_get_contents(plugin_dir_path( __FILE__ )."templates/".$this->style_template.".css"))."</style>";
	$html_out = $html_css;
	
	// get all nested nodes starting from root home path
	$arr_loop = $this->GetNestedNodes($this->root);

	// supports up to 7 levels
	if (!empty($arr_loop)){$html_out .= '<'.$this->style_order.' class="wlfc_tree" id="L1">';}
	foreach ($arr_loop as $arr_it){
		$arr_loop2= $this->GetNestedNodes($arr_it);
		
		// get item name and filter it
		$endname =$this->SetEndpoint($this->root,$arr_it,empty($arr_loop2));
		if ($this->FilterNode($endname,$this->_is_endpoint)){

			$html_out .= '<li><span id="S1">';
			if (!empty($arr_loop2) && (strtolower($this->style_collapse)=="true")) { $html_out .='<details open><summary class="node home">'; }
			$html_out .= $this->GetIcon($this->icon_home)."&nbsp;&nbsp;".str_replace("\\","",$endname);
			if (!empty($arr_loop2) && (strtolower($this->style_collapse)=="true")) { $html_out .= '</summary>';}
			if (!empty($arr_loop2)){$html_out .='<'.$this->style_order.' id="L2">';		}
			foreach ($arr_loop2 as $arr_it1){
				$arr_loop3= $this->GetNestedNodes($arr_it1);
				
				// get item name and filter it
				$endname =$this->SetEndpoint($arr_it,$arr_it1,empty($arr_loop3));
				if ($this->FilterNode($endname,$this->_is_endpoint)){
				
				$html_out .= '<li><span id="S2">';
				if (!empty($arr_loop3) && (strtolower($this->style_collapse)=="true")) { $html_out .= '<details>'; }
				if (empty($arr_loop3)) { $html_out .= '<summary class="node endpoint">'.$this->GetIcon($this->icon_endpoint)."&nbsp;&nbsp;"; } else { $html_out .= '<summary class="node middle">'.$this->GetIcon($this->icon_middle)."&nbsp;&nbsp;"; }
				$html_out .= $this->_render($endname);
				$html_out .= '</summary>';
				
				if (!empty($arr_loop3)){$html_out .= '<'.$this->style_order.' id="L3">';		}	
				foreach ($arr_loop3 as $arr_it2){
					$arr_loop4= $this->GetNestedNodes($arr_it2);

					// get item name and filter it
					$endname =$this->SetEndpoint($arr_it1,$arr_it2,empty($arr_loop4));
					if ($this->FilterNode($endname,$this->_is_endpoint)){
	 
					$html_out .= '<li><span id="S3">';
					if (!empty($arr_loop4) && (strtolower($this->style_collapse)=="true")) { $html_out .='<details>';}
					if (empty($arr_loop4)) { $html_out .= '<summary class="node endpoint">'.$this->GetIcon($this->icon_endpoint)."&nbsp;&nbsp;"; } else { $html_out .= '<summary class="node middle">'.$this->GetIcon($this->icon_middle)."&nbsp;&nbsp;"; }
					$html_out .= $this->_render($endname);
					$html_out .= '</summary>';
					
					if (!empty($arr_loop4)){$html_out .= '<'.$this->style_order.' id="L4">';	}			
					foreach ($arr_loop4 as $arr_it3){
						$arr_loop5= $this->GetNestedNodes($arr_it3);
							
						// get item name and filter it
						$endname =$this->SetEndpoint($arr_it2,$arr_it3,empty($arr_loop5));
						if ($this->FilterNode($endname,$this->_is_endpoint)){
					
						$html_out .= '<li><span id="S4">';
						if (!empty($arr_loop5) && (strtolower($this->style_collapse)=="true")) { $html_out .= '<details>';}
						if (empty($arr_loop5)) { $html_out .= '<summary class="node endpoint">'.$this->GetIcon($this->icon_endpoint)."&nbsp;&nbsp;"; } else { $html_out .= '<summary class="node middle">'.$this->GetIcon($this->icon_middle)."&nbsp;&nbsp;"; }
						$html_out .= $this->_render($endname);
						$html_out .= '</summary>';
						
						if (!empty($arr_loop5)){$html_out .= '<'.$this->style_order.' id="L5">';		}			
						foreach ($arr_loop5 as $arr_it4){
							$arr_loop6= $this->GetNestedNodes($arr_it4);
							
							// get item name and filter it
							$endname =$this->SetEndpoint($arr_it3,$arr_it4,empty($arr_loop6));
							if ($this->FilterNode($endname,$this->_is_endpoint)){
							
							$html_out .= '<li><span id="S5">';
							if (!empty($arr_loop6) && (strtolower($this->style_collapse)=="true")) { $html_out .= '<details>';}
							if (empty($arr_loop6)) { $html_out .= '<summary class="node endpoint">'.$this->GetIcon($this->icon_endpoint)."&nbsp;&nbsp;"; } else { $html_out .= '<summary class="node middle">'.$this->GetIcon($this->icon_middle)."&nbsp;&nbsp;"; }
							
							$html_out .= $this->_render($endname);				
							$html_out .= '</summary>';
							
							if (!empty($arr_loop6)){$html_out .='<'.$this->style_order.' id="L6">';}
							foreach ($arr_loop6 as $arr_it5){
								$arr_loop7= $this->GetNestedNodes($arr_it5);
								
								// get item name and filter it
								$endname =$this->SetEndpoint($arr_it4,$arr_it5,empty($arr_loop7));
								if ($this->FilterNode($endname,$this->_is_endpoint)){
								
								$html_out .= '<li><span id="S6">';
								if (!empty($arr_loop7) && (strtolower($this->style_collapse)=="true")) { $html_out .= '<details>';}
								if (empty($arr_loop7)) { $html_out .= '<summary class="node endpoint">'.$this->GetIcon($this->icon_endpoint)."&nbsp;&nbsp;"; } else { $html_out .= '<summary class="node middle">'.$this->GetIcon($this->icon_middle)."&nbsp;&nbsp;"; }
								
								$html_out .= $this->_render($endname);
								$html_out .= '</summary>';
								
								if (!empty($arr_loop7)){$html_out .= '<'.$this->style_order.' id="L7">';}
								foreach ($arr_loop7 as $arr_it6){
								
									// get item name and filter it
									$endname =$this->SetEndpoint($arr_it5,$arr_it6,true);
									if ($this->FilterNode($endname,$this->_is_endpoint)){
										$html_out .= '<li><span id="S7"><summary class="node endpoint">'.$this->GetIcon($this->icon_endpoint)."&nbsp;&nbsp;";
										$html_out .= $this->_render($endname);
										$html_out .= "</summary></li>";
									}
								}
								if (!empty($arr_loop7)){ $html_out .= '</'.$this->style_order.'>';}
								if (!empty($arr_loop7)&& (strtolower($this->style_collapse)=="true")) {$html_out .= "</details>";}
								$html_out .= "</span></li>";}
							}
							if (!empty($arr_loop6)){ $html_out .= '</'.$this->style_order.'>';}
							if (!empty($arr_loop6)&& (strtolower($this->style_collapse)=="true")) {$html_out .= "</details>";}
							$html_out .= "</span></li>";}
						}
						if (!empty($arr_loop5)){ $html_out .= '</'.$this->style_order.'>';}
						if (!empty($arr_loop5)&& (strtolower($this->style_collapse)=="true")) {$html_out .= "</details>";}
						$html_out .= "</span></li>";}
					}
					if (!empty($arr_loop4)){ $html_out .= '</'.$this->style_order.'>';}
					if (!empty($arr_loop4)&& (strtolower($this->style_collapse)=="true")) {$html_out .= "</details>";}
					$html_out .= "</span></li>";}
				}
				if (!empty($arr_loop3)){ $html_out .= '</'.$this->style_order.'>';}
				if (!empty($arr_loop3)&& (strtolower($this->style_collapse)=="true")) { $html_out .= "</details>";}
				$html_out .= "</span></li>";}
			}
			if (!empty($arr_loop2)){ $html_out .= '</'.$this->style_order.'>';}
			if (!empty($arr_loop2)&& (strtolower($this->style_collapse)=="true")) { $html_out .= "</details>"; }
			$html_out .= "</span></li>";}
	}
	if (!empty($arr_loop)){ $html_out .= '</'.$this->style_order.'>';}
	
	$this->$HTML_out=$html_out;
	return $html_out;
	
	}
	
	//----------------------------------------

	function GetNestedNodes($node){
	
		// a distinct array of all list items that match the $node at the next nest_level
		// e.g. home/path1/path2/path3 
		$array_out = array();

		// get list of all distinct nodes as subnodes of the $node provided
		$nest_level= substr_count($node,"\\");

		// go through full list to get next level
		foreach ($this->tree_source as $trees){
		
			// only look for match at the start of the node string
			$f_1= (substr_count(trim($trees),"\\")>=($nest_level));
			$f_2= (strpos(trim($trees), $node)==0); // note 0 is start of string - false is not present!
			$f_3 = strpos(trim($trees), $node)=="" ? "no" : "yes";
			if ($f_1 && $f_2 ) {
				$new_node_arr = explode("\\",trim($trees));
				$new_node = trim($new_node_arr[$nest_level]);
				if (($new_node <> "") && ($f_3=="yes")){ $array_out[] = $node.$new_node."\\"; }
			}
		
		}
		// ensure only unique elements are included in array
		$array_out = array_unique($array_out);
		return $array_out;	
	}	

//----------------------------------------------------------

function GetIcon($icon, $isize='s'){

	# check if it is an icon image or a special character
	if (preg_match('/^&/', $icon)){
	
		// set font size
		switch ($isize){
			case "xs":
				$htxt = "x-small";
			break;

			case "s":
				$htxt = "small";
			break;
			
			case "m":
				$htxt = "medium";
			break;
			
			case "l":
				$htxt = "large";
			break;

			case "xl":
				$htxt = "x-large";
			break;
			
			case "xxl":
				$htxt = "xx-large";
			break;

			default:   $htxt ="100%";
		}
		
		$ivalue = "<span style='font-size: ".$htxt.";'>".$icon."</span>";
	
	}else{
	# ---------------------------------------------------------------------------
	
	# icon assume png but if it is a gif then the whole filename can be specified
	$ficon = trim($icon);
	if ( !strpos ($icon, ".") ) { $ficon .= ".png"; }
	
	# set size of icon
	$htxt = "";
	switch ($isize){
	
		case "xs":
			$htxt = "style='width:16px;height:16px;'";
		break;

		case "s":
			$htxt = "style='width:32px;height:32px;'";
		break;
		
		case "m":
			$htxt = "style='width:64px;height:64px;'";
		break;
		
		case "l":
			$htxt = "style='width:96px;height:96px;'";
		break;

		case "xl":
			$htxt = "style='width:160px;height:160px;'";
		break;
		
		case "xxl":
			$htxt = "style='width:196px;height:196px;'";
		break;

		default:   # original file size
	}
	
	# get URL location of plugin folder where icon images are stored 
	$fronte = explode("/wordpress/",site_url())[0];
	$backe = explode("/wordpress/",plugin_dir_path( __FILE__ ))[1];
	$ifile = $fronte."/wordpress/".$backe;
	$ifile = str_replace('/includes/','/',$ifile) .'icons/'.$ficon; # still work if this is called from an includes subfolder

	$ivalue = "<img src='$ifile' $htxt>";
	
	}
	if ($icon == ""){ $ivalue = ""; }
	return $ivalue;
}
//----------------------------------------------------------
// IMPORT FUNCTIONS

function ImportSource(){

	$this->slug = plugin_basename( __DIR__ )."\\"; // make root the plugin name
			
	# supported source modes:
	switch ($this->source){
	
		case "uploads-folder":
			$upload = wp_upload_dir();
			$upload_dir = $upload['basedir'];	
			$this->tree_source = $this->ListFolder($upload_dir);	
		break;
		
		case "root-folder":
			$this->tree_source = $this->ListFolder($this->root);
		break;

		case "manual":
			$this->tree_source=explode(",",sanitize_text_field($this->manual_source ));	
		break;

		case "plugin-folder":
		default:
			$this->root = str_replace("/","\\",plugin_dir_path( __FILE__ ));   // convert path from URL/linux path format to windows path format 
			$this->root = str_replace($this->slug,"",$this->root);
			$this->tree_source = $this->ListFolder(plugin_dir_path( __FILE__ ));
	}
}

//------------------------------------------------------------------------------

function ListFolder($path) {
// recursively list folder contents

	$html_out = array();
	$directory_iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path));
	foreach($directory_iterator as $filename => $path_object)
	{
		$html_out[] .= trim(str_replace("/","\\",$filename));   // convert path from URL/linux path format to windows path format
	}
	
	return $html_out;
}

//------------------------------------------------------------------------------


#######################################################################
# END OF CLASS
}


###########################
?>