<?php
/*======================================================================*\
|| #################################################################### ||
|| # vBulletin 5.1.2 Patch Level 3 - Licence Number VBFA84B50C
|| # ---------------------------------------------------------------- # ||
|| # Copyright �2000-2014 vBulletin Solutions Inc. All Rights Reserved. ||
|| # This file may not be redistributed in whole or significant part. # ||
|| # ---------------- VBULLETIN IS NOT FREE SOFTWARE ---------------- # ||
|| # http://www.vbulletin.com | http://www.vbulletin.com/license.html # ||
|| #################################################################### ||
\*======================================================================*/

// ######################## SET PHP ENVIRONMENT ###########################
error_reporting(E_ALL & ~E_NOTICE);

// ##################### DEFINE IMPORTANT CONSTANTS #######################
define('CVS_REVISION', '$RCSfile$ - $Revision: 77380 $');

// #################### PRE-CACHE TEMPLATES AND DATA ######################
global $phrasegroups, $specialtemplates, $vbphrase, $settingphrase;
$phrasegroups = array(
	'timezone',
	'user',
	'cpuser',
	'holiday',
	'cppermission',
	'cpoption',
	'cphome',
	'attachment_image',
	'cprofilefield', // used for the profilefield option type
);

$settingphrase = array();
$assertor = vB::getDbAssertor();
$phrases = $assertor->assertQuery('vBForum:phrase',
		array(vB_dB_Query::TYPE_KEY => vB_dB_Query::QUERY_SELECT,
			'fieldname' => 'vbsettings',
			'languageid' => array(-1, 0, LANGUAGEID),
		),
		array('field' => 'languageid', 'direction' => vB_dB_Query::SORT_ASC)
);
if ($phrases AND $phrases->valid())
{
	foreach ($phrases AS $phrase)
	{
		$settingphrase["$phrase[varname]"] = $phrase['text'];
	}
}

$specialtemplates = array();



// ########################## REQUIRE BACK-END ############################
require_once(dirname(__FILE__) . '/global.php');
require_once(DIR . '/includes/adminfunctions_options.php');

// ######################## CHECK ADMIN PERMISSIONS #######################
if (!can_administer('canadminsettings'))
{
	print_cp_no_permission();
}

// ############################# LOG ACTION ###############################
$vbulletin->input->clean_array_gpc('r', array(
	'questionid' => vB_Cleaner::TYPE_UINT,
	'answerid'   => vB_Cleaner::TYPE_UINT,
));
log_admin_action(!empty($vbulletin->GPC['questionid']) ? 'question id = ' . $vbulletin->GPC['questionid'] : '');

$vbulletin->input->clean_array_gpc('r', array(
	'varname' => vB_Cleaner::TYPE_STR,
	'dogroup' => vB_Cleaner::TYPE_STR,
));
vB::getDatastore()->getValue('banemail');
$userContext = vB::getUserContext();

// ########################################################################
// ######################### START MAIN SCRIPT ############################
// ########################################################################

print_cp_header('');

if (empty($_REQUEST['do']))
{
	$_REQUEST['do'] = 'options';
}

// ###################### Intro Screen #######################
if ($_REQUEST['do'] == 'options')
{
        if (!vB::getUserContext()->hasAdminPermission('canadminsettingsall') AND !vB::getUserContext()->hasAdminPermission('canadminsettings'))
	{
		print_cp_no_permission();
	}
	global $settingscache, $grouptitlecache;

	require_once(DIR . '/includes/adminfunctions_language.php');

	$vbulletin->input->clean_array_gpc('r', array(
		'advanced' => vB_Cleaner::TYPE_BOOL,
		'expand'   => vB_Cleaner::TYPE_BOOL,
	));

	echo '<script type="text/javascript" src="' . $vb_options['bburl']. '/clientscript/vbulletin_cpoptions_scripts.js?v=' . SIMPLE_VERSION . '"></script>';

	// display links to settinggroups and create settingscache
	$settingscache = array();

	$settings = vB::getDbAssertor()->assertQuery('vBForum:fetchSettingsByGroup',
		array(vB_dB_Query::TYPE_KEY => vB_dB_Query::QUERY_METHOD, 'debug' => $vb5_config['Misc']['debug'])
	);

        foreach ($settings AS $setting)
        {
                // TODO: Issue #29084 - Reenable Profile Styling
                if ('profile_customization' == $setting['grouptitle'])
                {
                        continue;
                }

                //check the permissions
                if ((!empty($setting['groupperm']) AND !$userContext->hasAdminpermission($setting['groupperm'])) OR
                        (!empty($setting['adminperm']) AND !$userContext->hasAdminpermission($setting['adminperm'])))
                {
                        continue;
                }


                $settingscache["$setting[grouptitle]"]["$setting[varname]"] = $setting;
                if ($setting['grouptitle'] != $lastgroup)
                {
                        $grouptitlecache["$setting[grouptitle]"] = $setting['grouptitle'];
                        $options["$setting[grouptitle]"] = $settingphrase["settinggroup_$setting[grouptitle]"];
                }
                $lastgroup = $setting['grouptitle'];
        }

        $altmode = 1;

        // show selected settings
        print_form_header('funcaptcha_settings', 'dooptions', false, true, 'optionsform', '90%', '', true, 'post" onsubmit="return count_errors()');
        construct_hidden_code('dogroup', $vbulletin->GPC['dogroup']);
        construct_hidden_code('advanced', $vbulletin->GPC['advanced']);

    
        print_setting_group('funcaptcha', $vbulletin->GPC['advanced']);

        print_submit_row($vbphrase['save']);
        
        echo '<div class="fc-frame" style="margin-left: auto;margin-right: auto;background: white;width: 89%;margin-top: 20px;border: ridge 4px;text-align: center;"><h3>FunCaptcha Registration</h3><p>You can register for your public and private keys below or at our <a href="http://www.funcaptcha.co/" target="_blank">website</a>.</p><iframe id="reg-fc" src="https://www.funcaptcha.co/wp-fc-register?plugin=vbulletin" scrolling="no" frameBorder="0" height="450px;" width="400px"></iframe></div>';
        ?>
        <div id="error_output" style="font: 10pt courier new"></div>
        <script type="text/javascript">
        <!--
        var error_confirmation_phrase = "<?php echo $vbphrase['error_confirmation_phrase']; ?>";
        //-->
        </script>
        <script type="text/javascript" src="<?php echo $vb_options['bburl']; ?>/clientscript/vbulletin_settings_validate.js?v=<?php echo SIMPLE_VERSION; ?>"></script>
        
        <?php

}

// ###################### Start do options #######################
if ($_POST['do'] == 'dooptions')
{
	if (!vB::getUserContext()->hasAdminPermission('canadminsettingsall') AND !vB::getUserContext()->hasAdminPermission('canadminsettings'))
	{
		print_cp_no_permission();
	}

	$vbulletin->input->clean_array_gpc('p', array(
		'setting'  => vB_Cleaner::TYPE_ARRAY,
		'advanced' => vB_Cleaner::TYPE_BOOL
	));

	if (!empty($vbulletin->GPC['setting']))
	{
		try
		{
			$save = save_settings($vbulletin->GPC['setting']);
		}
		catch (vB_Exception_Api $e)
		{
			$errors = $e->get_errors();
			print_stop_message2($errors[0]);
		}

		if ($save)
		{
			print_stop_message2('saved_settings_successfully', 'funcaptcha_settings.php',
				array('do' => 'options', 'dogroup' => $vbulletin->GPC['dogroup'], 'advanced' => $vbulletin->GPC['advanced']));
		}
		else
		{
			print_stop_message2('nothing_to_do');
		}
	}
	else
	{
		print_stop_message2('nothing_to_do');
	}

}