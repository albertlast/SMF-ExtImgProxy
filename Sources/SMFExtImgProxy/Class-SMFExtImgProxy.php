<?php

/**
* @package manifest file for SMF-Benchmark
* @version 1.0
* @author albertlast (http://www.simplemachines.org/community/index.php?action=profile;u=226111)
* @copyright Copyright (c) 2018
* @license BSD 3-Clause License
*/

/*
 * BSD 3-Clause License
 * 
 * Copyright (c) 2018, albertlast
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 *
 * * Redistributions of source code must retain the above copyright notice, this
 *   list of conditions and the following disclaimer.
 * 
 * * Redistributions in binary form must reproduce the above copyright notice,
 *   this list of conditions and the following disclaimer in the documentation
 *   and/or other materials provided with the distribution.
 *
 * * Neither the name of the copyright holder nor the names of its
 *   contributors may be used to endorse or promote products derived from
 *   this software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
 * AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
 * IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
 * DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE
 * FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL
 * DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR
 * SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
 * CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY,
 * OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 */
 
if (!defined('SMF'))
	die('Hacking attempt...');

class SMFExtImgProxy {

	public static function addManageMaintenancePanel(&$subActions)
	{
		global $context;
		
		loadTemplate('SMFExtImgProxy');

		//$context[$context['admin_menu_name']]['tab_data']['tabs']['smf_extimgproxy'] = array();

		$subActions['smf_extimgproxy'] = 'SMFExtImgProxy::MaintainExtimgproxy';
	}

	public static function addAdminPanel(&$areas)
	{
		global $txt;
		
		loadLanguage('SMFExtImgProxy');

		$areas['config']['areas']['modsettings']['subsections'] =	array_merge(
			$areas['config']['areas']['modsettings']['subsections'] ,
			[
				'smf_extimgproxy' => array($txt['maintain_sub_extimgproxy'])
			]
		);
	}

	/**
	 *Dummy Function for MaintainExtimgproxy
	 */
	public static function MaintainExtimgproxy()
	{
		global $txt, $scripturl, $context, $config_vars, $image_proxy_enabled, $eip_url, $eip_mode, $sourcedir;

		if (empty($eip_url))
			$eip_url = '';
		if (empty($eip_mode))
			$eip_mode = 0;

		if (isset($_REQUEST['eip_url']))
			$eip_url = $_REQUEST['eip_url'];
		if (isset($_REQUEST['eip_mode']))
			$eip_mode = (int) $_REQUEST['eip_mode'];

		$config_vars = array();

		// Mode Select
		$config_vars[] = array(
			'type' => 'select',
			'name' => 'eip_mode',
			'data' => [
				['0', $txt['eip_internal']], // smf default proxy
				['1',$txt['eip_eexternal']],	 // easy external proxy proxy_url + img_url
			],
			'value' => $eip_mode,
			'label' => $txt['eip_mode'],
			'javascript' => '',
			'help' => 'eip_mode_help',
			'disabled' => empty($image_proxy_enabled),
			'invalid' => false,
			'preinput' => '',
		);

		// Proxy URL
		$config_vars[] = array(
			'type' => 'input',
			'name' => 'eip_url',
			'value' => $eip_url,
			'label' => $txt['eip_url'],
			'javascript' => '',
			'help' => '',
			'disabled' => empty($image_proxy_enabled) || empty($eip_mode),
			'invalid' => false,
			'preinput' => '',
			'size' => 30,
		);

		// Exp. Url
		$config_vars[] = array(
			'type' => 'text',
			'name' => 'exp',
			'value' => get_proxied_url('http://www.test.url/image/kitty.jpg'),
			'label' => $txt['eip_exp'],
			'javascript' => '',
			'help' => '',
			'disabled' => false,
			'invalid' => false,
			'preinput' => '',
			'size' => 60,
		);

		if (empty($context['settings_post_javascript']))
			$context['settings_post_javascript'] = '';
		$context['settings_post_javascript'] .= '$("#exp").prop("readonly", true);';

		$context['post_url'] = $scripturl . '?action=admin;area=modsettings;save;sa=smf_extimgproxy';
		$context['settings_title'] = $txt['maintain_sub_extimgproxy'];

		$context['config_vars'] = $config_vars;

		if (isset($_GET['save']))
		{
			$new_settings = array();
			$new_settings['eip_mode'] = (int) $eip_mode;
			$new_settings['eip_url'] = '\'' . addcslashes($eip_url, '\'\\') . '\'';
			// Save the relevant settings in the Settings.php file.
			require_once($sourcedir . '/Subs-Admin.php');
			updateSettingsFile($new_settings);
		}

		return $config_vars;
	}

	public static function generatedProxyUrl($url)
	{
		global $eip_mode, $eip_url, $image_proxy_enabled, $image_proxy_secret, $boardurl;

		if (empty($eip_mode))
			$eip_mode = 0;

		if ($eip_mode === 0)
		{
			return strtr($boardurl, array('http://' => 'https://')) . '/proxy.php?request=' . urlencode($url) . '&hash=' . md5($url . $image_proxy_secret);
		}
		elseif ($eip_mode === 1)
			return $eip_url . $url;
	}
	
	public static function generatedProxyUrlHook($url, &$url_out)
	{
		global $eip_mode;
		
		if ($eip_mode !== 0)
			$url_out = SMFExtImgProxy::generatedProxyUrl($url);
	}

	public static function loadLanguage()
	{
		loadLanguage('SMFExtImgProxy');
	}
}
