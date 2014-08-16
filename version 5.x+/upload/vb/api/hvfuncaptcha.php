<?php
if ( !class_exists('FUNCAPTCHA') ) {
	include_once(dirname(__FILE__).'/../../includes/funcaptcha.php');
}

class vB_Api_Hvfuncaptcha extends vB_Api
{
	public function fetchHvfuncaptcha(){

            $vboptions = vB::getDatastore()->getValue('options');
            global $vbulletin;
            
            if($vbulletin->userinfo['userid'] == 0 || ($vboptions["funcaptcha_newpost"] > 0 && $vbulletin->userinfo['posts'] < $vboptions["funcaptcha_newpost"])) {
            
                $funcaptcha = new FUNCAPTCHA();
                $funcaptcha->setSecurityLevel($vboptions["funcaptcha_security"]);
                $funcaptcha->setLightboxMode($vboptions["funcaptcha_lightbox"]);
                $funcaptcha->setProxy($vboptions["funcaptcha_proxy"]);
                $funcaptcha->setTheme($vboptions["funcaptcha_theme"]);
                $funcaptcha->setNoJSFallback($vboptions["funcaptcha_jsfallback"]);

                //only show HTML/label if not lightbox mode.
                if (FUNCAPTCHA_LIGHTBOX) {
                        $output = $funcaptcha->getFunCaptcha($vboptions["funcaptcha_publickey"]);
                } else {
                        $output = "<div class=\"blockrow\"><input type=hidden value='1' id='humanverify' name='humanverify' /><div class=\"group\"><li>";
                        $output = $output . "<label>Verification:</label>";
                        $output = $output . $funcaptcha->getFunCaptcha($vboptions["funcaptcha_publickey"]);
                        $output = $output . "</li></div></div>";
                }

                return $output;
            
            }
	}
}
