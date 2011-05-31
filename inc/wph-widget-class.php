<?php
/**
* WordPress Widgets Helper Class
*
* https://github.com/sksmatt/WordPress-Widgets-Helper-Class
*
* By @sksmatt | www.mattvarone.com
*
* @package WordPress Widgets Helper Class
* @author Matt Varone
* @license GPL
*/

if (!class_exists('WPH_Widget')) 
{

	class WPH_Widget extends WP_Widget
	{

		/** 
		* Create Widget 
		* 
		* Creates a new widget and sets it's labels, description, fields and options 
		* 
		* @package WPH Widget Class
		* @since 1.0
		* 
		* @param array $args
		*/

		function create_widget( $args )
		{
			// settings some defaults
			$defaults = array(
				'label' => __('My Widget'),
				'description' => __('My custom widget'),
				'fields' => array(),
				'options' => array(),
				'textdomain' => '',
			);
			
			// parse and merge args with defaults
			$args = wp_parse_args( $args, $defaults );
			
			// extract each arg to its own variable
			extract( $args, EXTR_SKIP );
			
			// no fields? then theres not much to do
			if (empty($fields)) return;
						
			// set the widget vars
			$this->label   = $label;
			$this->slug    = sanitize_title($this->label);
			$this->fields  = $fields;
			
			// set textdomain for internationalization 
			( $textdomain ) ? $this->textdomain = $textdomain : $this->textdomain = $this->slug;
			
			// check options
			$this->options = array('classname' => $this->slug, 'description' => $description);						
			if (!empty($options)) $this->options = array_merge($this->options,$options);
			
			// call WP_Widget to create the widget
			parent::__construct($this->slug, $this->label, $this->options);
						
		}

		/** 
		* Form
		* 
		* Creates the settings form. 
		* 
		* @package WPH Widget Class
		* @since 1.0
		*/

		function form($instance) 
		{
			$this->instance = $instance;
			$form = $this->create_fields();

			echo $form;
		}

		/** 
		* Update Fields
		*  
		* @package WPH Widget Class
		* @since 1.0
		*/

		function update($new_instance, $old_instance) 
		{
			$instance = $old_instance;

			foreach ($this->fields as $key)
			{
				$slug = $key['id']; 
				$instance[$slug] = strip_tags($new_instance[$slug]);
			}

			return $instance;
		}

		/** 
		* Create Fields 
		* 
		* Creates each field defined. 
		* 
		* @package WPH Widget Class
		* @since 1.0
		* 
		* @param string $out
		*/

		function create_fields($out = "")
		{

			$out = $this->before_create_fields($out);

			foreach ($this->fields as $key) 
				$out .= $this->create_field($key);

			$out = $this->after_create_fields($out);

			return $out;
		}

		/** 
		* Before Create Fields
		*
		* Allows to modify code before creating the fields.
		*  
		* @package WPH Widget Class
		* @since 1.0
		*/

		function before_create_fields($out = "")
		{
			return $out;
		}

		/** 
		* After Create Fields
		* 
		* Allows to modify code after creating the fields.
		* 
		* @package WPH Widget Class
		* @since 1.0
		*/

		function after_create_fields($out = "")
		{
			return $out;
		}

		/** 
		* Create Fields
		*  
		* @package WPH Widget Class
		* @since 1.0
		*/

		function create_field($key,$out = "")
		{
			// Set fields value
			$slug	= $key['id'];
			$std	= isset($key['std']) ? $key['std'] : "";
			$value	= empty($this->instance[$slug]) ? $std : strip_tags($this->instance[$slug]);
			$id 	= $this->get_field_id($slug);
			$name 	= $this->get_field_name($slug);		

			if ($key['type'] != 'checkbox')
				$out .= '<p>'.$this->create_label($key['name'],$id).'<br/>';

			switch ($key['type']) 
			{
				// Text Field
				case 'text':

					$out .= '<input type="text" ';

					if ( isset($key['class']))
					$out .= 'class="'.$key['class'].'" ';

					$out .= 'id="'.$id.'" name="'.$name.'" value="'.esc_attr__($value).'" ';

					if ( isset($key['size']))
					$out .= 'size="'.$key['size'].'" ';				

					$out .= ' />';

				break;

				// Text Area
				case 'textarea':

					$out .= '<textarea ';

					if ( isset($key['class']))
					$out .= 'class="'.$key['class'].'" ';

					if ( isset($key['rows']))
					$out .= 'rows="'.$key['rows'].'" ';

					if ( isset($key['cols']))
					$out .= 'cols="'.$key['cols'].'" ';

					$out .= 'id="'.$id.'" name="'.$name.'">'.esc_html($value).'" ';

					$out .= '</textarea>';

				break;

				// Checkbox
				case 'checkbox':

					$out .= '<p>';

					$out .= '<input type="checkbox" ';

					if ( isset($key['class']))
					$out .= 'class="'.$key['class'].'" ';

					$out .= 'id="'.$id.'" name="'.$name.'" value="'.$std.'" ';

					if ( esc_attr($value) == $key['std'] )
					$out .= ' checked="checked" ';			

					$out .= ' /> ';

					$out .= $this->create_label($key['name'],$id);

				break;

				// Select Box
				case 'select':

					$out .= '<select id="'.$id.'" name="'.$name.'" ';

					if ( isset($key['class']))
					$out .= 'class="'.$key['class'].'" ';

					$out .= '> ';

						foreach ($key['fields'] as $field => $option) 
						{

							$out .= '<option value="'.esc_attr($option['value']).'" ';

							if ( esc_attr($value)== $option['value'] )
							$out .= ' selected="selected" ';

							$out .= '> '.esc_html( $option['name'] ).'</option>';

						}

					$out .= ' </select> ';

				break;

				// Select Box with Option Groups
				case 'select-group':

					$out .= '<select id="'.$id.'" name="'.$name.'" ';

					if ( isset($key['class']))
					$out .= 'class="'.$key['class'].'" ';

					$out .= '> ';

						foreach ($key['fields'] as $group => $fields) 
						{

							$out .='<optgroup label="'.$group.'">';

							foreach ($fields as $field => $option) 
							{
								$out .= '<option value="'.esc_attr($option['value']).'" ';

								if ( esc_attr($value)== $option['value'] )
								$out .= ' selected="selected" ';

								$out .= '> '.esc_html( $option['name'] ).'</option>';
							}

							$out .= '</optgroup>';

						}

					$out .= '</select>';		

				break;

			}

			if (isset($key['desc']))
			$out .= '<br/><small class="description">'._e( $key['desc'], $this->textdomain ).'</small>'; 

			$out .= '</p>';

			return $out;

		}

		/** 
		* Create Label
		*  
		* @package WPH Widget Class
		* @since 1.0
		*/

		function create_label($name="",$id="")
		{
			return '<label for="'.$id.'">'._e( $name, $this->textdomain ).':</label>';
		}		

	} // class
}