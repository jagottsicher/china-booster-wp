<?php
/**
 * Plugin Name: WordPress-China
 * Description: A plugin to connect your WordPress installation to a domestically (China) hosted ecosystem to access downloads and services faster and more reliable
 * Author: WP中国本土化社区
 * Author URI:https://wp-china.org/
 * Version: 3.5.4
 * Network: True
 * License: GPLv3 or laterSettings
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'WP_CHINA_YES' ) ) {
	class WP_CHINA_YES {
		private $page_url;

		public function __construct() {
			$this->page_url = network_admin_url( is_multisite() ? 'settings.php?page=wp-china-yes' : 'options-general.php?page=wp-china-yes' );
		}

		public function init() {
			if ( is_admin() && ! ( defined( 'DOING_AJAX' ) && DOING_AJAX ) ) {
				/**
				 * Add settings to the plug-in list item
				 */
				add_filter( sprintf( '%splugin_action_links_%s', is_multisite() ? 'network_admin_' : '', plugin_basename( __FILE__ ) ), function ( $links ) {
					return array_merge(
						[ sprintf( '<a href="%s">%s</a>', $this->page_url, 'Settings' ) ],
						$links
					);
				} );


				/**
				 * Add "Translate Calibration" link to all plugins in the plugins list page
				 */
				// if (get_option('wpapi') == 1) {
				add_filter( sprintf( '%splugin_action_links', is_multisite() ? 'network_admin_' : '' ), function ( $links, $plugin = '' ) {
					$links[] = '<a target="_blank" href="https://litepress.cn/translate/projects/plugins/' . substr( $plugin, 0, strpos( $plugin, '/' ) ) . '/">Participate in translation</a>';

					return $links;
				}, 10, 2 );
				//}


				/**
				 * Initialize Settings items
				 */
				update_option( "wpapi", get_option( 'wpapi' ) ?: '2' );
				update_option( "super_admin", get_option( 'super_admin' ) ?: '2' );
				update_option( "super_gravatar", get_option( 'super_gravatar' ) ?: '1' );
				update_option( "super_googlefonts", get_option( 'super_googlefonts' ) ?: '2' );
				update_option( "super_googleajax", get_option( 'super_googleajax' ) ?: '2' );
				update_option( "super_cdnjs", get_option( 'super_cdnjs' ) ?: '2' );


				/**
				 * Delete configuration when disabling plugins
				 */
				register_deactivation_hook( __FILE__, function () {
					delete_option( "wpapi" );
					delete_option( "super_admin" );
					delete_option( "super_gravatar" );
					delete_option( "super_googlefonts" );
					delete_option( "super_googleajax" );
					delete_option( "super_cdnjs" );
				} );


				/**
				 * Menu Registration
				 */
				add_action( is_multisite() ? 'network_admin_menu' : 'admin_menu', function () {
					add_submenu_page(
						is_multisite() ? 'settings.php' : 'options-general.php',
						'WP-China-Yes',
						'WP-China-Yes',
						is_multisite() ? 'manage_network_options' : 'manage_options',
						'wp-china-yes',
						[ $this, 'options_page_html' ]
					);
				} );


				/**
				 * Replace the static file access links that WordPress core relies on with public resource nodes
				 */
				if (
					get_option( 'super_admin' ) != 2 &&
					! stristr( $GLOBALS['wp_version'], 'alpha' ) &&
					! stristr( $GLOBALS['wp_version'], 'beta' ) &&
					! stristr( $GLOBALS['wp_version'], 'RC' ) &&
					! isset( $GLOBALS['lp_version'] )
				) {
					$this->page_str_replace( 'preg_replace', [
						'~' . home_url( '/' ) . '(wp-admin|wp-includes)/(css|js)/~',
						sprintf( 'https://wpstatic.cdn.wepublish.cn/%s/$1/$2/', $GLOBALS['wp_version'] )
					], get_option( 'super_admin' ) );
				}
			}


			if ( is_admin() || wp_doing_cron() ) {
				add_action( 'admin_init', function () {
					/**
					 * wpapi用以标记用户所选的仓库api，数值说明：1 使用LitePress的API，2 只是经代理加速的api.wordpress.org原版API
					 */
					register_setting( 'wpcy', 'wpapi' );

					/**
					 * super_admin用以标记用户是否启用管理后台加速功能
					 */
					register_setting( 'wpcy', 'super_admin' );

					/**
					 * super_gravatar用以标记用户是否启用Cravatar头像功能
					 */
					register_setting( 'wpcy', 'super_gravatar' );

					/**
					 * super_googlefonts用以标记用户是否启用谷歌字体加速功能
					 */
					register_setting( 'wpcy', 'super_googlefonts' );
					
					/**
					 * super_cdnjs用以标记用户是否启用CDNJS加速功能
					 */
					register_setting( 'wpcy', 'super_cdnjs' );

					add_settings_section(
						'wpcy_section_main',
						'A plugin to connect your WordPress installation to a domestically (China) hosted ecosystem to access downloads and services faster and more reliable',
						'',
						'wpcy'
					);

					add_settings_field(
						'wpcy_field_select_wpapi',
						'Select Application Market',
						[ $this, 'field_wpapi_cb' ],
						'wpcy',
						'wpcy_section_main'
					);

					add_settings_field(
						'wpcy_field_select_super_admin',
						'Accelerated administration backend',
						[ $this, 'field_super_admin_cb' ],
						'wpcy',
						'wpcy_section_main'
					);

					add_settings_field(
						'wpcy_field_select_super_gravatar',
						'Use Cravatar avatar',
						[ $this, 'field_super_gravatar_cb' ],
						'wpcy',
						'wpcy_section_main'
					);

					add_settings_field(
						'wpcy_field_select_super_googlefonts',
						'Accelerated Google Fonts',
						[ $this, 'field_super_googlefonts_cb' ],
						'wpcy',
						'wpcy_section_main'
					);

					add_settings_field(
						'wpcy_field_select_super_googleajax',
						'Accelerated Google front-end public library',
						[ $this, 'field_super_googleajax_cb' ],
						'wpcy',
						'wpcy_section_main'
					);
					
					add_settings_field(
						'wpcy_field_select_super_cdnjs',
						'Accelerated CDNJS front-end public library',
						[ $this, 'field_super_cdnjs_cb' ],
						'wpcy',
						'wpcy_section_main'
					);
				} );

				/**
				 * 替换api.wordpress.org和downloads.wordpress.org为WP-China.org维护的大陆加速节点
				 * URL替换代码来自于我爱水煮鱼(http://blog.wpjam.com/)开发的WPJAM Basic插件
				 */
				add_filter( 'pre_http_request', function ( $preempt, $r, $url ) {
					if ( ( ! stristr( $url, 'api.wordpress.org' ) && ! stristr( $url, 'downloads.wordpress.org' ) ) || get_option( 'wpapi' ) == 3 ) {
						return $preempt;
					}
					if ( get_option( 'wpapi' ) == 1 ) {
						$url = str_replace( 'api.wordpress.org', 'api.litepress.cn', $url );
						$url = str_replace( 'downloads.wordpress.org', 'd.w.org.ibadboy.net', $url );
					} else {
						$url = str_replace( 'api.wordpress.org', 'api.w.org.ibadboy.net', $url );
						$url = str_replace( 'downloads.wordpress.org', 'd.w.org.ibadboy.net', $url );
					}

					$curl_version = '1.0.0';
					if ( function_exists( 'curl_version' ) ) {
						$curl_version_array = curl_version();
						if ( is_array( $curl_version_array ) && key_exists( 'version', $curl_version_array ) ) {
							$curl_version = $curl_version_array['version'];
						}
					}

					// 如果CURL版本小于7.15.0，说明不支持SNI，无法通过HTTPS访问又拍云的节点，故而改用HTTP
					if ( version_compare( $curl_version, '7.15.0', '<' ) ) {
						$url = str_replace( 'https://', 'http://', $url );
					}

					return wp_remote_request( $url, $r );
				}, 1, 3 );
			}


			if ( ! ( defined( 'DOING_AJAX' ) && DOING_AJAX ) ) {
				/**
				 * 替换谷歌字体为WePublish维护的加速节点
				 */
				if ( get_option( 'super_googlefonts' ) != 2 ) {
					$this->page_str_replace( 'str_replace', [
						'fonts.googleapis.com',
						'gfont.cdn.wepublish.cn'
					], get_option( 'super_googlefonts' ) );
				}

				/**
				 * 替换谷歌前端公共库为WePublish维护的加速节点
				 */
				if ( get_option( 'super_googleajax' ) != 2 ) {
					$this->page_str_replace( 'str_replace', [
						'ajax.googleapis.com',
						'gajax.cdn.wepublish.cn'
					], get_option( 'super_googleajax' ) );
				}
				
				/**
				 * 替换CDNJS前端公共库为WePublish维护的加速节点
				 */
				if ( get_option( 'super_cdnjs' ) != 2 ) {
					$this->page_str_replace( 'str_replace', [
						'cdnjs.cloudflare.com/ajax/libs',
						'cdnjs.cdn.wepublish.cn'
					], get_option( 'super_cdnjs' ) );
				}
			}

			/**
			 * 替换Gravatar头像为Cravatar头像
			 */
			if ( get_option( 'super_gravatar' ) == 1 ) {
				if ( ! function_exists( 'get_cravatar_url' ) ) {
					/**
					 * 替换Gravatar头像为Cravatar头像
					 *
					 * Cravatar是Gravatar在中国的完美替代方案，你可以在https://cravatar.cn更新你的头像
					 */
					function get_cravatar_url( $url ) {
						$sources = array(
							'www.gravatar.com',
							'0.gravatar.com',
							'1.gravatar.com',
							'2.gravatar.com',
							'secure.gravatar.com',
							'cn.gravatar.com',
							'gravatar.com',
						);

						return str_replace( $sources, 'cravatar.cn', $url );
					}

					/**
					 * 替换WordPress讨论设置中的默认LOGO名称
					 */
					function set_defaults_for_cravatar( $avatar_defaults ) {
						$avatar_defaults['gravatar_default'] = 'Cravatar 标志';

						return $avatar_defaults;
					}

					/**
					 * 替换个人资料卡中的头像上传地址
					 */
					function set_user_profile_picture_for_cravatar() {
						return '<a href="https://cravatar.cn" target="_blank">您可以在 Cravatar 修改您的资料图片</a>';
					}

					add_filter( 'user_profile_picture_description', 'set_user_profile_picture_for_cravatar' );
					add_filter( 'avatar_defaults', 'set_defaults_for_cravatar', 1 );
					add_filter( 'um_user_avatar_url_filter', 'get_cravatar_url', 1 );
					add_filter( 'bp_gravatar_url', 'get_cravatar_url', 1 );
					add_filter( 'get_avatar_url', 'get_cravatar_url', 1 );
				}
			}
		}

		public function field_wpapi_cb() {
			$wpapi = get_option( 'wpapi' );
			?>
            <label>
                <input type="radio" value="2" name="wpapi" <?php checked( $wpapi, '2' ); ?>>Official App Market Accelerated Mirror
            </label>
            <label>
                <input type="radio" value="1" name="wpapi" <?php checked( $wpapi, '1' ); ?>>LitePress Application Marketplace (Technology Trial)
            </label>
            <label>
                <input type="radio" value="3" name="wpapi" <?php checked( $wpapi, '3' ); ?>>No takeover of the application market
            </label>
            <p class="description">
                <b>Official App Market Accelerated Mirror</b>: Directly reverse-generated from official and distributed in mainland China, no changes except for adding support for WP-China-Yes plugin updates
            </p>
            <p class="description">
                <b>LitePress Application Marketplace</b>: The interface is in the development stage and currently provides a link to the <a href="https://litepress.cn/translate" target="_blank">LitePress
                    翻译平台</a> the integration of<b> (Note that you may encounter unknown bugs when using this interface, and you can help with  <a href="https://litepress.cn/" target="_blank">feedback</a>)</b>
            </p>
			<?php
		}

		public function field_super_admin_cb() {
			$this->field_cb( 'super_admin', 'Switch the static files that WordPress core relies on to public resources, this option greatly speeds up access to the admin backend', true );
		}

		public function field_super_gravatar_cb() {
			$this->field_cb( 'super_gravatar', 'Cravatar is the perfect alternative to Gravatar in China, and you can find it on <a href="https://cravatar.cn" target="_blank">https://cravatar.cn</a> Update your avatar. (Any developer can integrate the service in their own product)' );
		}

		public function field_super_googlefonts_cb() {
			$this->field_cb( 'super_googlefonts', 'Please enable this option only if you include Google Fonts to avoid unnecessary performance loss' );
		}

		public function field_super_googleajax_cb() {
			$this->field_cb( 'super_googleajax', 'Please only enable this option if the Google front-end public library is included, to avoid unnecessary performance loss' );
		}
		
		public function field_super_cdnjs_cb() {
			$this->field_cb( 'super_cdnjs', 'Please enable this option only if the CDNJS front-end public library is included, to avoid unnecessary performance loss' );
		}

		public function options_page_html() {
			if ( $_SERVER['REQUEST_METHOD'] === 'POST' ) {
				update_option( "wpapi", sanitize_text_field( $_POST['wpapi'] ) );
				update_option( "super_admin", sanitize_text_field( $_POST['super_admin'] ) );
				update_option( "super_gravatar", sanitize_text_field( $_POST['super_gravatar'] ) );
				update_option( "super_googlefonts", sanitize_text_field( $_POST['super_googlefonts'] ) );
				update_option( "super_googleajax", sanitize_text_field( $_POST['super_googleajax'] ) );
				update_option( "super_cdnjs", sanitize_text_field( $_POST['super_cdnjs'] ) );

				echo '<div class="notice notice-success settings-error is-dismissible"><p><strong>Settings saved</strong></p></div>';
			}

			if ( ! current_user_can( 'manage_options' ) ) {
				return;
			}

			settings_errors( 'wpcy_messages' );
			?>
            <div class="wrap">
                <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
                <form action="<?php echo $this->page_url; ?>" method="post">
					<?php
					settings_fields( 'wpcy' );
					do_settings_sections( 'wpcy' );
					submit_button( 'Save configuration' );
					?>
                </form>
            </div>
            <p>
                <a href="https://wp-china.org" target="_blank">WP China Localized Community</a>Our mission is to help WordPress establish a good local ecological environment in China, in order to promote the overall development of the industry and make the market cake bigger.<br/>
                Special Thanks<a href="https://zmingcx.com/" target="_blank">知更鸟</a>、<a href="https://www.weixiaoduo.com/"
                                                                              target="_blank">薇晓朵团队</a>、<a
                        href="https://www.appnode.com/" target="_blank">AppNode</a>Help given in the budding stage of the project.<br/>
                        The server resources required for the project are provided by<a href="https://www.vpsor.cn/" target="_blank">硅云</a> and <a href="https://www.upyun.com/"
                                                                                    target="_blank">又拍云</a>.
            </p>
			<?php
		}

		private function field_cb( $option_name, $description, $is_global = false ) {
			$option_value = get_option( $option_name );

			if ( ! $is_global ):
				?>
                <label>
                    <input type="radio" value="3"
                           name="<?php echo $option_name; ?>" <?php checked( $option_value, '3' ); ?>>Front Desk Enablement
                </label>
                <label>
                    <input type="radio" value="4"
                           name="<?php echo $option_name; ?>" <?php checked( $option_value, '4' ); ?>>Backend Enable
                </label>
			<?php endif; ?>
            <label>
                <input type="radio" value="1"
                       name="<?php echo $option_name; ?>" <?php checked( $option_value, '1' ); ?>><?php echo $is_global ? 'enable' : 'enable globally' ?>
            </label>
            <label>
                <input type="radio" value="2"
                       name="<?php echo $option_name; ?>" <?php checked( $option_value, '2' ); ?>>Disable
            </label>
            <p class="description">
				<?php echo $description; ?>
            </p>
			<?php
		}

		/**
		 * @param $replace_func string 要调用的字符串关键字替换函数
		 * @param $param array 传递给字符串替换函数的参数
		 * @param $level int 替换级别：1.全局替换 3.前台替换 4.后台替换
		 */
		private function page_str_replace( $replace_func, $param, $level ) {
			if ( $level == 3 && is_admin() ) {
				return;
			} elseif ( $level == 4 && ! is_admin() ) {
				return;
			}

			add_action( 'init', function () use ( $replace_func, $param ) {
				ob_start( function ( $buffer ) use ( $replace_func, $param ) {
					$param[] = $buffer;

					return call_user_func_array( $replace_func, $param );
				} );
			} );
		}
	}

	( new WP_CHINA_YES )->init();
}
