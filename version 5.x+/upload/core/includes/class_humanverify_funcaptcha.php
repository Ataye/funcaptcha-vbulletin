<?php

if (!isset($GLOBALS['vbulletin']->db)) {
    exit;
}

global $vbulletin;

define('FUNCAPTCHA_PUBLIC_KEY', $vbulletin->options["funcaptcha_publickey"]);
define('FUNCAPTCHA_PRIVATE_KEY', $vbulletin->options["funcaptcha_privatekey"]);
define('FUNCAPTCHA_SECURITY_LEVEL', $vbulletin->options["funcaptcha_security"]);
define('FUNCAPTCHA_LIGHTBOX', $vbulletin->options["funcaptcha_lightbox"]);
define('FUNCAPTCHA_THEME', $vbulletin->options["funcaptcha_theme"]);
define('FUNCAPTCHA_PROXY', $vbulletin->options["funcaptcha_proxy"]);
define('FUNCAPTCHA_JSFALLBACK', $vbulletin->options["funcaptcha_jsfallback"]);
define('FUNCAPTCHA_NEWPOSTS', $vbulletin->options["funcaptcha_newpost"]);
define('FUNCAPTCHA_DEFINE_POSTS', $vbulletin->userinfo['posts']);
define('FUNCAPTCHA_USERID', $vbulletin->userinfo['userid']);

require_once('funcaptcha.php');

/**
 * FunCaptcha class for vbulletin (funcaptcha.co)
 *
 */
class vB_HumanVerify_FunCaptcha extends vB_HumanVerify_Abstract {

    /**
     * Constructor
     *
     * @return	void
     */
    function vB_HumanVerify_FunCaptcha(&$registry) {
        parent::vB_HumanVerify_Abstract($registry);
    }

    /**
     * Verify is supplied token/reponse is valid
     *
     * 	@param	array	Values given by user 'input' and 'hash'
     *
     * @return	bool
     */
    function verify_token($input) {
        
            
        if(FUNCAPTCHA_USERID > 0 && FUNCAPTCHA_DEFINE_POSTS >= FUNCAPTCHA_NEWPOSTS) {
            return true;
        }
        
        $funcaptcha = new FUNCAPTCHA();
        $funcaptcha->setProxy(FUNCAPTCHA_PROXY);
        $funcaptcha->setTheme(FUNCAPTCHA_THEME);
        $funcaptcha->setNoJSFallback(FUNCAPTCHA_JSFALLBACK);
        $score = $funcaptcha->checkResult(FUNCAPTCHA_PRIVATE_KEY);

        if ($score) {
            return true;
        } else {
            $this->error = 'funcaptcha_unverfied';
            return false;
        }
    }

    /**
     * Returns the FunCaptcha HTML
     *
     * @param	string	Passed to template
     *
     * @return 	string	HTML to output
     *
     */
    function output_token($var_prefix = 'humanverify') {
        $funcaptcha = new FUNCAPTCHA();
        $funcaptcha->setSecurityLevel(FUNCAPTCHA_SECURITY_LEVEL);
        $funcaptcha->setLightboxMode(FUNCAPTCHA_LIGHTBOX);
        $funcaptcha->setProxy(FUNCAPTCHA_PROXY);
        $funcaptcha->setTheme(FUNCAPTCHA_THEME);
        $funcaptcha->setNoJSFallback(FUNCAPTCHA_JSFALLBACK);

        //only show HTML/label if not lightbox mode.
        if (FUNCAPTCHA_LIGHTBOX) {
            $output = $funcaptcha->getFunCaptcha(FUNCAPTCHA_PUBLIC_KEY);
        } else {
            $output = "<div class=\"blockrow\"><input type=hidden value='1' id='humanverify' name='humanverify' /><div class=\"group\"><li>";
            $output = $output . "<label>Verification:</label>";
            $output = $output . $funcaptcha->getFunCaptcha(FUNCAPTCHA_PUBLIC_KEY);
            $output = $output . "</li></div></div>";
        }

        return $output;
    }

}