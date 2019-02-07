<?php

//Prevent direct access to this file
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

//Enqueue assets
add_action( 'wp_enqueue_scripts', 'ss_enqueue_assets' );
function ss_enqueue_assets() {
	if ( ! is_admin() ) {
		wp_enqueue_script( 'ajax.js', plugin_dir_url( __FILE__ ) . "assets/js/ajax.js", array( 'jquery' ), false, true );
		wp_localize_script( 'ajax.js', 'my_ajax_object', array( 'ajax_url' => admin_url( 'admin-ajax.php' ) ) );
	}
}

//Add custom admin page
add_action( 'admin_menu', 'ss_admin' );

function ss_admin() {
	add_menu_page( 'Testimonials', 'Testimonials', 'edit_posts', 'ss-testimonials', 'ss_admin_callback', 'dashicons-format-chat', 6 );
}

//Callback for admin menu
function ss_admin_callback() {
	$myListTable = new SS_Testimonials_Table();
	?>
    <div class="wrap">
        <h2>Testimonials</h2>
		<?php
		$myListTable->prepare_items();
		$myListTable->display();
		?>
    </div>
	<?php
}

//add action to destroy single testimonial
add_action( 'admin_action_destroy', 'ss_delete_testi' );

function ss_delete_testi() {
	global $wpdb;
	if ( ! ( isset( $_GET['id'] ) || isset( $_POST['id'] ) || ( isset( $_REQUEST['action'] ) && 'destroy' == $_REQUEST['action'] ) ) ) {
		wp_die( 'No testimonial to delete has been supplied!' );
	}
	$id = ( isset( $_GET['id'] ) ? absint( $_GET['id'] ) : absint( $_POST['id'] ) );

	//If `$id` is provided, then delete it
	if ( isset( $id ) && $id != null ) {
		$ssTestimonials = new SS_Testimonials();
		$delete         = $ssTestimonials->delete( $id );
		if ( ! $delete->is_error ) {
			wp_redirect( admin_url( 'admin.php?page=ss-testimonials' ) );
			exit;
		} else {
			wp_die( $delete->message );
		}
	} else {
		wp_die( 'Failed to delete testimonial' );
	}
}


//Check if `WP_List_Table` is exist before requiring wp-list-table class
if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

class SS_Testimonials_Table extends WP_List_Table {

	//Prepare its columns
	function get_columns() {
		$columns = array(
			'cb'    => '<input type = "checkbox" />',
			'name'  => __( 'Name' ),
			'email' => __( 'Email' ),
			'phone' => __( 'Phone' ),
			'text'  => __( 'Testimonial' ),
			'time'  => __( 'Date' ),
		);

		return $columns;
	}

	//Function to prepare the items from database;
	function prepare_items() {
		global $wpdb;
		$ssTestimonials        = new SS_Testimonials();
		$show_testimonials     = $ssTestimonials->display();
		$columns               = $this->get_columns();
		$hidden                = array();
		$sortable              = array();
		$this->_column_headers = array( $columns, $hidden, $sortable );
		$this->items           = $show_testimonials->items;
		$per_page              = 10;
		$current_page          = $this->get_pagenum();
		$total_items           = count( $this->items );

		//Slice items for paging
		$new_data = array_slice( $this->items, ( ( $current_page - 1 ) * $per_page ), $per_page );

		$this->set_pagination_args( array(
			'total_items' => $total_items,
			'per_page'    => $per_page
		) );
		$this->items = $new_data;
	}

	//Assign item value for each column
	function column_default( $item, $column_name ) {
		switch ( $column_name ) {
			case 'name':
				return "<strong>" . $item[ $column_name ] . "</strong>";
			case 'email':
				return "<a href='mailto:" . $item[ $column_name ] . "'>" . $item[ $column_name ] . "</a>";
			case 'phone':
				return "<a href='tel:" . $item[ $column_name ] . "'>" . $item[ $column_name ] . "</a>";
			case 'text':
				return "<p>" . $item[ $column_name ] . "</p>";
			case 'time':
				return $item[ $column_name ];
			default:
				return $item[ $column_name ];
		}
	}

	function column_name( $item ) {
		$actions = array(
			'delete' => sprintf( "<a href = \"?action=%s&id=%s\"> Delete</a > ", 'destroy', $item['id'] ),
		);

		return sprintf( ' %1$s %2$s', $item['name'], $this->row_actions( $actions ) );
	}

	function column_cb( $item ) {
		return sprintf(
			'<input type = "checkbox" name = "id[]" value = "%s" />', $item['id']
		);
	}

	function get_bulk_actions() {
		$actions = array(
			'delete' => 'Delete'
		);

		return $actions;
	}

}

//Register the widget
function ss_load_widgets() {
	register_widget( 'ss_testimonials_widget' );
}

add_action( 'widgets_init', 'ss_load_widgets' );

//Create widget to display random testimonial
class SS_Testimonials_Widget extends WP_Widget {

	public function __construct() {
		$widget_options = array(
			'classname'   => 'ss_testimonials_widget',
			'description' => 'This is a super simple widget to display random testimonials',
		);
		parent::__construct( 'ss_testimonials_widget', 'Random Testimonial', $widget_options );
	}

	public function widget( $args, $instance ) {
		$title = apply_filters( 'widget_title', $instance['title'] );
		echo $args['before_widget'] . $args['before_title'] . $title . $args['after_title'];
		global $wpdb;
		$random_testi = $wpdb->get_results( 'SELECT * from ' . $wpdb->prefix . 'testimonials ORDER BY RAND() LIMIT 1 ', ARRAY_A );
		if ( $random_testi ) {
			echo "<strong>" . $random_testi[0]['name'] . "</strong> said:<br/>";
			echo "<blockquote>" . $random_testi[0]['text'] . "</blockquote>";
			echo "<small>On " . $random_testi[0]['time'] . "</small>";
		}
		echo $args['after_widget'];
	}

	public function form( $instance ) {
		$title = ! empty( $instance['title'] ) ? $instance['title'] : ''; ?>
        <p>
        <label for="<?php echo $this->get_field_id( 'title' ); ?>">Title:</label>
        <input type="text" id="<?php echo $this->get_field_id( 'title' ); ?>"
               name="<?php echo $this->get_field_name( 'title' ); ?>" value="<?php echo esc_attr( $title ); ?>"/>
        </p><?php
	}

	public function update( $new_instance, $old_instance ) {
		$instance          = $old_instance;
		$instance['title'] = strip_tags( $new_instance['title'] );

		return $instance;
	}

}