<?php

if (!isset($GLOBALS['vbulletin']->db))
{
	exit;
}

global $vbulletin;

define( 'FUNCAPTCHA_PUBLIC_KEY', $vbulletin->options["funcaptcha_publickey"]);
define( 'FUNCAPTCHA_PRIVATE_KEY', $vbulletin->options["funcaptcha_privatekey"]);
define( 'FUNCAPTCHA_SECURITY_LEVEL', $vbulletin->options["funcaptcha_security"]);
define( 'FUNCAPTCHA_LABEL', $vbulletin->options["funcaptcha_label"]);
define( 'FUNCAPTCHA_THEME', $vbulletin->options["funcaptcha_theme"]);
define( 'FUNCAPTCHA_PROXY', $vbulletin->options["funcaptcha_proxy"]);
define( 'FUNCAPTCHA_JSFALLBACK', $vbulletin->options["funcaptcha_jsfallback"]);

require_once(DIR . '/includes/funcaptcha.php');

/**
* FunCaptcha class for vbulletin (funcaptcha.com)
*
*/
class vB_HumanVerify_FunCaptcha extends vB_HumanVerify_Abstract
{
	/**
	* Constructor
	*
	* @return	void
	*/
	function vB_HumanVerify_FunCaptcha(&$registry)
	{
		parent::vB_HumanVerify_Abstract($registry);
	}

	/**
	* Verify is supplied token/reponse is valid
	*
	*	@param	array	Values given by user 'input' and 'hash'
	*
	* @return	bool
	*/
	function verify_token($input)
	{
		$funcaptcha =  new FUNCAPTCHA();
		$funcaptcha->setProxy(FUNCAPTCHA_PROXY);
		$funcaptcha->setTheme(FUNCAPTCHA_THEME);
		$funcaptcha->setNoJSFallback(FUNCAPTCHA_JSFALLBACK);
		$score =  $funcaptcha->checkResult(FUNCAPTCHA_PRIVATE_KEY);
		
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
	function output_token($var_prefix = 'humanverify')
	{
		$popup_button = null;
		$funcaptcha =  new FUNCAPTCHA();
		$funcaptcha->setSecurityLevel(FUNCAPTCHA_SECURITY_LEVEL);		
		$funcaptcha->setProxy(FUNCAPTCHA_PROXY);
		$funcaptcha->setTheme(FUNCAPTCHA_THEME);
		$funcaptcha->setNoJSFallback(FUNCAPTCHA_JSFALLBACK);

		$is_reply = false;
		$is_topic = false;
		$target_element = "";
		switch ($var_prefix) {
			case "editor_reply" :
				$is_reply = true;
			break;
			case "editor_thread" :
				$is_topic = true;
			break;
		}


		// If a topic or reply FC, we need to move it from the original position.
		$script = "";
		if ($is_topic || $is_reply) {
        	$script =   "<script type='text/javascript'>
                    var moved = false;
                    // This ensures the code is executed in the right order
                    if (!moved) {
                        rearrange_form_elements();
                    } else {
                        setTimeout('rearrange_form_elements()', 1000);
                    }
                    function rearrange_form_elements() {
                        var target = document.getElementById('funcaptcha-wrapper');
                        if (target != null && document.getElementsByName('vbform')[0] != null) {
                            target.parentNode.removeChild(target);
                            document.getElementsByName('vbform')[0].appendChild(target);
                        }
                    }
                </script>";
        }
		
		//only show HTML/label if not lightbox mode.
		$output = "<div class=\"blockrow\"><input type=hidden value='1' id='humanverify' name='humanverify' /><div class=\"group\"><li>";
		$output = $output . "<div id='funcaptcha-wrapper'>";
		if (FUNCAPTCHA_LABEL && FUNCAPTCHA_LABEL != ""){
            $output = $output . "<label>".FUNCAPTCHA_LABEL."</label>";
        }
		$output = $output . $funcaptcha->getFunCaptcha(FUNCAPTCHA_PUBLIC_KEY);
		$output = $output . "</li></div></div></div>";
		$output = $output . $script;
		return $output;
	}
}
?>
