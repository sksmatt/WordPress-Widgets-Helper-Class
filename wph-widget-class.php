<?php
/**
* WordPress Widgets Helper Class
*
* https://github.com/sksmatt/WordPress-Widgets-Helper-Class
*
* By @sksmatt | www.mattvarone.com
*
* @package		WordPress
* @subpackage	WPH Widget Class
* @author		Matt Varone
* @license 		GPLv2
* @version 		1.4
*/

if ( ! class_exists( 'WPH_Widget' ) ) 
{

	class WPH_Widget extends WP_Widget
	{

		/** 
		* Create Widget 
		* 
		* Creates a new widget and sets it's labels, description, fields and options 
		* 
		* @access	public
		* @param 	array
		* @return 	void
		* @since	1.0
		*/

		function create_widget( $args ) {
			
			// settings some defaults
			$defaults = array( 
				'label'        => '',
				'description'  => '',
				'fields'       => array(),
				'options'      => array(),
				'textdomain'   => '',
			 );
			
			// parse and merge args with defaults
			$args = wp_parse_args( $args, $defaults );
			
			// extract each arg to its own variable
			extract( $args, EXTR_SKIP );			
						
			// set the widget vars
			$this->label   = $label;
			$this->slug    = sanitize_title( $this->label );
			$this->fields  = $fields;
			
			// set textdomain for internationalization 
			( $textdomain ) ? $this->textdomain = $textdomain : $this->textdomain = $this->slug;
			
			// check options
			$this->options = array( 'classname' => $this->slug, 'description' => $description );						
			if ( ! empty( $options ) ) $this->options = array_merge( $this->options, $options );
			
			// call WP_Widget to create the widget
			parent::__construct( $this->slug, $this->label, $this->options );
						
		}

		/** 
		* Form
		* 
		* Creates the settings form. 
		* 
		* @access	private
		* @param 	array
		* @return 	void
		* @since	1.0		
		*/

		function form( $instance ) {
			$this->instance = $instance;
			$form = $this->create_fields();

			echo $form;
		}

		/** 
		* Update Fields
		*  
		* @access	private
		* @param 	array
		* @param	array
		* @return 	array
		* @since	1.0
		*/

		function update( $new_instance, $old_instance ) {
			$instance = $old_instance;

			foreach ( $this->fields as $key ) {
				$slug = $key['id'];
				
				if ( isset( $key['validate'] ) ) {
					if ( false === $this->validate( $key['validate'], $new_instance[$slug] ) )
					return $instance;
				}
				
				if ( isset( $key['filter'] ) )
					$instance[$slug] = $this->filter( $key['filter'], $new_instance[$slug] );
				else
					$instance[$slug] = strip_tags( $new_instance[$slug] );
			}

			return $instance;
		}

		/** 
		* Validate 
		*  
		* @access	private
		* @param	string
		* @param 	string
		* @return 	boolean
		* @since	1.0
		*/
				
		function validate( $rules, $value ) {
			$rules = explode( '|', $rules );
			
			if ( empty( $rules ) || count( $rules ) < 1 )
				return true;
			
			foreach ( $rules as $rule ) {
				if ( false === $this->do_validation( $rule, $value ) )
				return false;
			}
			
			return true;
		}
		
		/** 
		* Filter 
		*  
		* @access	private
		* @param 	string
		* @param	string
		* @return 	void
		* @since	1.0
		*/
				
		function filter( $filters, $value ) {
			$filters = explode( '|', $filters ); 
			
			if ( empty( $filters ) || count( $filters ) < 1 )
				return $value;
			
			foreach ( $filters as $filter ) 
				$value = $this->do_filter( $filter, $value );
			
			return $value;
		}
			
		/** 
		* Do Validation Rule
		*  
		* @access	private
		* @param 	string
		* @param	string
		* @return 	boolean
		* @since	1.0
		*/
		
		function do_validation( $rule, $value = "" )
		{
			switch ( $rule ) {

				case 'alpha':
					return ( ! preg_match( "/^( [a-z] )+$/i", $value ) ) ? false : true;
				break;

				case 'alpha_numeric':
					return ( ! preg_match( "/^( [a-z0-9] )+$/i", $value ) ) ? false : true;
				break;

				case 'alpha_dash':
					return ( ! preg_match( "/^( [-a-z0-9_-] )+$/i", $value ) ) ? false : true;
				break;

				case 'numeric':
					return is_numeric( $value );
				break;
			
				case 'integer':
					return ( bool ) preg_match( '/^[\-+]?[0-9]+$/', $value );
				break;
			
				case 'boolean':
					return is_bool( $value );
				break;
			
				case 'email':
					return is_email( $value );
				break;
			
				case 'decimal':
					return ( bool ) preg_match( '/^[\-+]?[0-9]+\.[0-9]+$/', $value );
				break;
			
				case 'natural':
					return ( bool ) preg_match( '/^[0-9]+$/', $value );
				return;
			
				case 'natural_not_zero':
					if ( ! preg_match( '/^[0-9]+$/', $value ) ) return false;
					if ( $value == 0 ) return false;
					return true;
				return;
			
				default:
					if ( method_exists( $this, $rule ) )
						return $this->$rule( $value );
					else
						return false;
				break;
			
			}
		}		

		/** 
		* Do Filter
		*  
		* @access	private
		* @param 	string
		* @param	string
		* @return 	boolean
		* @since	1.0
		*/
		
		function do_filter( $filter, $value = "" )
		{
			switch ( $filter ) 
			{
				case 'strip_tags':
					return strip_tags( $value );
				break;

				case 'wp_strip_all_tags':
					return wp_strip_all_tags( $value );
				break;

				case 'esc_attr':
					return esc_attr( $value );
				break;

				case 'esc_url':
					return esc_url( $value );
				break;
				
				case 'esc_textarea':
					return esc_textarea( $value );
				break;
				
				default:
					if ( method_exists( $this, $filter ) )
						return $this->$filter( $value );
					else
						return $value;
				break;
			}
		}

		/** 
		* Create Fields 
		* 
		* Creates each field defined. 
		* 
		* @access	private
		* @param 	string
		* @return 	string
		* @since	1.0
		*/

		function create_fields( $out = "" ) {

			$out = $this->before_create_fields( $out );

			if ( ! empty( $this->fields ) ) {
				foreach ( $this->fields as $key ) 
					$out .= $this->create_field( $key );	
			}

			$out = $this->after_create_fields( $out );

			return $out;
		}

		/** 
		* Before Create Fields
		*
		* Allows to modify code before creating the fields.
		*  
		* @access	public
		* @param 	string
		* @return 	string
		* @since	1.0
		*/

		function before_create_fields( $out = "" ) {
			return $out;
		}

		/** 
		* After Create Fields
		* 
		* Allows to modify code after creating the fields.
		*
		* @access	public
		* @param 	string
		* @return 	string
		* @since	1.0
		*/

		function after_create_fields( $out = "" ) {
			return $out;
		}

		/** 
		* Create Fields
		*  
		* @access	private
		* @param 	string
		* @param	string
		* @return 	string
		* @since	1.0
		*/

		function create_field( $key, $out = "" ) {
			// Set fields value
			$slug	= $key['id'];
			$std	= isset( $key['std'] ) ? $key['std'] : "";
			
			if ( isset( $this->instance[$slug] ) )
				$value = empty( $this->instance[$slug] ) ? '' : strip_tags( $this->instance[$slug] );
			else
				$value	= $std;
			
			$id 	= $this->get_field_id( $slug );
			$name 	= $this->get_field_name( $slug );		

			if ( $key['type'] != 'checkbox' )
				$out .= '<p>' . $this->create_label( $key['name'], $id ) . '<br/>';

			switch ( $key['type'] ) {
				// Text Field
				case 'text':

					$out .= '<input type="text" ';

					if ( isset( $key['class'] ) )
					$out .= 'class="'.$key['class'].'" ';

					$out .= 'id="' . $id . '" name="' . $name . '" value="' . esc_attr__( $value ) . '" ';

					if ( isset( $key['size'] ) )
					$out .= 'size="'.$key['size'].'" ';				

					$out .= ' />';

				break;

				// Text Area
				case 'textarea':

					$out .= '<textarea ';

					if ( isset( $key['class'] ) )
					$out .= 'class="'.$key['class'].'" ';

					if ( isset( $key['rows'] ) )
					$out .= 'rows="'.$key['rows'].'" ';

					if ( isset( $key['cols'] ) )
					$out .= 'cols="'.$key['cols'].'" ';

					$out .= 'id="'.$id.'" name="'.$name.'">'.esc_html( $value );

					$out .= '</textarea>';

				break;

				// Checkbox
				case 'checkbox':

					$out .= '<p>';
					
					$out .= $this->create_label( $key['name'],$id );
					
					$out .= ' <input type="checkbox" ';

					if ( isset( $key['class'] ) )
					$out .= 'class="'.$key['class'].'" ';
					
					$val = ( isset( $key['value'] ) ) ? $key['value'] : '' ;

					$out .= 'id="'.$id.'" name="'.$name.'" value="'.$val.'" ';

					if ( esc_attr( $value ) == $key['std'] )
					$out .= ' checked="checked" ';			

					$out .= ' /> ';

				break;

				// Select Box
				case 'select':

					$out .= '<select id="' . $id . '" name="' . $name . '" ';

					if ( isset( $key['class'] ) )
					$out .= 'class="' . $key['class'] . '" ';

					$out .= '> ';

						foreach ( $key['fields'] as $field => $option ) 
						{

							$out .= '<option value="' . esc_attr__( $option['value'] ) . '" ';

							if ( esc_attr( $value )== $option['value'] )
							$out .= ' selected="selected" ';

							$out .= '> '.esc_html( $option['name'] ).'</option>';

						}

					$out .= ' </select> ';

				break;

				// Select Box with Option Groups
				case 'select-group':

					$out .= '<select id="' . $id . '" name="' . $name . '" ';

					if ( isset( $key['class'] ) )
					$out .= 'class="'.$key['class'].'" ';

					$out .= '> ';

						foreach ( $key['fields'] as $group => $fields ) 
						{

							$out .= '<optgroup label="' . $group . '">';

							foreach ( $fields as $field => $option ) 
							{
								$out .= '<option value="' . esc_attr( $option['value'] ) . '" ';

								if ( esc_attr( $value ) == $option['value'] )
								$out .= ' selected="selected" ';

								$out .= '> ' . esc_html( $option['name'] ) . '</option>';
							}

							$out .= '</optgroup>';

						}

					$out .= '</select>';		

				break;

			}

			if ( isset( $key['desc'] ) )
				$out .= '<br/><small class="description">' . __( $key['desc'], $this->textdomain ) . '</small>'; 

			$out .= '</p>';

			return $out;

		}

		/** 
		* Create Label
		*  
		* @access 	private
		* @param 	string
		* @param 	string
		* @return 	string
		* @since	1.0
		*/

		function create_label( $name = "", $id =" " ) {
			return '<label for="' . $id . '">' . __( $name, $this->textdomain ) . ':</label>';
		}		

	} // class
}