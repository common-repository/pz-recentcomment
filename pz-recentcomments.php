<?php
/*
Plugin Name: Pz-RecentComments
Plugin URI: http://poporon.poponet.jp/pz-linkcard
Description: Recent comments widget.
Version: 1.0.0
Author: poporon
Author URI: http://poporon.poponet.jp
License: GPLv2 or later
*/

class PzRecentCommentsWidget extends WP_Widget {
	function __construct() {
		// load_plugin_textdomain (basename(dirname(__FILE__)) , false, basename(dirname(__FILE__)).'/languages');
		parent::__construct( 'recent-comments', __( 'Pz Recent Comments' ), array('description' => __('Recent comments widget.') ) );
		add_action('wp_enqueue_scripts', array($this, 'enqueue'));
	}

	function enqueue() {
		wp_enqueue_style	('pz-recentcomments', plugin_dir_url (__FILE__).'style.css');
	}
	
	// ウィジェット
	function widget($args, $instance ) {
		if (!isset($args['widget_id'] ) ) {
			$args['widget_id'] = $this->id;
		}
		
		$output		= '';
		$title		= (!empty($instance['title']) ? $instance['title'] : __('Recent Comments' ) );
		$title		= apply_filters( 'widget_title', $title, $instance, $this->id_base );
		
		$number		= (!empty($instance['number']) ? absint($instance['number'] ) : 5 );
		if ( $number < 0 ) {
			$number = 5;
		}
		if ( $number > 100 ) {
			$number = 100;
		}
		
		// $mouseover	= (!empty($instance['mouseover']) ? $instance['mouseover'] : '' );
		$mouseover = (wp_is_mobile() ? 0 : 1 );
		
		$comments = get_comments( apply_filters( 'widget_comments_args',
			array(
				'number'		=> $number,
				'status'		=> 'approve',
				'type'			=> 'comment',
				'post_status'	=> 'publish'
			) ) );
		
		$output		= $args['before_widget'];
		if ( $title ) {
			$output	.= $args['before_title'].$title.$args['after_title'];
		}
		
		$output		.= '<ul class="recentcomments">';
		if ( is_array($comments ) && $comments ) {
			foreach ( $comments as $comment ) {
				$output		.=	'<li class="comment-listitem">';
				
				$output		.=	'<span class="comment-user">';
				if ( !empty($comment->comment_author_url ) ) {
					$output	.=	'<span class="comment-author-link"><a href="'.esc_url( $comment->comment_author_url ).'" target="_blank">';
				}
				$output		.=	'<span class="comment-avatar">'.get_avatar( $comment, 40, null ).'</span>';
				$output		.=	'<span class="comment-author">'.$comment->comment_author.'</span>';
				if ( !empty($comment->comment_author_url ) ) {
					$output	.=	'</a></span>';
				}
				$output		.=	'<span class="comment-date">'.get_comment_date( get_option( 'date_format' ) , $comment->comment_ID).'</span>';
				$output		.=	'</span>';

				$output		.=	'<span class="comment-content-link"><A href="'.get_comment_link( $comment->comment_ID ).'"><span class="comment-content"'.( $mouseover ? ' title="'.esc_html($comment->comment_content).'"' : '' ).'">'.get_comment_excerpt(  $comment->comment_ID ).'</span></a></span>';
				$output		.=	'<span class="comment-title"><A href="'.get_permalink( $comment->comment_post_ID ).'">'.get_the_title( $comment->comment_post_ID ).'</a></span>';
				$output		.=	'</li>';
			}
		}
		$output		.= '</ul>';
		$output		.= $args['after_widget'];
		
		echo	$output;
	}
	
	// 外観設定で保存されたとき
	public function update( $new_instance, $old_instance ) {
		$instance			=	$old_instance;
		$instance['title']	=	sanitize_text_field( $new_instance['title'] );

		$number				=	absint( $new_instance['number'] );
		if ( $number < 0 ) {
			$number			=	0;
		} elseif ( $number > 100 ) {
			$number			=	100;
		}
		$instance['number']		=	$number;
		return	$instance;
	}

	// 外観設定
	public function form( $instance ) {
		$title		= !empty( $instance['title']		) ? $instance['title'] : '';
		$number		= !empty( $instance['number']	) ? absint( $instance['number'] ) : 5;
		$mouseover	= !empty( $instance['mouseover']	) ? 'on' : '';

		echo '<p><label for="'.$this->get_field_id( 'title' ).'">'.__( 'Title:' ).'</label>';
		echo '<input class="widefat" id="'.$this->get_field_id( 'title' ).'" name="'.$this->get_field_name( 'title' ).'" type="text" value="'.esc_attr( $title ).'" /></p>';

		echo '<p><label for="'.$this->get_field_id( 'number' ).'">'.__( 'Number of comments to show:' ).'</label>';
		echo '<input class="tiny-text" id="'.$this->get_field_id( 'number' ).'" name="'.$this->get_field_name( 'number' ).'" type="number" step="1" min="1" value="'.$number.'" size="3" /></p>';

//		echo '<p><label for="'.$this->get_field_id( 'mouseover' ).'">'.__( 'Show content on mouseover:' ).'</label>';
//		echo '<input class="tiny-text" id="'.$this->get_field_id( 'mouseover' ).'" name="'.$this->get_field_name( 'mouseover' ).'" type="number" step="1" min="1" value="'.$mouseover.'" size="3" /></p>';
	}
}
add_action('widgets_init', create_function('', 'return register_widget("PzRecentCommentsWidget");'));