<?php

class QTX_Admin_Modules {
	/**
	 * Update the modules to be loaded for plugin integration.
	 * Note each module can enable hooks both for admin and front requests.
	 * The valid modules are stored in the 'qtranslate_modules' option.
	 */
	public static function update_modules_option() {
		require_once( ABSPATH . 'wp-admin/includes/plugin.php' );

		$def_modules    = QTX_Modules_Handler::get_modules_defs();
		$option_modules = array();
		foreach ( $def_modules as $module ) {
			$status = self::check_module( $module['plugin'], $module['incompatible'] );
			if ( isset( $status ) ) {
				$option_modules[ $module['id'] ] = $status;
			}
		}
		update_option( 'qtranslate_modules', $option_modules );
	}

	/**
	 * Check if an integration module can be activated:
	 * - if the linked plugin to be integrated is active
	 * - if no incompatible plugin (legacy) prevents it. In that case, an admin notice is displayed.
	 *
	 * @param string $integration_plugin plugin file to be checked for integration
	 * @param string $incompatible_plugin legacy plugin that is incompatible and must be deactivated for module integration
	 *
	 * @return bool true if module can be activated, false if incompatible or null
	 */
	protected static function check_module( $integration_plugin, $incompatible_plugin ) {
		$module_status = null;
		if ( is_plugin_active( $integration_plugin ) ) {
			if ( isset( $incompatible_plugin ) && is_plugin_active( $incompatible_plugin ) ) {
				$module_status = false;
				add_action( 'admin_notices', function () use ( $incompatible_plugin ) {
					$plugin_data = get_plugin_data( WP_PLUGIN_DIR . '/' . $incompatible_plugin, false, true );
					$plugin_name = $plugin_data['Name'];
					if ( is_plugin_active( $incompatible_plugin ) ) :
						?>
                        <div class="notice notice-error is-dismissible">
                            <p><?php printf( __( '[%s] Incompatible plugin detected: "%s". Please disable it.', 'qtranslate' ), 'qTranslate&#8209;XT', $plugin_name ); ?></p>
                            <p><a class="button"
                                  href="<?php echo esc_url( wp_nonce_url( admin_url( 'plugins.php?action=deactivate&plugin=' . urlencode( $incompatible_plugin ) ), 'deactivate-plugin_' . $incompatible_plugin ) ) ?>"><strong><?php printf( __( 'Deactivate plugin %s', 'qtranslate' ), $plugin_name ) ?></strong></a>
                        </div>
					<?php
					endif;
				} );
			} else {
				$module_status = true;
			}
		}

		return $module_status;
	}

	/**
	 * Retrieve infos for all modules (for display).
     * The status is retrieved from the modules option.
	 */
	public static function get_modules_infos() {
		$def_modules     = QTX_Modules_Handler::get_modules_defs();
		$options_modules = get_option( 'qtranslate_modules', array() );
		$infos           = array();
		foreach ( $def_modules as $def_module ) {
			$info = array();
			$info['name'] = $def_module['name'];
			if ( array_key_exists( $def_module['id'], $options_modules ) ) {
				$info['active'] = true;
				$info['status'] = $options_modules[ $def_module['id'] ];
			} else {
				$info['active'] = false;
				$info['status'] = false;
			}
			array_push($infos, $info);
		}
		return $infos;
	}
}


