<?php
function gnv_dropdown_rss_link() {
	$source      = $_POST['source'];
	$list_source = list_rss_source();
	$list_link   = isset( $list_source[ $source ] ) ? $list_source[ $source ]['list'] : [];
	$str         = '<option value=""> -- Chọn link RSS -- </option>';
	foreach ( $list_link as $link ) {
		$str .= '<option value="' . $link . '">' . $link . '</option>';
	}
	echo $str;
	wp_die();
}

function gnv_rss_process() {
	$rss_link  = $_POST['rss_link'];
	$list_link = parseRSSLinks( $rss_link );
	$available = [];
	$existed   = [];
	$die       = [];
	//check link đã tồn tại trong content
	foreach ( $list_link as $link ) {
		if ( ! gnv_check_link_die( $link ) ) {
			$die[] = $link;
			continue;
		}
		if ( gnv_check_link( $link ) ) {
			$existed[] = $link;
			continue;
		}
		$available[] = $link;
	}
	echo json_encode( [
		'total'         => count( $available ),
		'links'         => $available,
		'existed'       => $existed,
		'total_rss'     => count( $list_link ),
		'rejected'      => count( $existed ) + count( $die ),
		'rejected_link' => $existed + $die
	] );
	die();
}

function gnv_news_process() {
	$link   = $_POST['link'];
	$cat    = $_POST['cat'];
	$status = $_POST['status'];
	$data   = process_link( trim($link), $cat, $status );
	//insert post
	$id = wp_insert_post( $data['data'] );
	Generate_Featured_Image( $data['img'], $id );
	update_post_meta( $id, 'link_get_content', $link );
}