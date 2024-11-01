<?php
/**
 * If this file is called directly, call the cops.
 *
 * @package       Webhook_Configuration_CF7
 * @author        Ritu Trivedi
 * @license       gplv2
 * @version       1.0.0
 */

defined( 'ABSPATH' ) || die( 'No script kiddies please!' );

/**
 * $contactform is 'WPCF7_ContactForm' from 'WCCF7_Module_CF7::html_template_panel_html'
 */

$activate          = '0';
$hook_url          = array();
$special_mail_tags = '';
$wccf7_skip_tags     = '';
$custom_headers    = '';

if ( is_a( $contactform, 'WPCF7_ContactForm' ) ) {
	$properties = $contactform->prop( WCCF7_Module_CF7::METADATA );

	if ( isset( $properties['activate'] ) ) {
		$activate = $properties['activate'];
	}

	if ( isset( $properties['hook_url'] ) ) {
		$hook_url = (array) $properties['hook_url'];
	}

	if ( isset( $properties['wccf7_skip_tags'] ) ) {
		$wccf7_skip_tags = $properties['wccf7_skip_tags'];
	}

	if ( isset( $properties['special_mail_tags'] ) ) {
		$special_mail_tags = $properties['special_mail_tags'];
	}

	if ( isset( $properties['custom_headers'] ) ) {
		$custom_headers = $properties['custom_headers'];
	}
}

?>

<h2>
	<strong><?php esc_attr_e( 'Webhook Configuration', 'webhook-configuration-cf7' ); ?></strong>
</h2>

<fieldset>
	<legend>
		<?php esc_attr_e( 'The option provides you facility to integration with webhook.', 'webhook-configuration-cf7' ); ?>
		<br>
		<?php esc_attr_e( 'Insert your webhook URL below for the webhok integration.', 'webhook-configuration-cf7' ); ?>
	</legend>
	<hr style="margin: 10px 0 30px 0;">
	<table class="form-table">
		<tbody>
			<tr>
				<th scope="row">
					<label>
						<?php esc_attr_e( 'Integrate with Webhook', 'webhook-configuration-cf7' ); ?>
					</label>
				</th>
				<td>
					<p>
						<label for="wcc-webhook-activate">
							<input type="checkbox" id="wcc-webhook-activate" name="wcc-webhook-activate" value="1" <?php checked( $activate, '1' ); ?>>
							<?php esc_attr_e( 'Send to Webhook', 'webhook-configuration-cf7' ); ?>
							<?php wp_nonce_field( 'wcc_webhook_activation', 'wcc_webhook_activation_nonce' ); ?>
						</label>
					</p>
				</td>
			</tr>
			<tr>
				<th scope="row">
					<label>
						<?php esc_attr_e( 'Insert Webhook URL', 'webhook-configuration-cf7' ); ?>
					</label>
				</th>
				<td>
					<p>
						<label for="wcc-webhook-hook-url">
							<textarea id="wcc-webhook-hook-url" name="wcc-webhook-hook-url" rows="4" style="width: 100%;"><?php echo esc_textarea( implode( PHP_EOL, $hook_url ) ); ?></textarea>
							<?php wp_nonce_field( 'wcc_webhook_hook_url', 'wcc_webhook_hook_url_nonce' ); ?>
						</label>
					</p>
					<?php if ( $activate && empty( $hook_url ) ) : ?>
						<p class="description" style="color: #D00;">
							<?php esc_html__( 'You should insert webhook URL here to finish configuration.','webhook-configuration-cf7' ); ?>
						</p>
					<?php else : ?>
						<p class="description">
							<?php esc_html__( 'Multiple webhooks can also be added: one per line.', 'webhook-configuration-cf7' ); ?>
						</p>
					<?php endif; ?>
				</td>
			</tr>
		</tbody>
	</table>
</fieldset>
<hr style="margin: 10px 0 30px 0;">
<h2>
	<strong><?php esc_attr_e( 'Special Mail Tags', 'webhook-configuration-cf7' ); ?></strong>
</h2>

<fieldset>
	<legend>
		<?php echo esc_html__('You can insert', 'webhook-configuration-cf7'); ?>
		<?php echo '<a href="'. esc_url('https://contactform7.com/special-mail-tags/').'" target="_blank">Special mail Tags</a>'; ?>
		<?php echo esc_html__('or', 'webhook-configuration-cf7'); ?>
		<?php echo '<a href="'. esc_url('https://contactform7.com/selectable-recipient-with-pipes/').'" target="_blank">Selectable recipient with pipes</a>'; ?>
		<?php echo esc_html__('to the data sent to webhook.', 'webhook-configuration-cf7'); ?>
	</legend>

	<div style="margin: 20px 0;">
		<label for="wcc-special-mail-tags">
			<?php
				$special_mail_tags = esc_textarea( $special_mail_tags );
				$rows              = ( (int) substr_count( $special_mail_tags, "\n" ) ) + 2;
				$rows              = max( $rows, 4 );
			?>
			<textarea id="wcc-special-mail-tags" name="wcc-special-mail-tags" class="large-text code" rows="<?php echo esc_attr($rows); ?>"><?php echo esc_textarea( $special_mail_tags ); ?></textarea>
		    <?php wp_nonce_field( 'wcc_special_mail_tags_field', 'wcc_special_mail_tags_nonce' ); ?>
		</label>
		<p class="description">
		<?php
			echo esc_html__( 'Insert Special Tags like in mail body:', 'webhook-configuration-cf7' );
			echo '<span style="font-family: monospace; font-size: 12px; font-weight: bold;">[_post_title]</span>';
			echo '<br>';
			echo esc_html__( 'Or can insert a second word  as key to Webhook:', 'webhook-configuration-cf7' );
			echo '<span style="font-family: monospace; font-size: 12px; font-weight: bold;">[_post_title title]</span>';
			?>
		</p>
	</div>
</fieldset>
<hr style="margin: 10px 0 30px 0;">
<h2>
	<strong><?php esc_attr_e( 'Custom Headers Request', 'webhook-configuration-cf7' ); ?></strong>
</h2>

<fieldset>
	<legend>
		<?php echo esc_html__('You can insert', 'webhook-configuration-cf7'); ?>
		<?php echo '<a href="'. esc_url('https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers').'" target="_blank">HTTP Headers</a>'; ?>
		<?php echo esc_html__(' to your webhook request.', 'webhook-configuration-cf7'); ?>
	</legend>

	<div style="margin: 20px 0;">
		<label for="wcc-custom-headers">
			<?php
				$custom_headers = esc_textarea( $custom_headers );
				$rows           = ( (int) substr_count( $custom_headers, "\n" ) ) + 2;
				$rows           = max( $rows, 4 );
			?>
			<textarea id="wcc-custom-headers" name="wcc-custom-headers" class="large-text code" rows="<?php echo esc_attr( $rows ); ?>"><?php echo esc_html( $custom_headers ); ?></textarea>
			<?php wp_nonce_field( 'wcc_custom_headers_field', 'wcc_custom_headers_nonce' ); ?>
		</label>
		<p class="description">
			<?php echo esc_html__('One header by line, separated by colon. Example:', 'webhook-configuration-cf7'); ?>
			<?php echo '<span style="font-family: monospace; font-size: 12px; font-weight: bold;">'. esc_html__('Authorization: Bearer 99999999999999999999', 'webhook-configuration-cf7').'</span>'; ?>
		</p>
	</div>
</fieldset>

<hr style="margin: 10px 0 30px 0;">
<h2>
	<strong><?php esc_attr_e( 'URL Params', 'webhook-configuration-cf7' ); ?></strong>
</h2>

<fieldset>
	<legend>
	<?php echo esc_html__('For the URL paramerrs like', 'webhook-configuration-cf7'); ?>
	<?php echo '<a href="'. esc_url('https://contactform7.com/hidden-field/').'" target="_blank">Hidden Fields</a> with'; ?>
	<?php echo '<a href="'. esc_url('https://contactform7.com/getting-default-values-from-the-context/').' "target="_blank">'. esc_html__( 'default values', 'webhook-configuration-cf7' ) . '</a>'; ?>
	<?php echo esc_html__(' in your form.', 'webhook-configuration-cf7'); ?>
	</legend>

	<div style="margin: 20px 0;">
		<pre style="background: #FFF; border: 1px solid #CCC; padding: 10px; margin: 0;"><?php echo esc_html__('For the utm_source: https://example.com/?utm_source=example
Here is the shortcode: [hidden utm_source default:get]', 'webhook-configuration-cf7'); ?></pre>
	</div>
</fieldset>
<hr style="margin: 10px 0 30px 0;">
<h2>
	<strong><?php esc_attr_e( 'Data Skip to Webhook', 'webhook-configuration-cf7' ); ?></strong>
</h2>
	<fieldset>
		<label for="wwcc-skip-tag">
			<?php
				$wccf7_skip_tags = esc_textarea( $wccf7_skip_tags );
				$rows          = ( (int) substr_count( $wccf7_skip_tags, "\n" ) ) + 2;
				$rows          = max( $rows, 4 );
			?>
			<textarea id="wwcc-skip-tag" name="wwcc-skip-tag" class="large-text code" rows="<?php echo esc_attr($rows); ?>" placeholder="[your-name]"><?php echo esc_textarea( esc_attr($wccf7_skip_tags) ); ?></textarea>
			<?php wp_nonce_field( 'wcc_skipping_tags', 'wcc_skip_tag_nonce' ); ?>
		</label>
		<?php esc_attr_e( 'Insert the tags of contact form that you want to skip for webhook integration, Example : For [text your-name], just simply add [your-name].', 'webhook-configuration-cf7' ); ?>
	</fieldset>
<fieldset>
	<hr style="margin: 30px 0 30px 0;">
	<h2>
		<strong><?php esc_attr_e( 'Data sent to Webhook', 'webhook-configuration-cf7' ); ?></strong>
	</h2>
	<div style="margin: 20px 0;">
		<?php
		$sent_data = array();
		// Special Tags.
		$special_tags  = array();
		$skipping_tags = WCCF7_Module_CF7::get_special_mail_tags_from_config_string( $wccf7_skip_tags );
		$special_tags  = WCCF7_Module_CF7::get_wccf7_skip_tags_from_config_string( $special_mail_tags );
		$tags          = array_keys( $special_tags );
		// Form Tags.
		$form_tags = $contactform->scan_form_tags();
		wp_nonce_field( 'wcc_all_tags', 'wcc_all_tag_nonce' );
		?>
		<?php
		foreach ( $form_tags as $form_tag ) {
			$key = $form_tag->get_option( 'Webhook_Configuration_CF7' );
			if ( ! empty( $key ) && ! empty( $key[0] ) ) {
				$tags[] = $key[0];
				continue;
			}
			$tags[] = $form_tag->name;
		}
		?>
		<legend>
			<strong><?php esc_attr_e( 'We will send your form data as below:', 'webhook-configuration-cf7' ); ?></strong>
		</legend>
		<?php
		if ( array_diff( $tags, $skipping_tags ) ) {
			$tags = array_diff( $tags, $skipping_tags );
			foreach ( $tags as $taglist ) {
				if ( empty( $taglist ) ) {
					continue;
				}
				$sent_data[ $taglist ] = '??????';
			}
		}
		?>
		<pre style="background: #FFF; border: 1px solid #CCC; padding: 10px; margin: 0 auto;">
			<?php
				echo '<div style="margin-left=0;">';
				echo wp_json_encode( $sent_data	, JSON_PRETTY_PRINT );
				echo '</div>';
			?>
		</pre>
	</div>
</fieldset>
