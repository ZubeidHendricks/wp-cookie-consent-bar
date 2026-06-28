<?php
/**
 * Plugin Name:       Cookie Consent Bar
 * Plugin URI:        https://zubeidhendricks.dev/wp-plugins/cookie-consent-bar
 * Description:        A lightweight, GDPR-friendly cookie consent banner with accept/decline and a remembered choice — no bloat, no external calls.
 * Version:           1.0.0
 * Requires at least: 5.8
 * Requires PHP:      7.2
 * Author:            Zubeid Hendricks
 * Author URI:        https://zubeidhendricks.dev
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       cookie-consent-bar
 *
 * @package CookieConsentBar
 */

defined( 'ABSPATH' ) || exit;

define( 'COOKIE_CONSENT_BAR_VERSION', '1.0.0' );

require_once __DIR__ . '/includes/factory-core.php';

/**
 * Cookie Consent Bar.
 */
final class CookieConsentBar extends ZubFactory_Plugin {

	protected function configure() {
		$this->slug    = 'cookie-consent-bar';
		$this->title   = 'Cookie Consent Bar';
		$this->version = COOKIE_CONSENT_BAR_VERSION;
	}

	protected function settings_fields() {
		return array(
			'enabled'  => array(
				'label'    => __( 'Status', 'cookie-consent-bar' ),
				'type'     => 'checkbox',
				'cb_label' => __( 'Show the consent banner', 'cookie-consent-bar' ),
				'default'  => 1,
			),
			'message'  => array(
				'label'   => __( 'Message', 'cookie-consent-bar' ),
				'type'    => 'textarea',
				'default' => 'We use cookies to improve your experience. By using our site you accept our use of cookies.',
			),
			'accept'   => array(
				'label'   => __( 'Accept button', 'cookie-consent-bar' ),
				'type'    => 'text',
				'default' => 'Accept',
			),
			'decline'  => array(
				'label'   => __( 'Decline button', 'cookie-consent-bar' ),
				'type'    => 'text',
				'desc'    => __( 'Leave blank to hide the decline button.', 'cookie-consent-bar' ),
				'default' => 'Decline',
			),
			'policy'   => array(
				'label'   => __( 'Privacy policy URL', 'cookie-consent-bar' ),
				'type'    => 'text',
				'default' => '',
			),
			'bg'       => array(
				'label'   => __( 'Background colour', 'cookie-consent-bar' ),
				'type'    => 'color',
				'default' => '#1e293b',
			),
			'block_scripts' => array(
				'label'    => __( 'Script blocking', 'cookie-consent-bar' ),
				'type'     => 'checkbox',
				'cb_label' => __( 'Block analytics/marketing scripts until consent', 'cookie-consent-bar' ),
				'pro'      => true,
			),
		);
	}

	protected function hooks() {
		add_action( 'wp_footer', array( $this, 'render' ) );
	}

	public function render() {
		if ( ! $this->option( 'enabled', 1 ) || is_admin() ) {
			return;
		}
		$message = trim( (string) $this->option( 'message', '' ) );
		if ( '' === $message ) {
			return;
		}

		$accept  = trim( (string) $this->option( 'accept', 'Accept' ) );
		$decline = trim( (string) $this->option( 'decline', 'Decline' ) );
		$policy  = esc_url( $this->option( 'policy', '' ) );
		$bg      = $this->option( 'bg', '#1e293b' ) ?: '#1e293b';
		?>
		<style>
			#zcc{position:fixed;bottom:0;left:0;right:0;z-index:99992;display:none;
				background:<?php echo esc_attr( $bg ); ?>;color:#fff;
				padding:16px 20px;font-size:14px;line-height:1.5;font-family:inherit;
				box-shadow:0 -2px 12px rgba(0,0,0,.2)}
			#zcc .zcc-in{max-width:1100px;margin:0 auto;display:flex;gap:16px;
				align-items:center;flex-wrap:wrap;justify-content:center}
			#zcc p{margin:0;flex:1 1 280px}
			#zcc a{color:#93c5fd}
			#zcc button{cursor:pointer;border:0;border-radius:6px;padding:9px 18px;font-weight:600;font-size:14px}
			#zcc .zcc-yes{background:#22c55e;color:#062b12}
			#zcc .zcc-no{background:transparent;color:#fff;border:1px solid rgba(255,255,255,.4)}
		</style>
		<div id="zcc" role="dialog" aria-label="<?php esc_attr_e( 'Cookie consent', 'cookie-consent-bar' ); ?>">
			<div class="zcc-in">
				<p>
					<?php echo esc_html( $message ); ?>
					<?php if ( $policy ) : ?>
						<a href="<?php echo $policy; // phpcs:ignore ?>"><?php esc_html_e( 'Learn more', 'cookie-consent-bar' ); ?></a>
					<?php endif; ?>
				</p>
				<div>
					<?php if ( $decline ) : ?>
						<button class="zcc-no" type="button"><?php echo esc_html( $decline ); ?></button>
					<?php endif; ?>
					<button class="zcc-yes" type="button"><?php echo esc_html( $accept ); ?></button>
				</div>
			</div>
		</div>
		<script>
		(function(){
			var bar=document.getElementById('zcc');
			function get(n){return document.cookie.split('; ').find(function(r){return r.indexOf(n+'=')===0;});}
			function set(v){
				var d=new Date();d.setTime(d.getTime()+180*24*60*60*1000);
				document.cookie='zcc_consent='+v+'; expires='+d.toUTCString()+'; path=/; SameSite=Lax';
			}
			if(get('zcc_consent')){return;}
			bar.style.display='block';
			function close(v){set(v);bar.style.display='none';
				document.dispatchEvent(new CustomEvent('zcc:consent',{detail:v}));}
			var y=bar.querySelector('.zcc-yes'),n=bar.querySelector('.zcc-no');
			if(y)y.addEventListener('click',function(){close('accepted');});
			if(n)n.addEventListener('click',function(){close('declined');});
		})();
		</script>
		<?php
	}
}

add_action(
	'plugins_loaded',
	function () {
		( new CookieConsentBar( __FILE__ ) )->boot();
	}
);
