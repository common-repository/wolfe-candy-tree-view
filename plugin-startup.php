<?php
# Copyright Wolfe Candy Creations 2022

####################################################################################################
# This plugin allows the creation of a templated style tree view with links for many applications  #
####################################################################################################

# create a unique slug for the plugin WOLFE_CANDY_[SLUG]_PLUGIN_VERSION - use this with all functions to ensure unique
define('WOLFE_CANDY_TREE_VIEW_PLUGIN_VERSION', '0.1.0');

require_once 'C-tree.php'; # main class 

//------------------------------------------------------------------

add_shortcode('show-tree', 'WlfC_ShowTree');

//------------------------------------------------------------------

// check if this is to use the shared Wolfe Candy library - ie.e. has the WlfC_ shared library already loaded
if(function_exists("WlfC_GL_functions")) { 

	// only do once so options can be changed in the admin screens
	if (WlfC_GetParam("settings-treeview-firstrun") == ""){

		// list the configurable options settings
		WlfC_SetParam("settings-treeview-title", "Tree View Settings"); 
		WlfC_SetParam("settings-treeview-slug", "wolfe-candy-tree-view"); // use wp slug for links to wp repository
		WlfC_SetParam("settings-treeview-php", "wolfe-candy-tree-view.php"); // use wp slug as php name too (default is plugin.meta.php)
		WlfC_SetParam("settings-treeview-path", plugin_dir_path( __FILE__ )); 
		WlfC_SetParam("settings-treeview-manual", plugin_dir_path( __FILE__ )."treeview-manual.txt"); 
	
		// factory default settings
		WlfC_SetParam("settings-treeview-style_template", "default"); 	// 
		
	}
}
# 	C-form SpConditions:	$DispTxt @@ $defaultval @@ $options @@ $special @@ $fhelp

#	set_config('settings-general', $source='Default', $ctype = '[input type]', $spconditions = '',	$cfield='[fieldname]',$initiate=true, $forder='1');

//------------------------------------------------------------------

function show_notices_for_WOLFE_CANDY_TREE_VIEW() {
    // We attempt to fetch our transient that we stored earlier.
    // If the transient has expired, we'll get back a boolean false
    $message = get_transient( 'WOLFE_CANDY_TREE_VIEW_my_plugin_activation_error_message' );
	delete_transient ('WOLFE_CANDY_TREE_VIEW_my_plugin_activation_error_message');

    if ( ! empty( $message ) ) {
        echo "<div class='notice notice-error is-dismissible'>
            <p>$message</p>
        </div>";
    }
}

add_action( 'admin_notices', 'show_notices_for_WOLFE_CANDY_TREE_VIEW' );

###########################
# shortcode

function WlfC_ShowTree($_atts){

	$stree = new WlfC_Treeview;
	
	// sample code
#	$stree->tree_source = array("\\path\\to\\tree\\","\\path\\5\\three\\y\\u\\6\\7\\8\\9","\\path\\to\\tree\\fell","\\path\\to\\tree\\one","\\path\\three\\tree\\","\\path\\four\\tree\\");
#	$stree->AddNodeList("\\path\\5\\three\\y\\u\\6\\9");
#	$stree->AddNodeList("\\path\\5\\three\\y\\u\\7\\8");

	$stree->SetDefaults($_atts);
	$stree->ImportSource();

	$html_out=$stree->BuildTree();
	
	return $html_out;

}

###########################

?>