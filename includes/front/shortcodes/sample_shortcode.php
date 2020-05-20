<?php
/*
Plugin Name: Test List Table Example
*/
/*
 *  accrding to this link
 * https://wpengineer.com/2426/wp_list_table-a-step-by-step-guide/
 * https://krumch.com/2018/04/20/frontend-pagination-with-wp-list-table/
 */
if( ! class_exists( 'WP_List_Table' ) ) {
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

class My_Example_List_Table extends WP_List_Table {
	var $data = array();
	function __construct(){
		global $status, $page;

		$this->data = get_data_from_priority();
		parent::__construct( array(
			'singular'  => __( 'book', 'mylisttable' ),     //singular name of the listed records
			'plural'    => __( 'books', 'mylisttable' ),   //plural name of the listed records
			'ajax'      => false        //does this table support ajax?

		) );


		//add_action( 'admin_head', array( &$this, 'admin_header' ) );

	}

	/*function admin_header() {
		$page = ( isset($_GET['page'] ) ) ? esc_attr( $_GET['page'] ) : false;
		if( 'my_list_test' != $page )
			return;
		echo '<style type="text/css">';
		echo '.wp-list-table .column-id { width: 5%; }';
		echo '.wp-list-table .column-booktitle { width: 40%; }';
		echo '.wp-list-table .column-author { width: 35%; }';
		echo '.wp-list-table .column-isbn { width: 20%;}';
		echo '</style>';
	}*/

	function no_items() {
		_e( 'No books found, dude.' );
	}

	function column_default( $item, $column_name ) {
		switch( $column_name ) {
			case 'PARTNAME':
			case 'PARTDES':
			case 'FAMILYDES':
				return $item[ $column_name ];
			default:
				return print_r( $item, true ) ; //Show the whole array for troubleshooting purposes
		}
	}

	function get_sortable_columns() {
		$sortable_columns = array(
			'PARTNAME'  => array('PARTNAME',false),
			'PARTDES' => array('PARTDES',false),
			'FAMILYDES'   => array('FAMILYDES',false)
		);
		return $sortable_columns;
	}

	function get_columns(){
		$columns = array(
			'cb'        => '<input type="checkbox" />',
			'PARTNAME' => __( 'Part Name', 'mylisttable' ),
			'PARTDES'    => __( 'Description', 'mylisttable' ),
			'FAMILYDES'      => __( 'Family', 'mylisttable' )
		);
		return $columns;
	}

	function usort_reorder( $a, $b ) {
		// If no sort, default to title
		$orderby = ( ! empty( $_GET['orderby'] ) ) ? $_GET['orderby'] : 'PARTNAME';
		// If no order, default to asc
		$order = ( ! empty($_GET['order'] ) ) ? $_GET['order'] : 'asc';
		// Determine sort order
		$result = strcmp( $a[$orderby], $b[$orderby] );
		// Send final sort direction to usort
		return ( $order === 'asc' ) ? $result : -$result;
	}

	/*function column_booktitle($item){
		$actions = array(
		//	'edit'      => sprintf('<a href="?page=%s&action=%s&book=%s">Edit</a>',$_REQUEST['page'],'edit',$item['ID']),
		//	'delete'    => sprintf('<a href="?page=%s&action=%s&book=%s">Delete</a>',$_REQUEST['page'],'delete',$item['ID']),
		);

		return sprintf('%1$s %2$s', $item['booktitle'], $this->row_actions($actions) );
	}*/

	function get_bulk_actions() {
		$actions = array(
			//		'delete'    => 'Delete'
		);
		return $actions;
	}

	function column_cb($item) {
		return sprintf(
			'<input type="checkbox" name="book[]" value="%s" />', $item['PARTNAME']
		);
	}

	function prepare_items() {

		$columns  = $this->get_columns();
		$hidden   = array();
		$sortable = $this->get_sortable_columns();
		$this->_column_headers = array( $columns, $hidden, $sortable );
		usort( $this->data, array( &$this, 'usort_reorder' ) );

		$per_page = 5;
		$current_page = $this->get_pagenum();
		$total_items = count( $this->data );

		// only ncessary because we have sample data
		//$this->found_data = array_slice( $this->data,( ( $current_page-1 )* $per_page ), $per_page );

		$this->set_pagination_args( array(
			'total_items' => $total_items,                  //WE have to calculate the total number of items
			'per_page'    => $per_page                     //WE have to determine how many items to show on a page
		) );

		// $paged = isset($_REQUEST['paged']) ? max(0, intval($_REQUEST['paged'] -1) * $per_page) : 0;
		$this->items = array_slice( $this->data,( ( $current_page-1 )* $per_page ),$per_page);

	}

} //class



function my_add_menu_items(){
	$hook = add_menu_page( 'My Plugin List Table', 'My List Table Example', 'activate_plugins', 'my_list_test', 'my_render_list_page' );
	add_action( "load-$hook", 'add_options' );
}

function add_options() {
	global $myListTable;
	$option = 'per_page';
	$args = array(
		'label' => 'Books',
		'default' => 10,
		'option' => 'books_per_page'
	);
	add_screen_option( $option, $args );
	//$myListTable = new My_Example_List_Table();
}
add_action( 'admin_menu', 'my_add_menu_items' );



function my_render_list_page(){

	if(!isset($_REQUEST['paged'])) {
		$_REQUEST['paged'] = explode('/page/', $_SERVER['REQUEST_URI'], 2);
		if(isset($_REQUEST['paged'][1])) list($_REQUEST['paged'],) = explode('/', $_REQUEST['paged'][1], 2);
		if(isset($_REQUEST['paged']) and $_REQUEST['paged'] != '') {
			$_REQUEST['paged'] = intval($_REQUEST['paged']);
			if($_REQUEST['paged'] < 2) $_REQUEST['paged'] = '';
		} else {
			$_REQUEST['paged'] = '';
		}
	}

	global $myListTable;
	$myListTable = new My_Example_List_Table();
	//ob_start();
	echo '</pre><div class="wrap"><h2>My List Table Sample</h2>';
	echo '</pre><div class="wrap"><h4>Copy this class and shortcode, change the API url and class columns to get more data from Priority</h4>';
	$myListTable->prepare_items();
	?>
	<form method="post">
		<input type="hidden" name="page" value="test_list_table">
	<?php
//	$myListTable->search_box( 'search', 'search_id' );

	$myListTable->display();
	echo '</form></div>';
	/*$content = ob_get_contents();
	ob_end_clean();
	return $content;*/
}

add_action ('init', function(){

	// If we're not in back-end we didn't expect to load these things

	if( ! is_admin() ){

		require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
		require_once( ABSPATH . 'wp-admin/includes/screen.php' );
		require_once( ABSPATH . 'wp-admin/includes/class-wp-screen.php' );
		require_once( ABSPATH . 'wp-admin/includes/template.php' );

		global $hook_suffix;
		$hook_suffix = '';
		if(isset($page_hook)) {
			$hook_suffix = $page_hook;
		} else if(isset($plugin_page)) {
			$hook_suffix = $plugin_page;
		} else if(isset($pagenow)) {
			$hook_suffix = $pagenow;
		}
		require_once(ABSPATH.'wp-admin/includes/screen.php' );
		require_once(ABSPATH.'wp-admin/includes/class-wp-screen.php' );
		require_once(ABSPATH.'wp-admin/includes/template.php' );
	}
});



add_shortcode('grid','my_render_list_page');


function get_data_from_priority(){
	PriorityAPI\API::instance()->run();
	// make request
	$response = PriorityAPI\API::instance()->makeRequest('GET', 'LOGPART', null,true);

	if ($response['code']<=201) {
		$body_array = json_decode($response["body"],true);
		return  $body_array['value'];
	}
	if($response['code'] >= 400){
		$body_array = json_decode($response["body"],true);
		wp_die('Error!');
	}

	if (!$response['status']) {
		wp_die('Error!');
	}
}
