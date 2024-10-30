<?php
// Exit if called directly
if (!defined('ABSPATH')) {
    exit();
}

// Initialize Modules in specific order
include_once CIAM_PLUGIN_DIR . 'ciam-lang.php';

use LoginRadiusSDK\CustomerRegistration\Account\SottAPI;

if (!class_exists('CIAM_Authentication_Commonmethods')) {
    class CIAM_Authentication_Commonmethods
    {
        /*
         * class constructor
         */

        public function __construct()
        {
            add_action('init', array($this, 'init'));
        }

        /*
         * Load all required dependencies
         */

        public function init()
        {
            add_action('wp_head', array($this, 'ciam_hook_commonoptions'));
            add_action('wp_head', array($this, 'ciam_hook_loader'));
            add_action('admin_head', array($this, 'ciam_hook_commonoptions'));
            add_action('wp_head', array($this, 'birthdateonregistrationtime'));
        }

        /*
         * custom ciam form loader....
         */

        public static function ciam_hook_loader()
        {
            ?>
            <script type='text/javascript'>
                jQuery(document).ready(function () {
                    setTimeout(function () {
                        jQuery("#ciam_loading_gif").hide();
                    }, 3000);
                    loadingimg();
                });
            </script>
            <?php
            /* action for debug mode */
            do_action("ciam_debug", __FUNCTION__, func_get_args(), get_class(), "");
        }

        /*
         * show birth date on registration time....
         */

        public function birthdateonregistrationtime()
        {
            ?>
            <script type='text/javascript'>
                jQuery(document).ready(function () {
                    var lrObjectInterval1 = setInterval(function () {
                if(typeof LRObject !== 'undefined')
                {
                    clearInterval(lrObjectInterval1);
                    LRObject.$hooks.register('afterFormRender', function (actionName) {
                        if (actionName === "registration") {
                            show_birthdate_date_block();
                        }
                    });
                }
                    }, 1);
            });



            </script>
            <?php
            /* action for debug mode */
            do_action("ciam_debug", __FUNCTION__, func_get_args(), get_class(), "");
        }

        /*
         * Ciam Hook for Common Option ....
         */

        public function ciam_hook_commonoptions()
        {
            global $ciam_credentials, $ciam_setting;
           
            $verificationurl = (isset($ciam_setting['login_page_id'])) ? get_permalink($ciam_setting['login_page_id']) : '';
            $forgoturl = (isset($ciam_setting['change_password_page_id'])) ? get_permalink($ciam_setting['change_password_page_id']) : '';
            if ((!isset($ciam_credentials['apikey']) && empty($ciam_credentials['apikey'])) || (!isset($ciam_credentials['secret']) && empty($ciam_credentials['secret']))) {
                return;
            } ?>
            <script>
                var commonOptions = {};
                commonOptions.apiKey = '<?php echo $ciam_credentials['apikey']; ?>';
                commonOptions.appName = '<?php echo $ciam_credentials['sitename']; ?>';
                commonOptions.formValidationMessage = true;
                commonOptions.hashTemplate = true;
                commonOptions.forgotPasswordUrl = '<?php echo $forgoturl; ?>';
                commonOptions.resetPasswordUrl = '<?php echo $forgoturl; ?>';
               
                <?php

           
            if (isset($ciam_setting['reset-template']) && $ciam_setting['reset-template'] != '' && $ciam_setting['reset-template'] != 'default') {
                ?>
                        commonOptions.resetPasswordEmailTemplate = '<?php echo $ciam_setting['reset-template']?>';
                    <?php
            }
            if (isset($ciam_setting['account-verification-template']) && $ciam_setting['account-verification-template'] != '' && $ciam_setting['account-verification-template'] != 'default') {
                ?>
                        commonOptions.verificationEmailTemplate = '<?php echo $ciam_setting['account-verification-template']?>';
                    <?php
            }
           
            if (isset($ciam_setting['smsTemplate2FA']) && $ciam_setting['smsTemplate2FA'] != '' && $ciam_setting['smsTemplate2FA'] != 'default') {
                ?>
                        commonOptions.smsTemplate2FA = '<?php echo $ciam_setting['smsTemplate2FA']?>';
                    <?php
            }
            if (isset($ciam_setting['password-stength']) && $ciam_setting['password-stength'] == 1) {
                ?>
                    commonOptions.displayPasswordStrength = true;
                <?php
            }
            if (isset($ciam_setting['autohidetime']) && !empty($ciam_setting['autohidetime'])) {
                ?>
                    var ciamautohidetime = <?php echo (int)$ciam_setting['autohidetime']; ?>;
                <?php
            } else {
                ?>
                    var ciamautohidetime = 0;
                <?php
            }
            if (defined('WP_DEBUG') && true === WP_DEBUG) {
                ?>
                    commonOptions.debugMode = true;
                <?php
            }
           
            
            if (isset($ciam_setting['terms_conditions']) && !empty($ciam_setting['terms_conditions'])) {
                $string = $ciam_setting['terms_conditions'];
                $string = str_replace(array('<script>', '</script>'), '', $string);
                $string = trim(str_replace('"', "'", $string));
                $terms = str_replace(array("\r\n", "\r", "\n"), " ", $string); ?>
                    commonOptions.termsAndConditionHtml = "<?php echo trim($terms) ?>";
                <?php
            }
           
            try {
                //getting sott                                       
                $sottObj = new \LoginRadiusSDK\CustomerRegistration\Account\SottAPI();
                $sott_encrypt = $sottObj->generateSott('20');
                  
                if (isset($sott_encrypt->Sott) && !empty($sott_encrypt->Sott)) {
                    $sott = $sott_encrypt->Sott;
                } else {
                    $sott = '';
                } ?>
                        commonOptions.sott = '<?php echo $sott?>';
                <?php
            } catch (\LoginRadiusSDK\LoginRadiusException $e) {
                ?>
                        console.log('Internal Error Occured to get SOTT!!');
                    <?php
            } ?>
                commonOptions.verificationUrl = '<?php echo $verificationurl; ?>';    
                commonOptions.messageList =  {    
                       'SOCIAL_LOGIN_MSG' : '<?php echo SOCIAL_LOGIN_MSG; ?>',                
                       'LOGIN_BY_EMAIL_MSG' : '<?php echo LOGIN_BY_EMAIL_MSG; ?>',
                       'LOGIN_BY_USERNAME_MSG' : '<?php echo LOGIN_BY_USERNAME_MSG; ?>',
                       'LOGIN_BY_PHONE_MSG' : '<?php echo LOGIN_BY_PHONE_MSG; ?>',
                       'REGISTRATION_VERIFICATION_MSG' : '<?php echo REGISTRATION_VERIFICATION_MSG; ?>',
                       'REGISTRATION_OTP_VERIFICATION_MSG' : '<?php echo REGISTRATION_OTP_VERIFICATION_MSG; ?>',
                       'REGISTRATION_OTP_MSG' : '<?php echo REGISTRATION_OTP_MSG; ?>',
                       'REGISTRATION_SUCCESS_MSG' : '<?php echo REGISTRATION_SUCCESS_MSG; ?>',
                       'FORGOT_PASSWORD_MSG' : '<?php echo FORGOT_PASSWORD_MSG; ?>',
                       'FORGOT_PASSWORD_PHONE_MSG' : '<?php echo FORGOT_PASSWORD_PHONE_MSG; ?>',
                       'FORGOT_PHONE_OTP_VERIFICATION_MSG' : '<?php echo FORGOT_PHONE_OTP_VERIFICATION_MSG; ?>',
                       'FORGOT_PASSWORD_SUCCESS_MSG' : '<?php echo FORGOT_PASSWORD_SUCCESS_MSG; ?>',
                       'RESET_PASSWORD_MSG' : '<?php echo RESET_PASSWORD_MSG; ?>',
                       'TWO_FA_MSG' : '<?php echo TWO_FA_MSG; ?>',
                       'TWO_FA_ENABLED_MSG' : '<?php echo TWO_FA_ENABLED_MSG; ?>',
                       'TWO_FA_DISABLED_MSG' : '<?php echo TWO_FA_DISABLED_MSG; ?>',
                       'EMAIL_VERIFICATION_SUCCESS_MSG' : '<?php echo EMAIL_VERIFICATION_SUCCESS_MSG; ?>',
                       'CHANGE_PASSWORD_SUCCESS_MSG' : '<?php echo CHANGE_PASSWORD_SUCCESS_MSG; ?>'
                };              

                var tabValue = '';
                <?php
            if (isset($ciam_setting['tab_value']) && !empty($ciam_setting['tab_value'])) {
                ?>
                    var tabValue = '<?php echo $ciam_setting['tab_value']; ?>';
                <?php
            } ?>
           
               
            if (typeof LoginRadiusV2 === 'undefined') {
    	         var e = document.createElement('script');
    	         e.src = '//auth.lrcontent2.com/v2/js/LoginRadiusV2.js';
                 e.type = 'text/javascript';
                 document.getElementsByTagName("head")[0].appendChild(e);
	        }
	        var lrloadInterval = setInterval(function () {
    	        if (typeof LoginRadiusV2 != 'undefined') {
        	clearInterval(lrloadInterval);
                 LRObject = new LoginRadiusV2(commonOptions);
    	        }
	        }, 1);
                var lrObjectInterval = setInterval(function () {
                if(typeof LRObject !== 'undefined')
                {
                    clearInterval(lrObjectInterval);
                    LRObject.$hooks.register('endProcess', function (name) {
                    jQuery("#ciam_loading_gif").hide();
                });
                }
                }, 1);
                jQuery(document).ready(function () {
                <?php
                if (!is_super_admin()) {?>
                    jQuery("#email").attr('readonly', 'readonly');
                    <?php } ?>
                        });
            </script>                
            <?php
            /* action for debug mode */
            do_action("ciam_debug", __FUNCTION__, func_get_args(), get_class(), "");
        }
    }

    new CIAM_Authentication_Commonmethods();
}
