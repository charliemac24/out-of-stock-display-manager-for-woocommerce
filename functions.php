<?php

if( !function_exists('oosdm_register_submenu_page') ){

	function oosdm_register_submenu_page(){
		add_submenu_page(
            'edit.php?post_type=product',
            'Out of Stock Display Manager',
            'Out of Stock Display Manager',
            'manage_options',
            'out-of-stock-display-manager-for-woocommerce',
            'oosdm_load_template'
    	);
	}
	add_action( 'admin_menu', 'oosdm_register_submenu_page' );
}

if( !function_exists('oosdm_load_template') ){

	function oosdm_load_template(){

		$page = isset($_GET['page']) && $_GET['page'] == 'out-of-stock-display-manager-for-woocommerce' ? 'oosdm-products-table' : "";

		include_once(OOSDM_PLUGIN_DIR.'/'.$page.'.php');

	}

}

if ( function_exists( 'add_theme_support' ) ) {
    add_theme_support( 'post-thumbnails' );
    add_image_size( 'outofstock-custom-thumb', 40, 40); // 50 pixels wide 
 }

 add_action( 'admin_head', function(){
	echo "
	<style type='text/css'>
		table.wp-list-table .column-thumb{
			width: 52px;
		    text-align: center;
		    white-space: nowrap;
		}
		.oosdm-loading{
			display:none;
			margin-left:30px;
		}
		.oosdm-product-name-col {
			width:20%;
		}
		.oosdm-pagination{
			margin-top: 10px;
		}
		.oosdm-action{
			width:16%;
		}
		.oosdm-norec p{
			color:red;
		}
		.oosdm-pagination a,
		.oosdm-pagination span {
			font-size: 12px !important;
		    background-color: #999999;
		    padding: 5px 10px;
		    color: #ffffff;
		}
		.oosdm-pagination span.current{
			background-color:#bdbdbd !important;
		}
		.oosdm-bulk-action{
			float:left;
		}
		.oosdm-search-box{
			float:right;
		}
		.oosdm-success{
			color:green !important;
			font-weight:bold;
		}
	</style>
	";
});



if( !function_exists('oosdm_update_update_prod_display')){

	add_action( 'wp_ajax_oosdm_update_update_prod_display', 'oosdm_update_update_prod_display' );
	add_action( 'wp_ajax_nopriv_oosdm_update_update_prod_display', 'oosdm_update_update_prod_display' );

	function oosdm_update_update_prod_display(){

		global $wpdb;
		
		$param_ids = array();
		if(is_array($_REQUEST['id']) && isset($_REQUEST['id'])){
			foreach($_REQUEST['id'] as $id){
				$param_ids[] = filter_var($id,FILTER_SANITIZE_NUMBER_INT);
			}
		}elseif(is_numeric($_REQUEST['id']) && isset($_REQUEST['id']) && !is_array($_REQUEST['id'])){
			$param_ids[] = filter_var($_REQUEST['id'],FILTER_SANITIZE_NUMBER_INT);
		}

		$param_display = isset($_REQUEST['visibility']) && !is_numeric($_REQUEST['visibility']) ? filter_var($_REQUEST['visibility'], FILTER_SANITIZE_STRING) : "";

		$term_obj1 = get_term_by('slug',"exclude-from-catalog",'product_visibility');
		$term_obj2 = get_term_by('slug',"exclude-from-search",'product_visibility');
		$term_obj3 = get_term_by('slug',"outofstock",'product_visibility');

		$terms_array = array(
			$term_obj1->term_id,
			$term_obj2->term_id,
			$term_obj3->term_id
		);

		if($param_display == 'hidden'){

			// clear first
			oosdm_product_remove_display($param_ids,$terms_array);
		
			// insert all
			foreach($param_ids as $id){
				wp_set_object_terms($id,$term_obj1->term_id,'product_visibility',true);
				wp_set_object_terms($id,$term_obj2->term_id,'product_visibility',true);
				wp_set_object_terms($id,$term_obj3->term_id,'product_visibility',true);
			}

			echo json_encode($param_ids,JSON_FORCE_OBJECT);

		}elseif($param_display == 'search'){

			// clear first
			oosdm_product_remove_display($param_ids,$terms_array);
		
			// insert all
			foreach($param_ids as $id){
				wp_set_object_terms($id,$term_obj1->term_id,'product_visibility',true);
				wp_set_object_terms($id,$term_obj3->term_id,'product_visibility',true);
			}

			echo json_encode($param_ids,JSON_FORCE_OBJECT);
		}elseif($param_display == 'catalog'){

			// clear first
			oosdm_product_remove_display($param_ids,$terms_array);
		
			// insert all
			foreach($param_ids as $id){
				wp_set_object_terms($id,$term_obj2->term_id,'product_visibility',true);
				wp_set_object_terms($id,$term_obj3->term_id,'product_visibility',true);
			}

			echo json_encode($param_ids,JSON_FORCE_OBJECT);

		}else{

			// clear first
			oosdm_product_remove_display($param_ids,$terms_array);
		
			// insert all
			foreach($param_ids as $id){
				wp_set_object_terms($id,$term_obj3->term_id,'product_visibility',true);
			}

			echo json_encode($param_ids,JSON_FORCE_OBJECT);
		}
		
		exit();
		
	}
}


if( !function_exists('oosdm_product_remove_display')){

	function oosdm_product_remove_display($param_ids,$terms_array){

		foreach($param_ids as $id){
			foreach($terms_array as $term_id){
				wp_remove_object_terms($id,$term_id,'product_visibility');
			}
		}
	}

}


if( !function_exists('oosdm_selected_display')){

	function oosdm_selected_display($attr_val="",$array){

		if($attr_val == 'visible'){
			if($array[0]->slug == 'outofstock' && !isset($array[1])){
				return "selected";
			}
		}elseif($attr_val == 'catalog') {
			$arr = array();
			foreach($array as $k=>$val){	
				$arr[] = $array[$k]->slug;
			}
			
			if(in_array('exclude-from-search',$arr) && !in_array('exclude-from-catalog', $arr)){
				return "selected";
			}
		}elseif($attr_val == 'search') {
			$arr = array();
			foreach($array as $k=>$val){	
				$arr[] = $val->slug;
			}
			if(!in_array('exclude-from-search',$arr) && in_array('exclude-from-catalog', $arr)){
				return "selected";
			}
		}else{
			$arr = array();
			foreach($array as $k=>$val){	
				$arr[] = $val->slug;
			}
			if(in_array('exclude-from-search',$arr) && in_array('exclude-from-catalog', $arr)){
				return "selected";
			}
		}
		
	}

}