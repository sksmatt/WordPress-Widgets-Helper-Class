<?php
/**
* Plugin Name: Sample Widget
* Plugin URI: http://www.mattvarone.com
* Description: A widget example using WPH_Widget Class.
* Version: 0.1
* Author: Matt Varone
* Author URI: http://twitter.com/sksmatt
* License: GPL
*
* This program is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
*/

// Define path to this plugin
define('WPH_SAMPLE_WIDGET_PATH', plugin_dir_path(__FILE__));

// Require WPH_Widget Class
include_once(WPH_SAMPLE_WIDGET_PATH.'inc/wph-widget-class.php');

// Check if the custom class exists
if (!class_exists('My_Recent_Posts_Widget')) 
{
	// Create custom widget class extending WPH_Widget
	class My_Recent_Posts_Widget extends WPH_Widget
	{
	
		function __construct()
		{
		
			// Configure widget array
			$args = array(
				// Widget Backend label
				'label' => __('My Recent Posts'),
				// Widget Backend Description								
				'description' => __('My Recent Posts Widget Description'),		
			);
		
			// Configure the widget fields
			// Example for: Title (text) and Amount of posts to show ( select box )
		
			// fields array
			$args['fields'] = array(							
			
				// Title field
				array(		
				// field name/label									
				'name' => __('Title'),		
				// field description					
				'desc' => __('Enter the widget title.'),
				// field id		
				'id' => 'title',
				// field type ( text, checkbox, textarea, select, select-group)								
				'type'=>'text',	
				// class, rows, cols								
				'class' => 'widefat',	
				// default value						
				'std' => __( 'Recent Posts')					
				),
			
				// Amount Field
				array(
				'name' => __('Amount'),							
				'desc' => __('Select how many posts to show.'),
				'id' => 'amount',							
				'type'=>'select',				
				// selectbox fields			
				'fields' => array(								
						array( 
							// option name
							'name'  => __('1 Post'),
							// option value			
							'value' => '1' 						
						),
						array( 
							'name'  => __('2 Posts'),			
							'value' => '2' 					
						),
						array( 
							'name'  => __('1 Post'),
							'value' => '3'	
						)
					
						// add more options
				)),
			
				// add more fields
			
			); // fields array

			// create widget
			$this->create_widget( $args );
		}

		function widget($args,$instance)
		{
	
			// And here do whatever you want
	
			$out  = $args['before_title'];
			$out .= $instance['title'];
			$out .= $args['after_title'];
				
			// here you would get the most recent posts based on the selected amount: $instance['amount'] 
			// Then return those posts on the $out variable ready for the output
			
			$out .= '<p>Hey There!</p>';

			echo $out;
		}
	
	} // class

	// Register widget
	if ( !function_exists('my_register_widget') )
	{
		function my_register_widget()
		{
			register_widget('My_Recent_Posts_Widget');
		}
		
		add_action('init', 'my_register_widget', 1);
	}
}