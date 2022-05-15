<?php
namespace CuG\CSS;
if (! defined('ABSPATH')) {
	exit;
}


if (! class_exists(__NAMESPACE__ .'\Render')) {
	
	class Render {

		private static $instances = [];

        public $path = CUG_CSS__PATH;
        public $uri = CUG_CSS__URI;
        public $version = CUG_CSS__VERSION;

		public $inline_styles = [];

		public function init() {
			\add_filter( 'render_block', [$this, 'render_block'], 10, 2 );
			\add_action( 'wp_footer', [$this, 'add_inline_style'], 100 );
		}


        public function render_block( $block_content, $block ) {
			if ( is_admin() || ( defined('REST_REQUEST') && REST_REQUEST ) ) {
				return $block_content;
			}

			if ( empty( $block['attrs']['cugCSS'] ) ) {
				return $block_content;
			}
			
			$css_text = $block['attrs']['cugCSS'];
			$css_text = $this->minify_css( $css_text );
            $block_name = $block['blockName'] ?? '';

			$css_index = array_search( $css_text, $this->inline_styles );
			if ( $css_index === false ) {
				$css_index = count( $this->inline_styles );
				$this->inline_styles[] = $css_text;
			}
						
			$content = '<html><body>' . $block_content . '<body><html>';
			$document = \mb_convert_encoding($content, 'HTML-ENTITIES', "UTF-8");
			$dom = new \DOMDocument();
			\libxml_use_internal_errors(true);
			$dom->loadHTML( utf8_decode($document));
            $body = $dom->getElementsByTagName( 'body' );                
			$body = $body[0] ?? $body;

			$elements  = [];
			foreach ( $body->childNodes as $child ) {
				$elements[] = $child;
			}		  
				
			foreach ( $elements as $element ) {				
				if( $element->nodeType == 1 ) {
					$class = $element->getAttribute( 'class' );
					if( ! empty( $class ) ) {
						$classes = explode( " ", $class );
					} else {
						$classes = [];
					}
					$classes = array_map( 'trim', $classes );
					$classes[] = "cug-css-" . $css_index;
					$classes = array_unique( $classes );
					$classes = implode( " ", $classes );
					$element->setAttribute( 'class', $classes );
				}
			}		  
		  
			$block_content = "";
			$children  = $body->childNodes;
		
			foreach ( $body->childNodes as $child )
			{
				$block_content .= $dom->saveHTML($child);
			}	

			return $block_content;
		}

		function minify_css( $css ){
			$css = preg_replace('/\/\*((?!\*\/).)*\*\//', '', $css);
			$css = preg_replace('/\s{2,}/', ' ', $css);
			$css = preg_replace('/\s*([:;{}])\s*/', '$1', $css);
			$css = preg_replace('/;}/', '}', $css);
			return $css;
		}

		public function add_inline_style() {
			if ( empty( $this->inline_styles ) ) {
				return;
			}

			$styles = [];

			foreach ( $this->inline_styles as $key => $style ) {
				$pattern = "/\\b(THIS|ThiS|ThIS|THiS)\\b/m";
				$replacement = ".cug-css-" . $key;
				$styles[] = preg_replace( $pattern, $replacement, $style );
			}

			$style = sprintf( 
				'<style id="%1$s">%2$s</style>',
				"cug-css-custom-inline-style",
				implode("\r\n", $styles)
			);

			echo $style;			
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

