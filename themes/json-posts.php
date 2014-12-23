<?php
/* 
Auxiliary template file you can drop in your theme file to hijack the JSON spit-out.
You can modify and extend in conjunction with the plugin to format your JSON as needed.
*/
global $wp_query, $post;
$post_data = array();

if ($wp_query->have_posts()){
	$key = 0;
	while ($wp_query->have_posts()){
		the_post();
		$post_id = get_the_ID();
		$post_data[$key] = get_post();
		$key++;
		
	}
}

echo json_encode($post_data);