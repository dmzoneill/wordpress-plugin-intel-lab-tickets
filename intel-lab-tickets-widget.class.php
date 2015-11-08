<?php

class Intel_Lab_Tickets_Widget extends WP_Widget 
{
	private $mssql = null;
  
	public function __construct() 
	{		
		$id_base = 'intel-lab-tickets-widget';
		$name = 'Intel Lab Tickets';
		$widget_options = array( 'description' => 'Show lab tickets' );
		$control_options = array();
    
		parent::__construct( $id_base, $name, $widget_options, $control_options );
	}
  
	public function widget( $args, $instance ) 
	{ 
		$widgetHeader = "";
		
		if ( ! empty( $instance['title'] ) ) 
		{
			$widgetHeader = $args['before_title'] . apply_filters( 'widget_title', $instance['title'] ). $args['after_title'];
		} 
    
    ob_start();
    include( "ticker.php" );
    $template = ob_get_clean();    
    $output = preg_replace( "/" . preg_quote( "[widget_header]" ) . "/", $widgetHeader, $template );
    
    echo $output;    
	}
    
	public function update( $new_instance, $old_instance ) 
	{
		$instance = array();
		$instance['title'] = ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';

		return $instance;
	}
  
	public function form( $instance ) 
	{
		$title = ! empty( $instance['title'] ) ? $instance['title'] : 'New title';
		
		$template = file_get_contents( realpath( dirname( __FILE__ ) ) . "/templates/widget_form.tpl" );
		$output = preg_replace( "/" . preg_quote( "[for_title]" ) . "/", $this->get_field_id( 'title' ), $template );
		$output = preg_replace( "/" . preg_quote( "[e_title]" ) . "/", _e( 'Title:' ), $output );
		$output = preg_replace( "/" . preg_quote( "[field_id]" ) . "/", $this->get_field_id( 'title' ), $output );
		$output = preg_replace( "/" . preg_quote( "[field_name]" ) . "/", $this->get_field_name( 'title' ), $output );
		$output = preg_replace( "/" . preg_quote( "[field_value]" ) . "/", esc_attr( $title ), $output );
		
		echo $output;		
	}	
}