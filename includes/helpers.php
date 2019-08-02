<?php
function parseRSSLinks($url){
	$rss = simplexml_load_file(trim($url));
	$links = [];
	foreach($rss->channel->item as $item) {
		$links[] = trim(htmlspecialchars($item->link));
	}
	return $links;
}
function getContent( $url, $method = 'get', $params = [], $options = [], $user_agent = null ) {
	try {
		$content = '';
		$url     = trim( $url );
		$method  = strtolower( $method );

		if ( function_exists( 'curl_init' ) ) {
			$options = $options + [
					CURLOPT_RETURNTRANSFER => true,
					CURLOPT_HEADER         => false,
					CURLOPT_URL            => $url,
					CURLOPT_TIMEOUT        => 180,
				];

			$ch = curl_init();

			if ( $user_agent ) {
				$options[ CURLOPT_USERAGENT ] = $user_agent;
			}

			if ( $method == 'post' ) {
				$options[ CURLOPT_POST ] = true;
				if ( ! empty( $params ) ) {
					$options[ CURLOPT_POSTFIELDS ] = http_build_query( $params );
				}
			}

			if ( strpos( $url, 'https' ) != false ) {
				$options[ CURLOPT_SSL_VERIFYPEER ] = false;
				$options[ CURLOPT_SSL_VERIFYHOST ] = false;
			}

			curl_setopt_array( $ch, $options );
			$content = curl_exec( $ch );

			// Set status code
			curl_getinfo( $ch, CURLINFO_HTTP_CODE );

			curl_close( $ch );
		}

		return $content;
	} catch ( \Exception $ex ) {
		//log
		echo $ex->getMessage();

		return '';
	}
}
function list_rss_source() {
	return [
		[
			'source' => 'VnExpress',
			'list'   => [
				'https://vnexpress.net/rss/thoi-su.rss',
				'https://vnexpress.net/rss/the-gioi.rss',
				'https://vnexpress.net/rss/kinh-doanh.rss',
				'https://vnexpress.net/rss/startup.rss',
				'https://vnexpress.net/rss/giai-tri.rss',
				'https://vnexpress.net/rss/the-thao.rss',
				'https://vnexpress.net/rss/phap-luat.rss',
				'https://vnexpress.net/rss/giao-duc.rss',
				'https://vnexpress.net/rss/suc-khoe.rss',
				'https://vnexpress.net/rss/doi-song.rss',
				'https://vnexpress.net/rss/du-lich.rss',
				'https://vnexpress.net/rss/khoa-hoc.rss',
				'https://vnexpress.net/rss/so-hoa.rss',
				'https://vnexpress.net/rss/xe.rss',
				'https://vnexpress.net/rss/y-kien.rss',
				'https://vnexpress.net/rss/tam-su.rss',
				'https://vnexpress.net/rss/cuoi.rss',
			]
		],
		[
			'source' => 'Tuổi trẻ',
			'list'   => [
				'https://tuoitre.vn/rss/the-gioi.rss',
				'https://tuoitre.vn/rss/kinh-doanh.rss',
				'https://tuoitre.vn/rss/xe.rss',
				'https://tuoitre.vn/rss/van-hoa.rss',
				'https://tuoitre.vn/rss/the-thao.rss',
				'https://tuoitre.vn/rss/khoa-hoc.rss',
				'https://tuoitre.vn/rss/gia-that.rss',
				'https://tuoitre.vn/rss/ban-doc-lam-bao.rss',
				'https://tuoitre.vn/rss/phap-luat.rss',
				'https://tuoitre.vn/rss/cong-nghe.rss',
				'https://tuoitre.vn/rss/nhip-song-tre.rss',
				'https://tuoitre.vn/rss/giai-tri.rss',
				'https://tuoitre.vn/rss/giao-duc.rss',
				'https://tuoitre.vn/rss/suc-khoe.rss',
				'https://tuoitre.vn/rss/thu-gian.rss',
				'https://tuoitre.vn/rss/du-lich.rss',
			]
		],
		[
			'source' => 'GenK',
			'list'   => [
				'http://genk.vn/tin-ict.rss',
				'http://genk.vn/internet.rss',
				'http://genk.vn/kham-pha.rss',
				'http://genk.vn/thu-thuat.rss',
				'http://genk.vn/tra-da-cong-nghe.rss',
				'http://genk.vn/apps-games.rss',
				'http://genk.vn/do-choi-so.rss'
			]
		],
	];
}

function process_link($url, $cat, $status){
	$html    = str_get_html( getContent( $url ) );
	$options = get_option( 'add_menu_hk' );
	if ( isset( $options['list-op'] ) ) {
		foreach ( $options['list-op'] as $select ) {
			foreach ( $html->find( $select ) as $value ) {
				$value->outertext = '';
			}
		}
	}
	$web      = explode( '/', $url )[2];
	if ( $web == 'laodong.com.vn' ) {
		$img = $html->find( '.cms-photo', 0 )->src;
		$img = explode( '?', $img )[0];
	}
	$mang_tag = array(
		'.block_timer_share'           => true,
		'#box_tinkhac_detail'          => true,
		'#box_comment'                 => true,
		'.block_input_comment'         => true,
		'.btn_icon_show_slide_show'    => true,
		'script'                       => true,
		'#social_like'                 => true,
		'#box_tinlienquan'             => true,
		'.title_div_fbook'             => true,
		'.relative_new'                => true,
		'table.article'                => true,
		'.banner_468'                  => true,
		'input'                        => true,
		'.media-content'               => true,
		'.news-tag'                    => true,
		'.box_embed_video_parent'      => true,
		'.tinlienquanold'              => true,
		'#TinLienQuanDetail'           => true,
		'#LoadTinLQRight'              => true,
		'.tlqdetail'                   => true,
		'.logo-small'                  => true,
		'.inner-article'               => true,
		'.box-taitro'                  => true,
		'#ctl00_mainContent_divAvatar' => true,
		'.cms-relate'                  => true,
	);

	foreach ( $mang_tag as $key => $value1 ) {
		if ( $html->find( $key ) ) {
			foreach ( $html->find( $key ) as $value ) {
				$value->outertext = '';
			}
		}
	}
	$html->load( $html->save() );

	foreach ( $html->find( 'img' ) as $value ) {
		$value->outertext = '<p style="text-align:center"><img style=" display: block; margin: 15px auto;" src="' . $value->src . '" ></p>';
	}
	foreach ( $html->find( 'video' ) as $value ) {
		$value->outertext = '<p style="text-align:center"><video poster="' . $value->getAttribute( 'poster' ) . '" width="600" height="400" preload="none" muted="" controls="controls" src="' . $value->src . '" ></video></p>';
	}
	foreach ( $html->find( 'a' ) as $value ) {
		$value->outertext = $value->innertext;
	}
	foreach ( $html->find( 'table' ) as $value ) {
		$value->outertext = $value->innertext;
	}
	foreach ( $html->find( 'tr' ) as $value ) {
		$value->outertext = $value->innertext;
	}
	foreach ( $html->find( 'td' ) as $value ) {
		$value->outertext = '<p>' . $value->innertext . '</p>';
	}
	foreach ( $html->find( 'figure' ) as $value ) {
		$value->outertext = $value->innertext;
	}
	foreach ( $html->find( 'tbody' ) as $value ) {
		$value->outertext = $value->innertext;
	}
	foreach ( $html->find( '.desc_cation' ) as $value ) {
		$value->outertext = $value->innertext;
	}
	foreach ( $html->find( '.short_intro' ) as $value ) {
		$value->outertext = $value->innertext;
	}
	foreach ( $html->find( '.block_thumb_slide_show' ) as $value ) {
		$value->outertext = $value->innertext;
	}
	foreach ( $html->find( '.item_slide_show' ) as $value ) {
		$value->outertext = $value->innertext;
	}
	foreach ( $html->find( 'div' ) as $value ) {
		if ( isset( $value->attr['data-component-type'] ) ) {
			$value->outertext = '';
		}
	}
	foreach ( $html->find( 'div' ) as $value ) {
		if ( isset( $value->attr['data-src'] ) ) {
			$value->outertext = '';
		}
	}
	foreach ( $html->find( 'div' ) as $value ) {
		$value->outertext = '<p>' . $value->innertext . '</p>';
	}
	foreach ( $html->find( '.VCSortableInPreviewMode' ) as $value ) {
		$value->outertext = $value->innertext;
	}
	$html->load( $html->save() );
	// Lấy tiêu đề
	if ( $html->find( '.title_news h1', 0 ) ) {
		$tieude = $html->find( '.title_news h1', 0 );
	} else {
		$tieude = $html->find( 'h1', 0 );
	}

	$content_tag = array(
		'.fck_detail',
		'#box_details_news .w670',
		'.cms-body',
		'.knc-content',
		'.fck',
		'#divNewsContent',
		'#ArticleContent',
		'#main_detail'
	);

	foreach ( $content_tag as $value ) {
		if ( $html->find( $value, 0 ) != null ) {
			$noidung = $html->find( $value, 0 );
			break;
		}
	}
	$img = null;
	foreach ( $content_tag as $value ) {
		if ( $html->find( $value . ' img', 0 )->src ) {
			$img = $html->find( $value . ' img', 0 )->src;
			break;
		}
	}
	$my_post = array(
		'post_title'    => $tieude->plaintext,
		'post_content'  => $noidung->innertext,
		'post_status'   => $status,
		'post_author'   => 1,
		'post_category' => array( $cat )
	);
	return [
		'data' => $my_post,
		'img'=>$img
	];
}
function Generate_Featured_Image( $image_url, $post_id ) {
	if ( empty( $image_url ) ) {
		return;
	}
	$upload_dir = wp_upload_dir();
	$image_data = file_get_contents( $image_url );
	$filename   = basename( $image_url );

	if ( wp_mkdir_p( $upload_dir['path'] ) ) {
		$file = $upload_dir['path'] . '/' . $filename;
	} else {
		$file = $upload_dir['basedir'] . '/' . $filename;
	}

	file_put_contents( $file, $image_data );

	$wp_filetype = wp_check_filetype( $filename, null );
	$attachment  = array(
		'guid'           => $upload_dir['url'] . '/' . basename( $filename ),
		'post_mime_type' => $wp_filetype['type'],
		'post_title'     => sanitize_file_name( $filename ),
		'post_content'   => '',
		'post_status'    => 'inherit'
	);
	$attach_id   = wp_insert_attachment( $attachment, $file, $post_id );
	require_once( ABSPATH . 'wp-admin/includes/image.php' );
	$attach_data = wp_generate_attachment_metadata( $attach_id, $file );
	$res1        = wp_update_attachment_metadata( $attach_id, $attach_data );
	$res2        = set_post_thumbnail( $post_id, $attach_id );
}
function gnv_check_link( $link ) {
	$meta_links = gnv_get_meta_values( "link_get_content" );
	$i          = 0;
	foreach ( $meta_links as $value ) {
		if ( $value == $link ) {
			$i ++;
		}
	}
	if ( $i == 0 ) {
		return false;
	} else {
		return true;
	}
}

function gnv_check_link_die( $url = null ) {
	if ( $url == null ) {
		return false;
	}
	$ch = curl_init( $url );
	curl_setopt( $ch, CURLOPT_TIMEOUT, 5 );
	curl_setopt( $ch, CURLOPT_CONNECTTIMEOUT, 5 );
	curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
	$data     = curl_exec( $ch );
	$httpcode = curl_getinfo( $ch, CURLINFO_HTTP_CODE );//lay code tra ve cua http
	curl_close( $ch );
	if ( $httpcode >= 200 && $httpcode < 300 ) {
		return true;
	} else {
		return false;
	}
}

class Gnv_Auto_Save_Images {

	function __construct() {

		add_filter( 'content_save_pre', array( $this, 'gnv_post_save_images' ) );
	}

	function gnv_post_save_images( $content ) {
		if ( ( $_POST['save'] || $_POST['publish'] ) ) {
			set_time_limit( 500 );
			global $post;
			$post_id = $post->ID;
			$preg    = preg_match_all( '/<img.*?src="(.*?)"/', stripslashes( $content ), $matches );
			if ( $preg ) {
				foreach ( $matches[1] as $image_url ) {
					if ( empty( $image_url ) ) {
						continue;
					}
					$pos = strpos( $image_url, $_SERVER['HTTP_HOST'] );
					if ( $pos === false ) {
						$res     = $this->gnv_save_images( $image_url, $post_id );
						$replace = $res['url'];
						$content = str_replace( $image_url, $replace, $content );
					}
				}
			}
		}
		remove_filter( 'content_save_pre', array( $this, 'gnv_post_save_images' ) );

		return $content;
	}

	function gnv_save_images( $image_url, $post_id ) {
		$file      = file_get_contents( $image_url );
		$post      = get_post( $post_id );
		$posttitle = $post->post_title;
		$postname  = sanitize_title( $posttitle );
		$im_name   = "$postname-$post_id.jpg";
		$res       = wp_upload_bits( $im_name, '', $file );
		$this->gnv_insert_attachment( $res['file'], $post_id );

		return $res;
	}

	function gnv_insert_attachment( $file, $id ) {
		$dirs        = wp_upload_dir();
		$filetype    = wp_check_filetype( $file );
		$attachment  = array(
			'guid'           => $dirs['baseurl'] . '/' . _wp_relative_upload_path( $file ),
			'post_mime_type' => $filetype['type'],
			'post_title'     => preg_replace( '/\.[^.]+$/', '', basename( $file ) ),
			'post_content'   => '',
			'post_status'    => 'inherit'
		);
		$attach_id   = wp_insert_attachment( $attachment, $file, $id );
		$attach_data = wp_generate_attachment_metadata( $attach_id, $file );
		wp_update_attachment_metadata( $attach_id, $attach_data );

		return $attach_id;
	}
}

new Gnv_Auto_Save_Images();