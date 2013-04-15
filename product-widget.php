<?php
/*
Plugin Name: Product Widget
Description: A widget to display the scrolling lists for products
Version: 1.0
Author: Matthew Day
*/
class Product_Widget extends WP_Widget 
{	
	public static $PAGINATION_NUMBER = 10;
	
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
    
    public function widget($args, $instance)
	{
		extract($args);
        extract($instance);

        $parts = array();
        $prods = array();

		$model = new Products_Model($parts);
		$nf = (!empty($model->not_found)) ? $model->not_found : array();

		foreach($instance as $k => $v)
		{
			if(strpos($k, 'pw_id_') === 0)
			{
				$part = str_replace('pw_id_', "", $k);

				if(in_array($part, $nf))
				{
					continue;
				}

				$parts[] = $part;
				$prods[] = $v;
			}
		}

		$template = locate_template(array("widgets/product-widget/slider.php"));
		$data = $model->get_by_id($prods);
	   
		echo $before_widget;

		include($template);

        echo $after_widget;
    }
    
    public function update($new_instance, $old_instance)
	{
		// inherit the existing settings
		$instance = $old_instance;        
		$ids = explode(",", $instance['pw_ids']);

		for($i=0; $i<count($ids); $i++)
		{
			$ids[$i] = trim($ids[$i]);
		}

		$model = new Products_Model($ids);
		$prods = $model->products;
		$nf = $model->not_found;

		foreach($prods as $p)
		{
			if(empty($p))
			{
				continue;
			}

			$new_instance['pw_id_' . $p->meta->partnumber] = $p->ID;
			$new_instance['check_pw_id_' . $p->meta->partnumber] = "on";
		}

		if(!empty($nf))
		{
			$new_instance['not_found'] = implode(",", $nf);
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

				if(strpos($key, 'check_pw_id_') === 0)
				{
					unset($instance[str_replace("check_", "", $key)]);
				}
			}

			if((strpos($key, 'check_pw_id_') === 0) && (!empty($new_instance['pw_ids_remove_all'])))
        	{
        		unset($instance[str_replace("check_", "", $key)]);
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
				'field_id'		=> "pw_title",
				'type'			=> "text",
				'label'			=> "Title"
			),
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
		$sf = NULL;
	
		$afield = array();
		$pn = 0;

		foreach($instance as $k => $v)
		{
			if(is_null($sf))
			{
				$sf = array();
			}
			
			if(strpos($k, 'check_pw_id_') === 0)
			{
				$sf[] = array(
					'field_id'		=> "$k",
					'type'			=> "checkbox",
					'label'			=> str_replace("check_pw_id_", "", $k)
				);
				
				$pn++;
	
				if($pn >= self::$PAGINATION_NUMBER)
				{
					$supFields[] = $sf;
					$sf = NULL;
					$pn = 0;
				}
			}
		}

		if(!empty($sf))
		{
			$supFields[] = $sf;
		}

		if(!empty($supFields))
		{						
			$afield[] = array(
				'field_id'		=> "pw_ids_remove_all",
				'type'			=> "checkbox",
				'label'			=> "Remove All"
			);
		}

		if(!empty($instance['not_found']))
		{
			echo "<h4>The following part numbers were not found:</h4>";
			echo "<div>" . $instance['not_found'] . "</div>";
		}

        $this->form_fields($fields, $instance);

		if(count($supFields) > 1)
		{
			echo '<div class="checkBoxPaginateNav">';
			echo '<p><input type="button" Value="Previous" onclick="prevCheckPage(this);" /> | <input type="button" Value="Next" onclick="nextCheckPage(this);" /></p>';
			echo '<p><span class="pageNumberContainer">1</span> of ' . count($supFields) . '</p>';
			echo '</div>';
		}
		
		if(!empty($afield))
		{
			$this->form_fields($afield, $instance);
		}

		for($i=0; $i<count($supFields); $i++)
		{
			$disp = ($i == 0) ? "" : " checkBoxInvisible";
			echo "<div class='productWidgetCheckArea$disp'>";
			$this->form_fields($supFields[$i], $instance);
			echo "</div>";
		}
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

function enqueue_product_widget($hook)
{
    if('post.php' != $hook )
    {
        return;
    }

    wp_enqueue_script('product_widget_files_js', plugins_url('/assets/product_widget.js', __FILE__));
    wp_enqueue_script('product_widget_files_css', plugins_url('/assets/product_widget.css', __FILE__));
}

add_action('admin_enqueue_scripts', 'enqueue_product_widget');