<?php
/*
 Plugin Name: WP-Dict
 Plugin URI: http://wordpress.org/extend/plugins/wp-dict/
 Description: Widget Plugin. A widget could help to translate between English and Chinese.Take advantage of the API from dict.cn.
 Version: 1.0.0
 Author: windlx ( Tony Luo )
 Author URI: http://blog.tech4k.com/about
 License: MIT
 */
class WP_Dict_Widget extends WP_Widget {

	function WP_Dict_Widget() {
		parent::WP_Widget(false, $name = 'WP Dict');
		/* Widget settings. */
		$widget_ops = array( 'classname' => 'wp_dict',
	'description' => 'A widget could help to translate.Take advantage of the API from dict.cn.' );

		/* Widget control settings. */
		$control_ops = array( 'width' => 300, 'height' => 350, 'id_base' => 'wp-dict-widget' );

		/* Create the widget. */
		$this->WP_Widget( 'wp-dict-widget', 'WP Dict Widget', $widget_ops, $control_ops );

		// Load jQuery
		wp_enqueue_script('jquery');
	}

	function widget( $args, $instance ) {
		extract( $args );

		// Get the div id of the widget
		$widgetid = $args['widget_id'];

		/* User-selected settings. */
		$title = apply_filters('widget_title', $instance['title'] );

		//an option that allows your users to turn on or off AJAX within the widget
		$useAjax = isset($instance['useAjax']) ? $instance['useAjax'] : false;

		/* Before widget (defined by themes). */
		echo $before_widget;

		/* Title of widget (before and after defined by themes). */
		if ( $title )
		echo $before_title;
		echo $title;
		echo '<input type="text" style="margin-left:10px;width:150px" id="wp_dict_query" name="wp_dict_query" />';
		echo '<input type="button" style="margin-left:5px;width:30px;text-align:middle" id="wp_dict_submit" name="wp_dict_submit" value="Go"/>';
		echo '<div id="wp_dict_result"></div>';
		echo $after_title;
		if ($useAjax) { ?>
<script type="text/javascript">
        jQuery(document).ready(function($){
            $("#wp_dict_submit").click(function(){
            	$.ajax({
                    type : "GET",
                    url : "index.php",
                    data : { mywidget_request      : "wp_dict_request_handler",
                                query : $("#wp_dict_query").val() },
                    success : function(response) {
                        // The server has finished executing PHP and has returned something,
                        // so display it!
                        $("#wp_dict_result").html(response);
                    }
                });
            });
        });
    	</script>
		<?php
		}

		/* After widget (defined by themes). */
		echo $after_widget;
	}
	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;

		/* Strip tags (if needed) and update the widget settings. */
		$instance['title'] = strip_tags( $new_instance['title'] );
		$instance['useAjax'] = (strip_tags($new_instance['useAjax']) == "Yes" ? true : false);

		return $instance;
	}
	function form( $instance ) {

		/* Set up some default widget settings. */
		$defaults = array( 'title' => 'Translate', 'useAjax' => true );
		$instance = wp_parse_args( (array) $instance, $defaults ); ?>

<p style="text-align: right;"><label
	for="<?php echo $this->get_field_id( 'title' ); ?>">Title:</label> <input
	id="<?php echo $this->get_field_id( 'title' ); ?>"
	name="<?php echo $this->get_field_name( 'title' ); ?>"
	value="<?php echo $instance['title']; ?>" style="width: 200px;" /></p>
<p style="text-align: right;"><label
	for="<?php echo $this->get_field_id('useAjax'); ?>">Use Ajax:</label> <select
	id="<?php echo $this->get_field_id('useAjax'); ?>"
	name="<?php echo $this->get_field_name('useAjax'); ?>"
	style="width: 200px;">
	<option
	<?php if ($instance['useAjax'] == true) echo 'selected="selected"'; ?>>Yes</option>
	<option
	<?php if ($instance['useAjax'] == false) echo 'selected="selected"'; ?>>No</option>
</select></p>
	<?php
	}
}
function wp_dict_request_handler() {
	// Check that query have been passed
	if (isset($_GET['query'])) {
		$ch = curl_init();
		if (defined("CURL_CA_BUNDLE_PATH")) curl_setopt($ch, CURLOPT_CAINFO, CURL_CA_BUNDLE_PATH);
		curl_setopt($ch, CURLOPT_URL,'http://dict.cn/ws.php?utf8=true&q='.$_GET['query']);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
		curl_setopt($ch, CURLOPT_TIMEOUT, 30);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		//////////////////////////////////////////////////
		///// Set to 1 to verify Douban's SSL Cert //////
		//////////////////////////////////////////////////
		$response = curl_exec($ch);
		$http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		curl_close ($ch);
		if ($http_status != 200 ) {
			echo "Query Failed.";
		}
		else {
			echo $response;
		}
		exit();
	}
}

function wp_dict_load_widget() {
	register_widget( 'WP_Dict_Widget' );
}
/* Add our function to the widgets_init hook. */
add_action('widgets_init', 'wp_dict_load_widget' );
add_action('init', 'wp_dict_request_handler');
?>