<?php
/**
 * Uninstall cleanup.
 *
 * @package CookieConsentBar
 */

defined( 'WP_UNINSTALL_PLUGIN' ) || exit;

delete_option( 'cookie-consent-bar_options' );
