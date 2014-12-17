<?php
/*
Plugin Name: Sideways JSON Api
Plugin URI: http://sideways-nyc.com
Description: Get JSON of current WP post/page/archive.
Version: 0.1.1
Author: WPBakery
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


         add_filter('SWJsonApi/foobar', array($this, 'foobar'), 1, 1);
    
        
    }
    function foobar(){
      echo 'here';
      return 'herro';
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
        // Get loop info and extras.
        $this->getPosts();
        // Render json and leave.
        $this->renderJSON();
    }
    /* 
      get Post/Posts loop
    */
    public function getPosts()
    {
        $post_data = array();
        if ( have_posts() ) {
            $key = 0;
            while ( have_posts() ) {
                the_post();
                $post_id                      = get_the_ID();
                $this->posts[$key]            = get_post();
                $this->posts[$key]->thumbnail = $this->getThumbs( $post_id );
                $this->posts[$key]->meta      = $this->getMeta( $post_id );
               // $this->posts[$key]->foo = $this->foobar();
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
            $fields = array_filter( $fields );
            
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