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

                    //detect if ID btnAlertDialogOK is clicked. if it is, we need to chekc if the message is the
                    //same as our verification error text. if so, trigger reload.

                </script>";

                // var_dump($vboptions);

                //only show HTML/label if not lightbox mode.
                $output = $output . '<div class="form_row form-row-funcaptcha">';
                if ($vboptions["funcaptcha_label"] && $vboptions["funcaptcha_label"] != "") {
                    $output = $output . "<label class='label_column'>".$vboptions["funcaptcha_label"]."</label>";
                }
                $output = $output . "<div class='field_column'>";
                $output = $output . $funcaptcha->getFunCaptcha($vboptions["funcaptcha_publickey"]);
                $output = $output . "</div></div>";
                // $output = $output . $script;
                return $output;
            
            }
    }
}
