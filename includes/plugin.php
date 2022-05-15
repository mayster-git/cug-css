<?php
namespace CuG\CSS;
if (! defined('ABSPATH')) {
	exit;
}


if (! class_exists(__NAMESPACE__ .'\Plugin')) {
	
	class Plugin {

		private static $instances = [];


        public $path = CUG_CSS__PATH;
        public $uri = CUG_CSS__URI;
        public $version = CUG_CSS__VERSION;

		public function init() {
			\add_action( 'wp_loaded', [$this, 'register_blocks'], 99 );
			require_once $this->path . 'includes/render.php';
			Render::get_instance()->init();
		}

		public function register_blocks() {

			$registered = \WP_Block_Type_Registry::get_instance()->get_all_registered();

			foreach ( $registered as $name => $block ) {	
				$block->attributes['cugCSS'] = array(
					'type'    => 'object',
					'default' => ''
				);
			}

            add_action( 'enqueue_block_editor_assets', [$this, 'enqueue_block_editor_assets'] );

		}

		public function enqueue_block_editor_assets() {
			$cm_settings['codeEditor'] = wp_enqueue_code_editor( [ 'type' => 'text/css' ] );

			wp_localize_script( 'jquery', 'cm_settings', $cm_settings );
           
            wp_enqueue_script( 'wp-theme-plugin-editor' );
            wp_enqueue_style( 'wp-codemirror' );

			$script_rel_path = "build/index.js";

			$script_asset_path = $this->path . substr_replace( $script_rel_path, '.asset.php', - strlen( '.js' ) );
            
            $dependencies = [];

            if ( file_exists( $script_asset_path ) ) {
				$script_asset = require $script_asset_path;
                $dependencies = $script_asset['dependencies'] ?? [];
            }
              
            wp_enqueue_script(
                'cug-css-editor-script',
                $this->uri . $script_rel_path,
                array_merge( $dependencies, [ 'code-editor', 'csslint' ] ),
                $this->version,
                true
            );

			$style_rel_path = "build/editor.css";

            add_theme_support( 'editor-styles' );
            add_editor_style( $this->uri . $style_rel_path . "?v=" . $this->version );

		}
		
		public static function get_instance()
		{
			$cls = static::class;
			if ( ! isset( self::$instances[$cls] ) ) {
				self::$instances[$cls] = new static();
			}

			return self::$instances[$cls];
		}

		protected function __construct() { }

		protected function __clone() { }
	
		public function __wakeup()
		{
			throw new \Exception( "Cannot unserialize a singleton." );
		}

	}
}

