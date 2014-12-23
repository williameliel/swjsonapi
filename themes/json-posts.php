<?php
//Auxiliary template file you can drop in your theme file to hijack the JSON spit-out.
global $wp_query, $post;
$post_data = array();

if ($wp_query->have_posts())
	{
	$key = 0;
	while ($wp_query->have_posts())
		{
		the_post();
		$post_id = get_the_ID();
		$post_data[$key] = get_post();
		$key++;
		}
	}

echo json_encode($post_data);
?>