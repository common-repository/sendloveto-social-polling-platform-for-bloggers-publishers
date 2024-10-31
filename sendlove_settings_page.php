<div class="wrap">
  <h2><?php print SENDLOVE_PLUGIN_NAME ." ". __('Settings', SENDLOVE_I18N_DOMAIN); ?></h2>

  <?php if ( isset( $_REQUEST['settings-updated'] ) && false !== $_REQUEST['settings-updated'] ) : ?>
      <div id="message" class="updated fade"><p><strong><?php _e( 'Your changes have been saved.' ); ?></strong></p></div>
  <?php endif; ?>


  <form method="post" action="options.php">
      <?php
          settings_fields( 'sendlove-settings-group' );
      ?>
      <h3>New to SendLove.to?</h3>
      <p>Register for your <b>free account</b> by signing up for the <a href="http://SendLove.to/">Publisher Plugin at SendLove.to</a>.  Once you've registered, finish the process by following the steps below.</p>
      <h3>Already have a SendLove.to account?</h3>
      <p>
          <ol>
              <li>Go to the <a href="http://SendLove.to/apps/">SendLove.to Publisher Dashboard</a></li>
              <li>Click on <b>Settings</b> on the top menu bar</li>
              <li>Locate the <b>Site Shortname</b> in the "Main Settings" section</li>
              <li>Enter the value of the Site Shortname in the following field:
                  <table class="form-table">
                      <tr valign="top">
                      <th scope="row">Site Shortname:</th>
                      <td><input type="text" id="sendlove_site_short_name" name="sendlove_site_short_name" value="<?php echo get_option('sendlove_site_short_name'); ?>" /></td>
                      </tr>
                  </table>
              </li>
              <li>Press <b><?php _e('Save Changes') ?></b> below</li>
          </ol>
      </p>
      <p class="submit">
          <input type="submit" class="button-primary" value="<?php _e('Save Changes') ?>" />
      </p>

      <h3>Advanced Configuration (Optional)</h3>
      <p>You should only change this setting if you are having issues getting the plugin to work properly.  This might be necessary if you are using a non-standard template or a template which doesn't call the comments_template method.
      <div style="padding-left: 25px;">
          <table class="form-table">
              <tr valign="top">
              <th scope="row">Install Method:</th>
              <td><select name="sendlove_pulse_inject"><option value="comments"<?php echo get_option('sendlove_pulse_inject') == "comments" ? "selected='selected'" : ""; ?>>Standard (recommended)</option><option value="content" <?php echo get_option('sendlove_pulse_inject') == "content" ? "selected='selected'" : ""; ?>>Alternate (if "Standard" doesn't work)</option></select></td>
              </tr>
          </table>
      </div>

      <p class="submit">
          <input type="submit" class="button-primary" value="<?php _e('Save Changes') ?>" />
      </p>
      <script type="text/javascript">
          jQuery(document).ready( function () {
              jQuery('#sendlove_site_short_name').focus();
          });
      </script>
  </form>
</div>