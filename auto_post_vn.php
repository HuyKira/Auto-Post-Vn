<?php
/*
Plugin Name: Auto Post VN
Plugin URI: https://huykira.net/webmaster/wordpress/plugin-lay-tin-tu-dong-tu-vnexpress-net.html
Description: Plugin Auto Post VN by Huy Kira
Author: Huy Kira
Version: 2.0
Author URI: http://www.huykira.net
- vnexpress.net
- kenh14.vn
- dantri.com.vn
- tuoitre.vn
- news.zing.vn
- nld.com.vn
- vietnamnet.vn
- laodong.com.vn
- genk.vn
*/
if ( ! function_exists( 'add_action' ) ) {
	echo 'Hi there!  I\'m just a plugin, not much I can do when called directly.';
	exit;
}

define( 'HK_VNEXPRESS_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'HK_VNEXPRESS_PLUGIN_RIR', plugin_dir_path( __FILE__ ) );

require_once( ABSPATH . "wp-includes/pluggable.php" );
if ( ! class_exists( 'simple_html_dom_node' ) ) {
	require_once( HK_VNEXPRESS_PLUGIN_RIR . 'includes/simple_html_dom.php' );
}
require_once HK_VNEXPRESS_PLUGIN_RIR . 'includes/helpers.php';
require_once HK_VNEXPRESS_PLUGIN_RIR . 'ajax.php';
add_action( 'admin_menu', 'gnv_add_menu_hk' );
add_action('wp_ajax_gnv_dropdown_rss_link', 'gnv_dropdown_rss_link');
add_action('wp_ajax_gnv_rss_process', 'gnv_rss_process');
add_action('wp_ajax_gnv_news_process', 'gnv_news_process');


function gnv_add_menu_hk() {
	add_menu_page(
		__( 'Auto Post', 'textdomain' ),
		'Auto Post',
		'manage_options',
		'hk_auto_post',
		'gnv_create_page',
		'dashicons-index-card'
	);
	add_action( 'admin_init', 'gnv_register_mysettings' );
}

;
function gnv_register_mysettings() {
	register_setting( 'my-settings-group', 'gnv_add_menu_hk' );
	register_setting( 'my-settings-group', 'some_other_option' );
	register_setting( 'my-settings-group', 'option_etc' );
}

function gnv_custom_style() {
	wp_enqueue_style( 'boots_css', HK_VNEXPRESS_PLUGIN_URL . 'scripts/css/bootstrap.min.css', false, '1.0.0' );
	wp_enqueue_style( 'custom_css', HK_VNEXPRESS_PLUGIN_URL . 'scripts/css/style.css', false, '1.0.0' );
	wp_enqueue_script( 'boots_js', HK_VNEXPRESS_PLUGIN_URL . 'scripts/js/bootstrap.min.js', true, '1.0.0' );
	wp_enqueue_script( 'custom_js', HK_VNEXPRESS_PLUGIN_URL . 'scripts/js/custom.js', true, '1.0.0' );
}

if ( gnv_curPageURL() == admin_url( 'admin.php?page=hk_auto_post' ) ) {
	add_action( 'admin_enqueue_scripts', 'gnv_custom_style' );
}
if ( gnv_curPageURL() == admin_url( 'admin.php?page=hk_auto_post&settings-updated=true' ) ) {
	add_action( 'admin_enqueue_scripts', 'gnv_custom_style' );
}

function gnv_create_page() { ?>
	<?php $options = get_option( 'gnv_add_menu_hk' );
	$args = array(
		'hide_empty' => 0,
		'taxonomy'   => 'category',
	);
	$cates      = get_categories( $args );
	?>

    <div class="wrap tp-app">
        <h2>AUTO POST</h2>
        <br>
        <div class="col-xs-12 col-sm-12 col-md-6">
            <form name="post" action="" method="post" id="post" autocomplete="off">
				<?php wp_nonce_field( 'get_new_express' ); ?>
                <div class="row">
                    <div class="input-muti">
                        <div class="form-group">
                            <label for="link">Nhập link bài viết</label>
                            <input required="required" name="link[]" type="url" class="form-control" id=""
                                   placeholder="Nhập link vào đây" value="<?php if ( isset( $_POST['link'] ) ) {
								echo sanitize_text_field( $_POST['link'][0] );
							} ?>">
                            <span class="glyphicon glyphicon-remove" aria-hidden="true"></span>
                        </div>
                    </div>
                    <div class="list-input">
						<?php if ( isset( $_POST['add_post'] ) and ( $_POST['add_post'] == 'true' ) ) {
							$links = $_POST['link'];
							if ( is_array( $links ) ) {
								foreach ( $links as &$link ) {
									$link = sanitize_text_field( $link );
								}
								foreach ( $links as $key => $value ) {
									if ( $key == 0 ) {
									} else { ?>
                                        <div class="form-group">
                                            <label for="link">Nhập link</label>
                                            <input required="required" name="link[]" type="url" class="form-control"
                                                   value="<?php echo $value; ?>">
                                            <span class="glyphicon glyphicon-remove" aria-hidden="true"></span>
                                        </div>
									<?php }
								}
							}
						} ?>
                    </div>
                    <div class="more">
                        <span class="click-more">Thêm link</span>
                    </div>
                    <div class="row">
                        <div class="col-xs-12 col-sm-12 col-md-6">
                            <div class="form-group">
                                <label for="cat">Chọn chuyên mục</label>
                                <select name="cat" id="input" class="form-control" required="required">
									<?php
									foreach ( $cates as $cate ) {
										if ( isset( $_POST['cat'] ) ) {
											$cat_id = sanitize_text_field( $_POST['cat'] );
										}
										?>
                                        <option value="<?php echo $cate->term_id; ?>" <?php if ( $cat_id == $cate->term_id ) {
											echo 'selected';
										} ?>><?php echo $cate->name; ?></option>
									<?php } ?>
                                    <!-- Get category -->
                                </select>
                            </div>
                        </div>
                        <div class="col-xs-12 col-sm-12 col-md-6">
                            <div class="form-group">
                                <label for="cat">Chọn trạng thái</label>
                                <select name="status" id="input" class="form-control" required="required">
                                    <option value="Pending" <?php if ( sanitize_text_field( $_POST['status'] ) == 'Pending' ) {
										echo 'selected';
									} ?>>Xét duyệt
                                    </option>
                                    <option value="Publish" <?php if ( sanitize_text_field( $_POST['status'] ) == 'Publish' ) {
										echo 'selected';
									} ?>>Đăng luôn
                                    </option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <input type="hidden" name="add_post" value="true">
                    <div class="alignleft">
                        <button>Nhập bài viết</button>
                    </div>
					<?php
					if ( isset( $_POST['add_post'] ) and ( $_POST['add_post'] == 'true' ) ) {
						if ( isset( $_POST['cat'] ) ) {
							$cat = sanitize_text_field( $_POST['cat'] );
							if ( isset( $_POST['status'] ) ) {
								$status = sanitize_text_field( $_POST['status'] );
								if ( isset( $_POST['link'] ) ) {
									$links = $_POST['link'];
									if ( is_array( $links ) ) {
										foreach ( $links as &$link ) {
											$link = sanitize_text_field( $link );
										}
										foreach ( $links as $key => $value ) {
											qnv_get_express( $value, $cat, $status );
										}
									}
								}
							}

						}
					}
					?>
                </div>
            </form>
            <div class="row">
                <div class="info-setting">
                    <form action="options.php" method="POST" role="form">
						<?php settings_fields( 'my-settings-group' ); ?>
						<?php do_settings_sections( 'my-settings-group' ); ?>
                        <p><input type="checkbox" id="gnv_add_menu_hk[cmt]" name="gnv_add_menu_hk[cmt]"
                                  value="1" <?php if ( isset( $options['cmt'] ) ) {
								echo 'checked';
							} ?>/> <label for="gnv_add_menu_hk[cmt]">Bật chức năng thảo luận</label></p>
                        <p class="kiki">Loại bỏ nội dung lấy về bằng cách thêm các <strong>vùng chọn (class,
                                id...)</strong> vào các textbox bên dưới! </p>
                        <div class="row">
                            <div class="option-list">
                                <div class="col-xs-12 col-sm-12 col-md-6">
                                    <div class="input-hk form-group">
                                        <input type="text" class="form-control" name="gnv_add_menu_hk[list-op][]"
                                               value="<?php if ( isset( $options['list-op'][0] ) ) {
											       echo $options['list-op'][0];
										       } ?>">
                                        <span class="glyphicon glyphicon-remove" aria-hidden="true"></span>
                                    </div>
                                </div>
                            </div>
                            <div class="list-hihi">
								<?php if ( isset( $options['list-op'] ) ) { ?>
									<?php foreach ( $options['list-op'] as $key => $value ) { ?>
										<?php if ( $key == 0 ) {
										} else { ?>
                                            <div class="col-xs-12 col-sm-12 col-md-6">
                                                <div class="input-hk form-group">
                                                    <input type="text" class="form-control"
                                                           name="gnv_add_menu_hk[list-op][]"
                                                           value="<?php echo $value; ?>">
                                                    <span class="glyphicon glyphicon-remove" aria-hidden="true"></span>
                                                </div>
                                            </div>
										<?php } ?>
									<?php } ?>
								<?php } ?>
                            </div>
                            <div class="clear"></div>
                            <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
                                <span class="click-add">Thêm tùy chọn</span>
                            </div>
                        </div>
                        <div class="alignleft">
                            <button>Lưu</button>
                        </div>
                    </form>
                    <div class="clear"></div>
                </div>
            </div>
        </div>
        <div class="col-xs-12 col-sm-12 col-md-6">
            <div class="info-plugin">
                <h2>Lấy tin từ RSS</h2>
                <div class="form-group">
                    <label for="rss_source">Chọn nguồn RSS</label>
                    <select name="rss_source" id="rss_source" class="form-control">
                        <option value="-1"> -- Chọn nguồn RSS --</option>
                        <?php
                        $list_rss = list_rss_source();
                        foreach ($list_rss as $index=>$source) {
                            ?>
                            <option value="<?=$index?>"><?=$source['source']?></option>
                            <?php
                        }
                        ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="rss_link">Chọn link RSS</label>
                    <select name="rss_link" id="rss_link" class="form-control">
                        <option value=""> -- Chọn link RSS -- </option>
                    </select>
                </div>
                <div class="row">
                    <div class="col-xs-12 col-sm-12 col-md-6">
                        <div class="form-group">
                            <label for="rss_cat">Chọn chuyên mục</label>
                            <select name="rss_cat" id="rss_cat" class="form-control">
			                    <?php
			                    foreach ( $cates as $cate ) {
				                    ?>
                                    <option value="<?php echo $cate->term_id; ?>" ><?php echo $cate->name; ?></option>
			                    <?php } ?>
                                <!-- Get category -->
                            </select>
                        </div>
                    </div>
                    <div class="col-xs-12 col-sm-12 col-md-6">
                        <div class="form-group">
                            <label for="rss_status">Chọn trạng thái</label>
                            <select name="rss_status" id="rss_status" class="form-control" required="required">
                                <option value="Pending">Xét duyệt
                                </option>
                                <option value="Publish">Đăng luôn
                                </option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <button id="rss_process" class="btn btn-success">Tiến hành lấy tin</button>
                </div>
                <div class="hidden" id="rss_process_status">
                    <p class="help-block">Trạng thái quét tin: <span id="total_rss">0</span></p>
                    <p class="help-block">Số tin bị trùng/lỗi: <span id="rejected">0</span></p>
                    <p class="help-block">
                        Tiến trình: <span id="current_scan">0</span>/<span id="total_scan">0</span> bài viết
                    </p>
                    <p class="alert alert-success hidden" id="process-done">Đã hoàn thành xong việc lấy tin!</p>
                </div>
            </div>
        </div>
    </div>
	<?php if ( isset( $options['cmt'] ) ) { ?>
        <div class="clear"></div>
        <div class="wrap tp-app">
            <h2>Thảo luận về plugin</h2>
            <br>
            <div class="cmt">
                <div class="fb-comments" data-width="100%"
                     data-href="https://huykira.net/webmaster/wordpress/plugin-lay-tin-tu-dong-tu-vnexpress-net.html"
                     data-numposts="3"></div>
            </div>
            <div id="fb-root"></div>
        </div>
	<?php } ?>
<?php }

function qnv_get_express( $link, $cat, $status ) {
	$list_web = array(
		'news.zing.vn'            => true,
		'kenh14.vn'               => true,
		'dantri.com.vn'           => true,
		'vnexpress.net'           => true,
		'tuoitre.vn'              => true,
		'nld.com.vn'              => true,
		'vietnamnet.vn'           => true,
		'laodong.com.vn'          => true,
		'laodong.vn'              => true,
		'genk.vn'                 => true,
		'kinhdoanh.vnexpress.net' => true,
		'giaitri.vnexpress.net'   => true,
		'thethao.vnexpress.net'   => true,
		'suckhoe.vnexpress.net'   => true,
		'giadinh.vnexpress.net'   => true,
		'dulich.vnexpress.net'    => true,
		'sohoa.vnexpress.net'     => true,
		'raovat.vnexpress.net'    => true
	);
	$url      = $link;
	$web      = explode( '/', $url )[2];
	if ( ! gnv_check_link_die( $url ) ) { ?>
        <div class="clear"></div>
        <br>
        <div class="alert alert-danger">
            <p><?php echo $url; ?> - Link không tồn tại!</p>
        </div>
	<?php } else if ( $list_web[ $web ] ) {
	    $data_process = process_link($url, $cat, $status);
		$my_post = $data_process['data'];
		$img = $data_process['img'];
		if ( gnv_check_link( $url ) ) { ?>
            <div class="clear"></div>
            <br>
            <div class="alert alert-danger">
                <p><?php echo $url; ?>: Đã tồn tại!</p>
            </div>
		<?php } else {
			$id = wp_insert_post( $my_post );
			Generate_Featured_Image( $img, $id );
			update_post_meta( $id, 'link_get_content', $url ); ?>
            <div class="clear"></div>
            <br>
            <div class="alert alert-success">
                <p>Post link '<?php echo $url; ?>' Thành công!</p>
            </div>
		<?php } ?>
	<?php } else { ?>
        <div class="clear"></div>
        <br>
        <div class="alert alert-danger">
            <p><?php echo $url; ?> - Sai địa chỉ website</p>
        </div>
	<?php } ?>
<?php }

function gnv_curPageURL() {
	$pageURL = 'http';
	if ( ! empty( $_SERVER['HTTPS'] ) ) {
		if ( $_SERVER['HTTPS'] == 'on' ) {
			$pageURL .= "s";
		}
	}
	$pageURL .= "://";
	if ( $_SERVER["SERVER_PORT"] != "80" ) {
		$pageURL .= $_SERVER["SERVER_NAME"] . ":" . $_SERVER["SERVER_PORT"] . $_SERVER["REQUEST_URI"];
	} else {
		$pageURL .= $_SERVER["SERVER_NAME"] . $_SERVER["REQUEST_URI"];
	}

	return $pageURL;
}

function gnv_get_meta_values( $key = '', $type = 'post' ) {
	global $wpdb;
	if ( empty( $key ) ) {
		return;
	}
	$r = $wpdb->get_col( $wpdb->prepare( "
			SELECT pm.meta_value FROM {$wpdb->postmeta} pm
			LEFT JOIN {$wpdb->posts} p ON p.ID = pm.post_id
			WHERE pm.meta_key = '%s' 
			AND p.post_type = '%s'
			", $key, $type ) );

	return $r;
}

