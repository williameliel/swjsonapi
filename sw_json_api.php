<?php
/*
Plugin Name: Sideways JSON Api
Plugin URI: http://sideways-nyc.com
Description: Get JSON of current WP post/page/archive.
Version: 0.1.1
Author: William Garcia
Author URI: http://sideways-nyc.com
License: GPLv2 or later
*/

// don't load directly
if ( !defined( 'ABSPATH' ) )
    die( '-1' );

class SWJsonApi{
    function __construct(){
        global $wp_query;

        // We add the query_var to WP
        add_filter( 'query_vars', array( $this, 'addQueryVar' ) );
        
        // Make redirect to show the JSON spitout
        add_action( 'template_redirect', array( $this,'templateRedirect' ) );

        // Add filterable function TODO
        // add_filter('SWJsonApi/init', array($this, 'init'),10,1);
        // apply_filters('SWJsonApi/init', array($this, 'init') );
        
    }
    /* 
    Function to add Query Var json=1
    */
    public function addQueryVar( $vars ){
        $vars[] = 'json';
        return $vars;
    }
    /* 
    Function to redirecte the template and add JSON headers.
    */
    public function templateRedirect(){
        global $wp_query;
        
        // If this is not a request for json then bail
        if ( !isset( $wp_query->query_vars['json'] ) || '1' !== $wp_query->query_vars['json'] ) {
            return;
        }
        
        // Set the appropriate header
        header( 'Content-Type: application/json; charset=utf-8' );
        
        // Help prevent MIME-type confusion attacks in IE8+
        send_nosniff_header();
        
        // Now go and render JSON 
        
        $this->init();
        
    }
    /*
    Initiate & control returns
    */
    public function init(){

        if(!file_exists( TEMPLATEPATH . '/json-posts.php')){

            // Get loop info and extras.
            $this->getPosts();
            // Render json and leave.
            $this->renderJSON();
        }else{
            
            get_template_part( 'json-posts.php' );
        }
    }
    /* 
      get Post/Posts loop
    */
    public function getPosts(){
        
        global $wp_query, $post;

        $post_data = array();

        if (  $wp_query->have_posts() ) {
            
            $key = 0;
            
            while (  $wp_query->have_posts() ) {
                
                the_post();
                
                $post_id  = get_the_ID();
                
                $this->posts[$key] = get_post();
                
                $this->posts[$key]->thumbnail = $this->getThumbs( $post_id );
                
                $this->posts[$key]->meta = $this->getMeta( $post_id );
               
                $key++;
            }
        }
    }
    /* Get the Thumbnails if existing */
    public function getThumbs( $post_id, $sizes = false )
    {
        
        if ( isset( $sizes ) && $sizes == true && is_array( $sizes ) ) { // You can pass an array containing the sizes that you wish to retrieve. ie: array('thumbnail', 'large');
            $thumb_sizes = $sizes;
        } else {
            
            $thumb_sizes = get_intermediate_image_sizes();
        }
        foreach ( $thumb_sizes as $sizes ) {
            
            $thumbs[$sizes] = wp_get_attachment_image_src( get_post_thumbnail_id( $post_id ), $sizes );
            
        }
        
        return $thumbs;
        
    }
    /* Get the Meta or ACF */
    public function getMeta( $post_id )
    {
        if ( function_exists( 'get_fields' ) ) {
            $fields = get_fields( $post_id );
            if(is_array( $fields )){
                $fields = array_filter( $fields );
            }
            
        } else {
            
            $fields = get_post_meta( $post_id );
        }
        
        return $fields;
    }
    /* 
    Function to render the JSON this should be filterable from your functions file.
    */
    public function renderJSON()
    {
        // Render the Json
        echo json_encode( $this );
        
        //Stop execution
        exit;
    }

}
// Finally initialize code
new SWJsonApi();
