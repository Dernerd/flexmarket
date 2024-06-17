<?php
/**
 * The class to register, update and display blocks
 *
 * It provides an easy API for people to add their own blocks
 * to the Aqua Page Builder
 *
 * @package Aqua Page Builder
 */

$aq_registered_blocks = array();

if(!class_exists('AQ_Block')) {
	class AQ_Block {
	 	
	 	//some vars
	 	var $id_base;
	 	var $block_options;
	 	var $instance;
	 	
	 	/* PHP4 constructor */
	 	function AQ_Block($id_base = false, $block_options = array()) {
	 		AQ_Block::__construct($id_base, $block_options);
	 	}
	 	
	 	/* PHP8 constructor */
	 	function __construct($id_base = false, $block_options = array()) {
	 		$this->id_base = isset($id_base) ? strtolower($id_base) : strtolower(get_class($this));
	 		$this->name = isset($block_options['name']) ? $block_options['name'] : ucwords(preg_replace("/[^A-Za-z0-9 ]/", '', $this->id_base));
	 		$this->block_options = $this->parse_block($block_options);
	 	}
	 	
	 	/**
	 	 * Block - display the block on front end
	 	 *
	 	 * Sub-class MUST override this or it will output an error
	 	 * with the class name for reference
	 	 */
	 	function block($instance) {
	 		extract($instance);
	 		echo __('function AQ_Block::block should not be accessed directly. Output generated by the ', 'framework') . strtoupper($id_base). ' Class';
	 	}
	 	
	 	/**
	 	 * The callback function to be called on blocks saving
	 	 * 
	 	 * You should use this to do any filtering, sanitation etc. The default
	 	 * filtering is sufficient for most cases, but nowhere near perfect!
	 	 */
	 	function update($new_instance, $old_instance) {
	 		$new_instance = array_map('htmlspecialchars', array_map('stripslashes', $new_instance));
	 		return $new_instance;
	 	}
	 	
	 	/** 
	 	 * The block settings form 
	 	 *
	 	 * Use subclasses to override this function and generate
	 	 * its own block forms
	 	 */
	 	function form($instance) {
	 		echo '<p class="no-options-block">' . __('There are no options for this block.', 'framework') . '</p>';
	 		return 'noform';
	 	}
	 	
	 	/** 
	 	 * Form callback function 
	 	 *
	 	 * Sets up some default values and construct the basic
	 	 * structure of the form. Unless you know exactly what you're
	 	 * doing, DO NOT override this function
	 	 */
	 	function form_callback($instance = array()) {
	 		//insert block options into instance
	 		$instance = is_array($instance) ? wp_parse_args($instance, $this->block_options) : $this->block_options;
	 		
	 		//insert the dynamic block_id
	 		$this->block_id = 'aq_block_' . $instance['number'];
	 		$instance['block_id'] = $this->block_id;
	 		
	 		//display the block
	 		$this->before_form($instance);
	 		$this->form($instance);
	 		$this->after_form($instance);
	 	}
	 	
	 	/**
	 	 * Block callback function
	 	 *
	 	 * Sets up some default values. Unless you know exactly what you're
	 	 * doing, DO NOT override this function
	 	 */
	 	function block_callback($instance) {
	 		//insert block options into instance
	 		$instance = is_array($instance) ? wp_parse_args($instance, $this->block_options) : $this->block_options;
	 		
	 		//insert the dynamic block_id
	 		$this->block_id = 'aq_block_' . $instance['number'];
	 		$instance['block_id'] = $this->block_id;
	 		
	 		//display the block
	 		$this->before_block($instance);
	 		$this->block($instance);
	 		$this->after_block($instance);
	 	}
	 	
	 	/* assign default block options if not yet set */
	 	function parse_block($block_options) {
	 		$defaults = array(
	 			'id_base' => $this->id_base,	//the classname
	 			'order' => 0, 					//block order
	 			'name' => $this->name,			//block name
	 			'size' => 'span12',				//default size
	 			'title' => '',					//title field
	 			'parent' => 0,					//block parent (for blocks inside columns)
	 			'number' => '__i__',			//block consecutive numbering
	 			'first' => false,				//column first
	 			'resizable' => 1,				//whether block is resizable/not
	 		);
	 		
	 		$block_options = is_array($block_options) ? wp_parse_args($block_options, $defaults) : $defaults;
	 		
	 		return $block_options;
	 	}
	 	
	 	
	 	//form header
	 	function before_form($instance) {
	 		extract($instance);
	 		
	 		$title = $title ? '<span class="in-block-title"> : '.$title.'</span>' : '';
	 		$resizable = $resizable ? '' : 'not-resizable';
	 		
	 		echo '<li id="template-block-'.$number.'" class="block block-'.$id_base.' '. $size .' '.$resizable.'">',
	 				'<dl class="block-bar">',
	 					'<dt class="block-handle">',
	 						'<div class="block-title">',
	 							$name , $title, 
	 						'</div>',
	 						'<span class="block-controls">',
	 							'<a class="block-edit" id="edit-'.$number.'" title="Edit Block" href="#block-settings-'.$number.'">Edit Block</a>',
	 						'</span>',
	 					'</dt>',
	 				'</dl>',
	 				'<div class="block-settings cf" id="block-settings-'.$number.'">';
	 	}
	 	
	 	//form footer
	 	function after_form($instance) {
	 		extract($instance);
	 		
	 		$block_saving_id = 'aq_blocks[aq_block_'.$number.']';
	 			
	 			echo '<div class="block-control-actions cf"><a href="#" class="delete">Delete</a> | <a href="#" class="close">Close</a></div>';
	 			echo '<input type="hidden" class="id_base" name="'.$this->get_field_name('id_base').'" value="'.$id_base.'" />';
	 			echo '<input type="hidden" class="name" name="'.$this->get_field_name('name').'" value="'.$name.'" />';
	 			echo '<input type="hidden" class="order" name="'.$this->get_field_name('order').'" value="'.$order.'" />';
	 			echo '<input type="hidden" class="size" name="'.$this->get_field_name('size').'" value="'.$size.'" />';
	 			echo '<input type="hidden" class="parent" name="'.$this->get_field_name('parent').'" value="'.$parent.'" />';
	 			echo '<input type="hidden" class="number" name="'.$this->get_field_name('number').'" value="'.$number.'" />';
	 		echo '</div>',
	 			'</li>';
	 	}
	 	
	 	/* block header */
	 	function before_block($instance) {
	 		extract($instance);
	 		$column_class = $first ? 'aq-first' : '';
	 		
	 		echo '<div id="aq-block-'.$number.'" class="aq-block aq-block-'.$id_base.' '.$size.' '.$column_class.' cf">';
	 	}
	 	
	 	/* block footer */
	 	function after_block($instance) {
	 		extract($instance);
	 		echo '</div><div class="clear visible-phone"></div>';	
	 	}
	 	
	 	function get_field_id($field) {
	 		$field_id = isset($this->block_id) ? $this->block_id . '_' . $field : '';
	 		return $field_id;
	 	}
	 	
	 	function get_field_name($field) {
	 		$field_name = isset($this->block_id) ? 'aq_blocks[' . $this->block_id. '][' . $field . ']': '';
	 		return $field_name;
	 	}
	 	
	}
}