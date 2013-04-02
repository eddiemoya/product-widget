<?php
/*
Plugin Name: Product Widget
Description: A widget to display the scrolling lists for products
Version: 1.0
Author: Matthew Day
*/
class Product_Widget extends WP_Widget 
{	
	var $widget_name = 'Product Widget';
	var $id_base = 'product_widget';
	
	public function __construct()
	{
		$widget_ops = array(
			'description' => "Product Widget",
			'classname' => "product-widget"
		);

		parent::WP_Widget($this->id_base, $this->widget_name, $widget_ops);
	}

	public static function register_widget() 
	{
		add_action('widgets_init', create_function('', 'register_widget("' . __CLASS__ . '");'));
	}
    
    public function widget( $args, $instance )
	{
		extract($args);
        extract($instance);
		
		echo $before_widget;
	   
        echo $after_widget;
    }
    
    public function update($new_instance, $old_instance)
	{
		// inherit the existing settings
		$instance = $old_instance;        
		$ids = explode(",", $instance['pw_ids']);

        foreach($ids as $id)
        {
			$id = trim($id);

			if(empty($id))
			{
				continue;
			}        	

        	$new_instance['pw_id_' . $id] = "on";
        }

		foreach($new_instance as $key => $value)
		{
			$instance[$key] = $value;	
        }

        foreach($instance as $key => $value)
		{
			if($value == 'on' && !isset($new_instance[$key]))
			{
				unset($instance[$key]);
			}

			if((strpos($key, 'pw_id_') === 0) && (!empty($new_instance['pw_ids_remove_all'])))
        	{
        		unset($instance[$key]);
        	}
        }

		return $instance;
	}

	public function form($instance)
	{
        // Merge saved input values with default values
        $instance = wp_parse_args((array) $instance, $defaults);
		
		$fields = array(
			array(
				'field_id'		=> "pw_template",
				'type'			=> "select",
				'label'			=> "Template",
				'options'		=> array(
					'products-horizontal'	=> "Horizontal",
					'products-vertical'		=> "Vertical"
				)
			),
			array(
				'field_id'		=> "pw_ids",
				'type'			=> "textarea",
				'label'			=> "IDs (CSV List)",
				'remove_value'	=> TRUE
			)
		);

		$supFields = array();

		foreach($instance as $k => $v)
		{
			if(strpos($k, 'pw_id_') === 0)
			{
				$supFields[] = array(
					'field_id'		=> "$k",
					'type'			=> "checkbox",
					'label'			=> str_replace("pw_id_", "", $k)
				);
			}
		}

		if(!empty($supFields))
		{
			$fields[] = array(
				'field_id'		=> "pw_ids_remove_all",
				'type'			=> "checkbox",
				'label'			=> "Remove All"
			);

			$fields = array_merge($fields, $supFields);
		}

        $this->form_fields($fields, $instance);
	}
    
    private function form_fields($fields, $instance, $group = false){
        
        if($group) {
            echo "<p>";
        }
            
        foreach($fields as $field){
            
            extract($field);
            $label = (!isset($label)) ? null : $label;
            $options = (!isset($options)) ? null : $options;
            $this->form_field($field_id, $type, $label, $instance, $options, $group, $remove_value);
        }
        
        if($group){
             echo "</p>";
        }
    }
    
    private function form_field($field_id, $type, $label, $instance, $options = array(), $group = false, $rv = FALSE){
  
        if(!$group)
             echo "<p>";
            
        $input_value = (isset($instance[$field_id]) && !$rv) ? $instance[$field_id] : '';
        switch ($type){
            
            case 'text': ?>
            
                    <label for="<?php echo $this->get_field_id( $field_id ); ?>"><?php echo $label; ?>: </label>
                    <input type="text" id="<?php echo $this->get_field_id( $field_id ); ?>" class="widefat" style="<?php echo (isset($style)) ? $style : ''; ?>" class="" name="<?php echo $this->get_field_name( $field_id ); ?>" value="<?php echo $input_value; ?>" />
                <?php break;
            
            case 'select': ?>
                    <label for="<?php echo $this->get_field_id( $field_id ); ?>"><?php echo $label; ?>: </label>
                    <select id="<?php echo $this->get_field_id( $field_id ); ?>" class="widefat" name="<?php echo $this->get_field_name($field_id); ?>">
                        <?php
                            foreach ( $options as $value => $label ) :  ?>
                        
                                <option value="<?php echo $value; ?>" <?php selected($value, $input_value) ?>>
                                    <?php echo $label ?>
                                </option><?php
                                
                            endforeach; 
                        ?>
                    </select>
                    
				<?php break;
                
            case 'textarea':
                
                $rows = (isset($options['rows'])) ? $options['rows'] : '16';
                $cols = (isset($options['cols'])) ? $options['cols'] : '20';
                
                ?>
                    <label for="<?php echo $this->get_field_id( $field_id ); ?>"><?php echo $label; ?>: </label>
                    <textarea class="widefat" rows="<?php echo $rows; ?>" cols="<?php echo $cols; ?>" id="<?php echo $this->get_field_id($field_id); ?>" name="<?php echo $this->get_field_name($field_id); ?>"><?php echo $input_value; ?></textarea>
                <?php break;
            
            case 'radio' :
                /**
                 * Need to figure out how to automatically group radio button settings with this structure.
                 */
                ?>
                    
                <?php break;
            

            case 'hidden': ?>
                    <input id="<?php echo $this->get_field_id( $field_id ); ?>" type="hidden" style="<?php echo (isset($style)) ? $style : ''; ?>" class="widefat" name="<?php echo $this->get_field_name( $field_id ); ?>" value="<?php echo $input_value; ?>" />
                <?php break;

            
            case 'checkbox' : ?>
                    <input type="checkbox" class="checkbox" id="<?php echo $this->get_field_id($field_id); ?>" name="<?php echo $this->get_field_name($field_id); ?>"<?php checked( (!empty($instance[$field_id]))); ?> />
                	<label for="<?php echo $this->get_field_id( $field_id ); ?>"><?php echo $label; ?></label>
                <?php
        }
        
        if(!$group)
             echo "</p>";
            
       
    }
}

Product_Widget::register_widget();