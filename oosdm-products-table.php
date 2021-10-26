<?php

echo "<div class=\"wrap\">";
echo "<h1 class=\"wp-heading-inline\">".esc_html(__('Out of Stock Display Manager','product-creation-time-saver-for-woocommerce'))."</h1>";
echo "<p><i>Note: This won't override your global setting for your out of stock products ","<strong><a href=".admin_url('admin.php?page=wc-settings&tab=products&section=inventory').">Here</a></strong></p></i>";
echo "<hr class=\"wp-header-end\">";
echo "<p class=\"oosdm-bulk-action\"><select class=\"oosdm-visibility-bulk\">
			<option value=\"--\">".esc_html(__('Bulk actions','out-of-stock-display-manager-for-woocommerce'))."</option>
					<option value=\"".esc_attr(__('visible','out-of-stock-display-manager-for-woocommerce'))."\">".esc_html(__('Shop and search results','out-of-stock-display-manager-for-woocommerce'))."</option>
					<option value=\"".esc_attr(__('catalog','out-of-stock-display-manager-for-woocommerce'))."\">".esc_html(__('Shop only','out-of-stock-display-manager-for-woocommerce'))."</option>
					<option value=\"".esc_attr(__('search','out-of-stock-display-manager-for-woocommerce'))."\">".esc_html(__('Search results only','out-of-stock-display-manager-for-woocommerce'))."</option>
					<option value=\"".esc_attr(__('hidden','out-of-stock-display-manager-for-woocommerce'))."\">".esc_html(__('Hidden','out-of-stock-display-manager-for-woocommerce'))."</option>
				</select> <input type=\"submit\" class=\"button oosdm-bulk-update\" value=\"".esc_attr(__('Apply to selected products','out-of-stock-display-manager-for-woocommerce'))."\"/><input type=\"hidden\" class=\"oosdm-appy-bulk-ids\" /> </p>";

echo "<p><form method=\"get\" class=\"oosdm-search-box\">
<input type=\"text\" name=\"s\" value=\"".(isset($_GET['s']) ? filter_var($_GET['s'], FILTER_SANITIZE_STRING) : "")."\"/>
<input type=\"hidden\" value=\"".esc_attr(__('product'))."\" name=\"post_type\"/> 
<input type=\"hidden\" value=\"".esc_attr(__('out-of-stock-display-manager-for-woocommerce'))."\" name=\"page\"/>
<input type=\"submit\" class=\"button oosdm-search-button\" value=\"".esc_attr(__('Search products','out-of-stock-display-manager-for-woocommerce'))."\"/></form></p>";

	$paged = isset($_GET['paged']) && is_numeric($_GET['paged']) ? filter_var($_GET['paged'], FILTER_SANITIZE_NUMBER_INT) : 1;
	$keyword = isset($_GET['s']) ? filter_var($_GET['s'], FILTER_SANITIZE_STRING) : "";
	$args = array(
        'post_type' => 'product',
	    'posts_per_page' => 20,
	    'post_status' => 'publish',
	    'paged'=>$paged,
	    's'=> $keyword,
	    'meta_query' => array(
	       array(
	          'key' => '_stock_status',
	          'value' => 'outofstock',
	       )
	    )
    );

    $loop = new WP_Query( $args );
    $table_content = "";
    
    if($loop->have_posts()):
    while ( $loop->have_posts() ) : $loop->the_post();
    	
        global $product;

        $visibilities = get_the_terms(get_the_ID(),'product_visibility');
        $terms = get_the_terms( $product->get_id(), 'product_cat' );
        $categories_array = array();
        foreach($terms as $term){
        	$categories_array[] = $term->name;
        }
        $table_content .= "<tr>
        	<th scope=\"row\" class=\"check-column\">			
        		<label class=\"screen-reader-text\" for=\"cb-select-".get_the_ID()."\">
				</label>
			<input id=\"cb-select-".get_the_ID()."\" class=\"oosdm-cb\" type=\"checkbox\" name=\"post[]\" value=\"".get_the_ID()."\">			
			</th>
			<td class=\"name column-name has-row-actions column-primary\">
				<a href=\"".get_permalink()."\">" . woocommerce_get_product_thumbnail('outofstock-custom-thumb')."</a>
			</td>
			<td><strong><a href=\"".get_permalink()."\" class=\"row-title\">".get_the_title()."</a></strong></td>
			<td>".$product->get_sku()."</td>
			<td>".$product->get_price_html()."</td>
			<td>".get_post_status(get_the_ID())."</td>
			<td>".implode(",",$categories_array)."</td>
			<td><select class=\"oosdm-visibility-".get_the_ID()."\">
					<option value=\"".esc_attr(__('visible','out-of-stock-display-manager-for-woocommerce'))."\" ".oosdm_selected_display('visible',$visibilities).">".esc_html(__('Shop and search results','out-of-stock-display-manager-for-woocommerce'))."</option>
					<option value=\"".esc_attr(__('catalog','out-of-stock-display-manager-for-woocommerce'))."\" ".oosdm_selected_display('catalog',$visibilities).">".esc_html(__('Shop only','out-of-stock-display-manager-for-woocommerce'))."</option>
					<option value=\"".esc_attr(__('search','out-of-stock-display-manager-for-woocommerce'))."\" ".oosdm_selected_display('search',$visibilities).">".esc_html(__('Search results only','out-of-stock-display-manager-for-woocommerce'))."</option>
					<option value=\"".esc_attr(__('hidden','out-of-stock-display-manager-for-woocommerce'))."\" ".oosdm_selected_display('hidden',$visibilities).">".esc_html(__('Hidden','out-of-stock-display-manager-for-woocommerce'))."</option>
				</select>
			</td>
			<td><a href=\"#\" class=\"button button-primary update_visibility\" attr-id=\"".get_the_ID()."\">".esc_html(__('Update','out-of-stock-display-manager-for-woocommerce'))."</a> <span class=\"oosdm-loading oosdm-loading-".get_the_ID()."\"></td>
			</tr>";
    endwhile;

else:
	$table_content .="<tr><td colspan=\"9\" class=\"oosdm-norec\"><p>".esc_html(__('No records found!','out-of-stock-display-manager-for-woocommerce'))."</p></td></tr>";
endif;

    

    

    echo "<table class=\"oosdm wp-list-table widefat fixed striped table-view-list\">
    	<thead>
    		<tr>
    			<td id=\"cb\" class=\"manage-column column-cb check-column\"><input id=\"cb-select-all-1\" type=\"checkbox\"></td>

    			<th scope=\"col\" id=\"thumb\" class=\"manage-column column-thumb\"></th>
    			<th class=\"oosdm-product-name-col\">".esc_html(__('Products','out-of-stock-display-manager-for-woocommerce'))."</th>
    			<th>".esc_html(__('SKU','out-of-stock-display-manager-for-woocommerce'))."</th>
    			<th>".esc_html(__('Price','out-of-stock-display-manager-for-woocommerce'))."</th>
    			<th>".esc_html(__('Status','out-of-stock-display-manager-for-woocommerce'))."</th>
    			<th>".esc_html(__('Categories','out-of-stock-display-manager-for-woocommerce'))."</th>
    			<th>".esc_html(__('Visibility','out-of-stock-display-manager-for-woocommerce'))."</th>
    			<th class=\"oosdm-action\">".esc_html(__('Actions','out-of-stock-display-manager-for-woocommerce'))."</th>
    		</tr>
    	</thead>
    	<tbody>
    	".$table_content."
    	</tbody>
    </table>";
    echo "<div class=\"oosdm-pagination\">";
     echo paginate_links( array(
     	  'base' => '%_%',
          'format' => '?paged=%#%',
          'current' => $paged,
          'total' => $loop->max_num_pages,
          'prev_text' => '&laquo; Prev',
          'next_text' => 'Next &raquo;'
     ) );
     wp_reset_postdata();

echo "</div>";
?>
<script type="text/javascript">
	jQuery(document).ready(function($){

		// update button indv
		$('.update_visibility').click(function(){
			var id = $(this).attr('attr-id');
			var visibility = $(".oosdm-visibility-"+id).val();
			$('.oosdm-loading-'+id).show();
			$('.oosdm-loading-'+id).html('<i>Processing...</i>');
			$.post("<?php echo admin_url("admin-ajax.php")?>", {
				id: id,visibility:visibility,action: 'oosdm_update_update_prod_display'
			}).done(function(ids) {
				var json = $.parseJSON(ids);
				$.each(json, function( index, value ) {				
					$('.oosdm-loading-'+value).html('<span class="oosdm-success">Visibility updated!</span>');
					 setTimeout(function(){
						$('.oosdm-loading-'+value).hide();
					},2000);
				});
			});
		});

		// update button bulk
		$('.oosdm-bulk-update').click(function(){
			var ids = $('.oosdm-appy-bulk-ids').val().split(",");
			var visibility = $('.oosdm-visibility-bulk').val();
			if(ids.length>0){
				$.each(ids, function( index, value ) {				
				 	$('.oosdm-loading-'+value).show();
					$('.oosdm-loading-'+value).html('<i>Processing...</i>');
				});
				$.post("<?php echo admin_url("admin-ajax.php")?>", {
					id: ids,visibility:visibility,action: 'oosdm_update_update_prod_display'
				}).done(function(ids) {
					var json = $.parseJSON(ids);
					$.each(json, function( index, value ) {				
					 	$('.oosdm-loading-'+value).html('<span class="oosdm-success">Visibility updated!</span>');
					 	$(".oosdm-visibility-"+value+" option[value="+visibility+"]").prop('selected', true);
					 	setTimeout(function(){
							$('.oosdm-loading-'+value).hide();
						},2000);
					});
					
				});
			}
			
		});

		// check all
		$('#cb-select-all-1').change(function(){
			var ids = [];
			$('.oosdm-appy-bulk-ids').val("");			
			$('.oosdm-cb:checked').each(function() {
				var ids_string;
				ids.push(this.value);
				ids_string = ids.join(',');
				$('.oosdm-appy-bulk-ids').val(ids_string);
			});
		});

		// check indv
		$('.oosdm-cb').change(function(){
			var ids = [];		
			$('.oosdm-appy-bulk-ids').val("");	
			$('.oosdm-cb:checked').each(function() {
				var ids_string;
				ids.push(this.value);
				ids_string = ids.join(',');				
				$('.oosdm-appy-bulk-ids').val(ids_string);
			});
		});
	});
</script>