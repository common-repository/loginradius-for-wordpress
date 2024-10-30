<?php

// Exit if called directly

if (!defined('ABSPATH')) {
    exit();
}

use LoginRadiusSDK\Utility\Functions;

/**

 * The activation settings class.

 */

if (!class_exists('ciam_authentication_settings')) {
    class ciam_authentication_settings
    {



        /**

         * generate ciam page selection option

         *

         * @param type $pages

         * @param type $settings

         * @param type $name

         * @return string

         */

        private function select_field($pages, $settings, $name)
        {
            $output = '<select class="ciam-row-field" name="ciam_authentication_settings[' . $name . ']">';
            $output .= '<option value="">' . __(' Select Page ', 'ciam-plugin-slug') . '</option>';
           
            foreach ($pages as $page) {
                $select_page = '';
                if (isset($settings[$name]) && $page->ID == $settings[$name]) {
                    $select_page = ' selected="selected"';
                }
                $output .= '<option value="' . $page->ID . '" ' . $select_page . '>' . $page->post_title . '</option>';
            }

            $output .= '</select>';

            /* action for debug mode */

            do_action("ciam_debug", __FUNCTION__, func_get_args(), get_class(), $output);
            return $output;
        }
        
        private function checkbox($settings, $name, $class)
        {
            $output = '<input type="checkbox" name="ciam_authentication_settings['.$name.']" value="1" ';
            if (isset($settings[$name]) && ($settings[$name] == '1' || $settings[$name] == 'username')) {
                $output .= 'checked class="'.$class.'" id="ciam-'.$name .'"/>';
            } else {
                $output .= ' class="'.$class.'" id="ciam-'.$name.'"/>';
            }
            return $output;
        }

        private function select_template($template_array, $settings, $name)
        {
            $output = '<select class="ciam-row-field" name="ciam_authentication_settings[' . $name . ']" id="ciam_login_page_id">';
            $output .= '<option value="">' . __(' Select Template ', 'ciam-plugin-slug') . '</option>';
             
            if (!in_array('default', $template_array) || empty($template_array)) {
                array_push($template_array, 'default');
            }

            foreach ($template_array as $template) {
                $select_template = '';
                if (isset($settings[$name]) && !empty($settings[$name]) && $template == $settings[$name]) {
                    $select_template = ' selected="selected"';
                }
                $output .= '<option value="' . $template . '" ' . $select_template . '>' . $template . '</option>';
            }
            $output .= '</select>';
            return $output;
        }

        /**
        * Encrypt and decrypt
        *
        * @param string $string string to be encrypted/decrypted
        * @param string $action what to do with this? e for encrypt, d for decrypt
        */
     
        public function encrypt_and_decrypt($string, $secretKey, $secretIv, $action)
        {
            // you may change these values to your own
            $secret_key = $secretKey;
            $secret_iv = $secretIv;
            $output = false;
            $encrypt_method = "AES-256-CBC";
            $key = hash('sha256', $secret_key);
            $iv = substr(hash('sha256', $secret_iv), 0, 16);
            if ($action == 'e') {
                $output = base64_encode(openssl_encrypt($string, $encrypt_method, $key, 0, $iv));
            } elseif ($action == 'd') {
                $output = openssl_decrypt(base64_decode($string), $encrypt_method, $key, 0, $iv);
            }
            return $output;
        }

        /*

         * This function will render the HTML.....

         */

        public function render_options_page($args)
        {
            global $ciam_setting, $ciam_credentials;
            $decryt_secret_key = $this->encrypt_and_decrypt($ciam_credentials['secret'], $ciam_credentials['apikey'], $ciam_credentials['apikey'], 'd');

            try {
                $query_array = [
                        'apikey' => $ciam_credentials['apikey'],
                        'apisecret' => $decryt_secret_key,
                      ];

                $url = "https://config.lrcontent.com/ciam/appInfo/templates";
                $templates = Functions::apiClient($url, $query_array);
            } catch (LoginRadiusException $e) {
                $currentErrorResponse = "Something went wrong3: " . $e->getErrorResponse()->description;
                add_settings_error('ciam_authentication_settings', esc_attr('settings_updated'), $currentErrorResponse, 'error');
            }

            $pages = get_pages($args);
            $ciam_setting = get_option('Ciam_Authentication_settings'); ?>

<div class="wrap active-wrap cf">
  <header>
    <h1 class="logo"><a href="//www.loginradius.com" target="_blank">Authentication Page Configuration</a></h1>
  </header>
  <div class="cf">
    <form action="options.php" method="post">
      <?php

                        settings_fields('ciam_authentication_settings');
            settings_errors(); ?>
      <ul class="ciam-options-tab-btns">
        <li class="nav-tab ciam-active" data-tab="ciam_options_tab-1">       
          <?php _e('User Registration', 'ciam-plugin-slug') ?>
        </li>
        <li class="nav-tab" data-tab="ciam_options_tab-2">        
          <?php _e('Authentication', 'ciam-plugin-slug') ?>
        </li>
        <li class="nav-tab" data-tab="ciam_options_tab-4">   
          <?php _e('Advanced Settings', 'ciam-plugin-slug') ?>
        </li>
        <?php do_action("ciam_auth_tab_title"); ?>        
        <li class="nav-tab" data-tab="ciam_options_tab-9">
          <?php _e('Short Codes', 'ciam-plugin-slug') ?>
        </li>
      </ul>
      <div id="ciam_options_tab-1" class="ciam-tab-frame ciam-active">

      <script type="text/javascript">
      jQuery('.ciam-options-tab-btns li').click(function () {
        var tab_id = jQuery(this).attr('data-tab');
        if(tab_id == 'ciam_options_tab-1'){
          jQuery( "#tab-2").remove();
          jQuery( "#tab-4" ).remove();
          jQuery( "#tab-9" ).remove();
          jQuery("#ciam_options_tab-1").append('<input type="hidden" id="tab-1" value="ciam_options_tab-1" name="ciam_authentication_settings[tab_value]">')
        }
      });
      </script>
        <div class="ciam_options_container">
          <div class="ciam-row">
            <h3>
              <?php _e('User Registration Settings', 'ciam-plugin-slug'); ?>
            </h3>
            <div>
              
              <div id="autopage-generate">
                   <input type="hidden" name="ciam_authentication_settings[ciam_autopage]">
                   <label class="active-toggle" for="ciam-ciam_autopage">
                    <?php echo $this->checkbox($ciam_setting, 'ciam_autopage', 'active-toggle'); ?>            
                    <span class="active-toggle-name">
                    <?php _e('Auto Generate Authentication Page'); ?>
                     </span>                  
                  </label>
                  <span class="ciam-tooltip" data-title="<?php _e('When enabled, the authentication pages will be generated and linked in the respective drop-downs only if the Select Page option is selected.', 'ciam-plugin-slug'); ?>"> 
                     <span class="dashicons dashicons-editor-help"></span> 
                 </span> <br/>   
                <div class="ciam-row ciam-custom-page-settings">
                  <div>
                    <label> <span class="ciam_property_title">
                      <?php _e('Login page', 'ciam-plugin-slug'); ?>
                      <span class="ciam-tooltip" data-title="<?php _e('Add login page short code from Short Code tab in selected page.', 'ciam-plugin-slug'); ?>"> 
                      <span class="dashicons dashicons-editor-help"></span> </span> </span>
                       <?php echo $this->select_field($pages, $ciam_setting, 'login_page_id'); ?></label>
                  </div>
                  <div>
                    <label> <span class="ciam_property_title">
                      <?php _e('Registration page', 'ciam-plugin-slug'); ?>
                      <span class="ciam-tooltip" data-title="<?php _e('Add registration page short code from Short Code tab in selected page.', 'ciam-plugin-slug'); ?>"> 
                      <span class="dashicons dashicons-editor-help"></span> </span> </span>
                      <?php echo $this->select_field($pages, $ciam_setting, 'registration_page_id'); ?> </label>
                  </div>
                  <div>
                    <label> <span class="ciam_property_title">
                      <?php _e('Forgot Password Page', 'ciam-plugin-slug'); ?>
                      <span class="ciam-tooltip" data-title="<?php _e('Add forgot password page short code from Short Code tab in selected page.', 'ciam-plugin-slug'); ?>">
                      <span class="dashicons dashicons-editor-help"></span> </span> </span> 
                      <?php echo $this->select_field($pages, $ciam_setting, 'lost_password_page_id'); ?></label>
                  </div>
                  <div>
                    <label> <span class="ciam_property_title">
                      <?php _e('Reset password page', 'ciam-plugin-slug'); ?>
                      <span class="ciam-tooltip" data-title="<?php _e('Add reset password page short code from Short Code tab in selected page.', 'ciam-plugin-slug'); ?>"> 
                      <span class="dashicons dashicons-editor-help"></span> </span> </span> 
                      <?php echo $this->select_field($pages, $ciam_setting, 'change_password_page_id'); ?></label>
                  </div>
                </div>
              </div>
            </div>
            <hr>
            <h3>
              <?php _e('Redirection settings after login ', 'ciam-plugin-slug'); ?>
              <span class="active-tooltip" data-title="<?php _e('This feature sets the redirection to the page where user will get redirected to post login.', 'ciam-plugin-slug'); ?>"> <span class="dashicons dashicons-editor-help"></span> </span> </h3>
            <div class="custom-radio">
              <input id="radio0" type="radio" class="loginRedirectionRadio" name="ciam_authentication_settings[after_login_redirect]" value="samepage" <?php echo (!isset($ciam_setting['after_login_redirect']) || $ciam_setting['after_login_redirect'] == 'samepage') ? 'checked' : ''; ?>/>
              <label for="radio0"> <?php _e('Redirect to the same page where the user logged in', 'ciam-plugin-slug'); ?> </label>
            </div>
            <div class="custom-radio">
              <input id="radio2" type="radio" class="loginRedirectionRadio" name="ciam_authentication_settings[after_login_redirect]" value="homepage" <?php echo (isset($ciam_setting['after_login_redirect']) && $ciam_setting['after_login_redirect'] == 'homepage') ? 'checked' : ''; ?>/>
              <label for="radio2">
              <?php _e('Redirect to the home page of your WordPress site', 'ciam-plugin-slug'); ?>
              </label> </div>
             <div class="custom-radio">
              <input id="radio3" type="radio" class="loginRedirectionRadio" name="ciam_authentication_settings[after_login_redirect]" value="dashboard" <?php echo (isset($ciam_setting['after_login_redirect']) && $ciam_setting['after_login_redirect'] == 'dashboard') ? 'checked' : ''; ?> />
              <label for="radio3">
              <?php _e('Redirect to the user\'s account dashboard', 'ciam-plugin-slug'); ?>
              </label></div>
            <div class="custom-radio">
            <input id="radio4" type="radio" class="loginRedirectionRadio custom" id="customUrl" name="ciam_authentication_settings[after_login_redirect]" value="custom"  <?php echo (isset($ciam_setting['after_login_redirect']) && $ciam_setting['after_login_redirect'] == 'custom') ? 'checked' : ''; ?>/>
            <label for="radio4">
            <?php _e('Redirect to a custom URL'); ?>
            </label>
            <div class="ciam-row" id="customRedirectUrlField">                
                <input type="text" class="ciam-row-field" id="customRedirectOther" name="ciam_authentication_settings[custom_redirect_other]" value="<?php echo (isset($ciam_setting['custom_redirect_other'])) ? $ciam_setting['custom_redirect_other'] : ''; ?>" autofill='off' autocomplete='off' >
            </div>
            </div>
          </div>
        </div>
      </div>

      <div id="ciam_options_tab-2" class="ciam-tab-frame">        
        <!-- Authentication Flow Type -->
        <script type="text/javascript">
      jQuery('.ciam-options-tab-btns li').click(function () {
        var tab_id = jQuery(this).attr('data-tab');
        if(tab_id == 'ciam_options_tab-2'){
          jQuery( "#tab-1").remove();
          jQuery( "#tab-4" ).remove();
          jQuery( "#tab-9" ).remove();
          jQuery("#ciam_options_tab-2").append('<input type="hidden" id="tab-2" value="ciam_options_tab-2" name="ciam_authentication_settings[tab_value]">')
        }
      });
      </script>
        <div class="ciam_options_container">
          <div class="ciam-row ciam-ur-shortcodes loginoptions">
                      
            <div id="emailflowdiv">
              <h3>
              <?php _e('Email Authentication'); ?>
              </h3>                  
              <?php
                if (isset($templates->EmailTemplates)) {?> 
                <div id="customemailtemplates">                 
                <div> <span class="ciam_property_title">
                  <?php _e('Reset password email template : ', 'ciam-plugin-slug'); ?>
                        <span class="ciam-tooltip" data-title="<?php _e('Select the name of Reset Password Email template which is created in the LoginRadius Dashboard', 'ciam-plugin-slug'); ?>"> 
                            <span class="dashicons dashicons-editor-help"></span>
                        </span>
                  </span>
                    <?php echo $this->select_template($templates->EmailTemplates->ResetPassword, $ciam_setting, 'reset-template');?>
                </div>
                <div> <span class="ciam_property_title">
                  <?php _e('Account verification email template : ', 'ciam-plugin-slug'); ?>
                        <span class="ciam-tooltip" data-title="<?php _e('Select the name of Account Verification Email template which is created in the LoginRadius Dashboard', 'ciam-plugin-slug'); ?>"> 
                            <span class="dashicons dashicons-editor-help"></span>
                        </span>
                  </span>
                     <?php echo $this->select_template($templates->EmailTemplates->Verification, $ciam_setting, 'account-verification-template');?>
                </div>
              </div>
                <?php
                } ?>
            </div>
          </div>
        </div>
      </div>
     
      <div id="ciam_options_tab-4" class="ciam-tab-frame">
      <script type="text/javascript">
      jQuery('.ciam-options-tab-btns li').click(function () {
        var tab_id = jQuery(this).attr('data-tab');
        if(tab_id == 'ciam_options_tab-4'){
          jQuery( "#tab-1").remove();
          jQuery( "#tab-2" ).remove();
          jQuery( "#tab-9" ).remove();
          jQuery("#ciam_options_tab-4").append('<input type="hidden" id="tab-4" value="ciam_options_tab-4" name="ciam_authentication_settings[tab_value]">')
        }
      });
      </script>
        <div class="ciam_options_container">
          <div class="ciam-row">
            <h3>
              <?php _e('Advanced Options', 'ciam-plugin-slug'); ?>
            </h3>
                          
         
            <div>                         
              <input type="hidden" name="ciam_authentication_settings[password-stength]">
              <label class="active-toggle">      
              <?php echo $this->checkbox($ciam_setting, 'password-stength', 'active-toggle'); ?>   
                <span class="active-toggle-name">
                <?php _e('Enable password strength', 'ciam-plugin-slug'); ?>
                </span>
              </label>
              <span class="ciam-tooltip tip-top" data-title="<?php _e('This feature when enabled, shows the strength bar under the password field on registration form, reset password form and change password form.', 'ciam-plugin-slug'); ?>"> 
              <span class="dashicons dashicons-editor-help"></span></span>           
            </div>
              
              
            <div class="ciam-ur-shortcodes loginoptions Notification-timeout-settings-field">
              <p class="margin-0">&nbsp;</p>
              <h3 class="ciam_property_title">
                <?php _e('Message timeout setting (in seconds)', 'ciam-plugin-slug'); ?>
                <span class="ciam-tooltip " id="autohidetime-temp" data-title="<?php _e('Enter the duration (in seconds) to hide response message.', 'ciam-plugin-slug'); ?>"> <span class="dashicons dashicons-editor-help"></span> </span> </h3>
              <label>
                <input placeholder="Time In Seconds" type="number" class="ciam-row-field custom-tooltip" name="ciam_authentication_settings[autohidetime]" id="autohidetime" value="<?php echo (isset($ciam_setting['autohidetime']) && !empty($ciam_setting['autohidetime'])) ? $ciam_setting['autohidetime'] : '' ?>" min="0"/>
              </label>
            </div><br>
               
            <div class="lr_terms_editor">
              <h3>
                <?php _e('Terms and Conditions', 'ciam-plugin-slug'); ?>
                <span class="active-tooltip" data-title="<?php _e('Enter the content which needs to be displayed on the registration form.', 'ciam-plugin-slug'); ?>"> <span class="dashicons dashicons-editor-help"></span> </span> </h3>
              <label class="wp_terms_and_condition_editor">
              <?php
              $content = isset($ciam_setting['terms_conditions']) ? $ciam_setting['terms_conditions'] : '';
            $settings = array('textarea_name' => "ciam_authentication_settings[terms_conditions]", 'textarea_rows' => 5, 'media_buttons' => false, 'tinymce' => true);
            wp_editor($content, 'terms_and_conditions', $settings); ?>               
              </label>
            </div>      
          
         
          </div>
        </div> 
      </div>
      <?php do_action("ciam_auth_tab_page"); ?>
      <div id="ciam_options_tab-9" class="ciam-tab-frame"> 
        
        <!-- Authentication Flow Type -->
        <script type="text/javascript">
      jQuery('.ciam-options-tab-btns li').click(function () {
        var tab_id = jQuery(this).attr('data-tab');   
        if(tab_id == 'ciam_options_tab-9'){
          jQuery( "#tab-1").remove();
          jQuery( "#tab-2" ).remove();
          jQuery( "#tab-4" ).remove();
          jQuery("#ciam_options_tab-9").append('<input type="hidden" id="tab-9" value="ciam_options_tab-9" name="ciam_authentication_settings[tab_value]">')
        }
      });
      </script>
        
        <div class="ciam_options_container" id="ciam-shortcodes">
          <div class="ciam-row ciam-ur-shortcodes">
            <h3>
              <?php _e('User Registration Short Codes', 'ciam-plugin-slug'); ?>
            </h3>
            <div class="ciam_shortcode_div">
              <h4>
                <?php _e('Login form', 'ciam-plugin-slug'); ?>
                <span class="ciam-tooltip tip-top" data-title="<?php _e('Copy and paste the following shortcode into a page or post to display the login form', 'ciam-plugin-slug'); ?>"> <span class="dashicons dashicons-editor-help"></span> </span> </h4>
              <textarea rows="1" onclick="this.select()" spellcheck="false" class="ciam-shortcode" readonly>[ciam_login_form]</textarea>
            </div>
            <div class="ciam_shortcode_div">
              <h4>
                <?php _e('Registration form', 'ciam-plugin-slug'); ?>
                <span class="ciam-tooltip tip-top" data-title="<?php _e('Copy and paste the following shortcode into a page or post to display the registration form', 'ciam-plugin-slug'); ?>"> <span class="dashicons dashicons-editor-help"></span> </span> </h4>
              <textarea rows="1" onclick="this.select()" spellcheck="false" class="ciam-shortcode" readonly>[ciam_registration_form]</textarea>
            </div>
            <div class="ciam_shortcode_div">
              <h4>
                <?php _e('Forgot password form', 'ciam-plugin-slug'); ?>
                <span class="ciam-tooltip tip-top" data-title="<?php _e('Copy and paste the following shortcode into a page or post to display the forgot password form', 'ciam-plugin-slug'); ?>"> <span class="dashicons dashicons-editor-help"></span> </span> </h4>
              <textarea rows="1" onclick="this.select()" spellcheck="false" class="ciam-shortcode" readonly>[ciam_forgot_form]</textarea>
            </div>
            <div class="ciam_shortcode_div">
              <h4>
                <?php _e('Reset password form', 'ciam-plugin-slug'); ?>
                <span class="ciam-tooltip tip-top" data-title="<?php _e('Copy and paste the following shortcode into a page or post to display reset password form', 'ciam-plugin-slug'); ?>"> <span class="dashicons dashicons-editor-help"></span> </span> </h4>
              <textarea rows="1" onclick="this.select()" spellcheck="false" class="ciam-shortcode" readonly>[ciam_password_form]</textarea>
            </div>
            <div class="ciam_shortcode_div">
              <h4>
                <?php _e('Default WordPress login form', 'ciam-plugin-slug'); ?>
                <span class="ciam-tooltip tip-top" data-title="<?php _e('Copy and paste the following shortcode into a page or post to display the default Wordpress login form. This can be used while configuring your site.It is independent from LR forms.', 'ciam-plugin-slug'); ?>"> <span class="dashicons dashicons-editor-help"></span> </span> </h4>
              <textarea rows="1" onclick="this.select()" spellcheck="false" class="ciam-shortcode" readonly>[ciam_wp_default_login]</textarea>
            </div>
          </div>
        </div>
      </div>
      <div style="position: relative;">
        <div class="ciam-option-disabled-hr" style="display: none;"></div>
      </div>
      <p id="savebtn">
        <?php submit_button('Save settings', 'primary', 'submit', false); ?>
      </p>
    </form>
  </div>
</div>
<?php

            /* action for debug mode */
            do_action("ciam_debug", __FUNCTION__, func_get_args(), get_class(), "");
        }
    }



    new ciam_authentication_settings();
}
