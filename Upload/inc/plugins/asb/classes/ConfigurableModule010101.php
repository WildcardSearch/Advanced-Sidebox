<?php
/*
 * Wildcard Helper Classes
 * ConfigurableModule Class Structure
 */

abstract class ConfigurableModule010101 extends ExternalModule020100 implements ConfigurableModuleInterface010000
{
	/**
	 * @var array
	 */
	protected $settings = array();

	/**
	 * @var
	 */
	public $hasSettings = false;

	/**
	 * settings builder
	 *
	 * @return bool
	 */
	static public function outputModuleSettings($module, $formContainer)
	{
		if (!$module->hasSettings) {
			return false;
		}

		$form = new Form('', '', '', false, '', true);

		foreach ($module->settings as $name => $setting) {
			$setting['name'] = $name;
			ConfigurableModule010101::buildSetting($setting, $form, $formContainer);
		}

		return true;
	}

	/**
	 * creates a single setting from an associative array
	 *
	 * @param  array
	 * @param  DefaultForm
	 * @param  DefaultFormContainer
	 * @return void
	 */
	static public function buildSetting($setting, $form, $formContainer)
	{
		$options = '';
		$type = explode("\n", $setting['optionscode']);
		$type = array_map('trim', $type);
		$elementName = "{$setting['name']}";
		$elementId = "setting_{$setting['name']}";

		$label = '<strong>'.htmlspecialchars_uni($setting['title']).'</strong>';
		$description = '<i>'.$setting['description'].'</i>';

		if ($type[0] == 'text' ||
			$type[0] == '') {
			$code = $form->generate_text_box($elementName, $setting['value'], array('id' => $elementId));
		} else if ($type[0] == 'numeric') {
			$fieldOptions = array('id' => $elementId);
			if (count($type) > 1) {
				for ($i = 1; $i < count($type); $i++) {
					$optionsexp = explode('=', $type[$i]);
					$opt = array_map('trim', $optionsexp);
					if (in_array($opt[0], array('min', 'max', 'step'))) {
						if ($opt[0] != 'step' || 
							$opt[1] != 'any') {
							$opt[1] = (float)$opt[1];
						}
						$fieldOptions[$opt[0]] = $opt[1];
					}
				}
			}
			$code = $form->generate_numeric_field($elementName, $setting['value'], $fieldOptions);
		} else if ($type[0] == 'textarea') {
			$code = $form->generate_text_area($elementName, $setting['value'], array('id' => $elementId));
		} else if ($type[0] == 'yesno') {
			$code = $form->generate_yes_no_radio($elementName, $setting['value'], true, array('id' => $elementId.'_yes', 'class' => $elementId), array('id' => $elementId.'_no', 'class' => $elementId));
		} else if ($type[0] == 'onoff') {
			$code = $form->generate_on_off_radio($elementName, $setting['value'], true, array('id' => $elementId.'_on', 'class' => $elementId), array('id' => $elementId.'_off', 'class' => $elementId));
		} else if ($type[0] == 'cpstyle') {
			$dir = @opendir(MYBB_ADMIN_DIR.'styles');

			$folders = array();
			while ($folder = readdir($dir)) {
				if ($file != '.' &&
					$file != '..' &&
					@file_exists(MYBB_ADMIN_DIR."styles/{$folder}/main.css")) {
					$folders[$folder] = ucfirst($folder);
				}
			}
			closedir($dir);
			ksort($folders);
			$code = $form->generate_select_box($elementName, $folders, $setting['value'], array('id' => $elementId));
		} else if ($type[0] == 'language') {
			$languages = $lang->get_languages();
			$code = $form->generate_select_box($elementName, $languages, $setting['value'], array('id' => $elementId));
		} else if ($type[0] == 'adminlanguage') {
			$languages = $lang->get_languages(1);
			$code = $form->generate_select_box($elementName, $languages, $setting['value'], array('id' => $elementId));
		} else if ($type[0] == 'passwordbox') {
			$code = $form->generate_password_box($elementName, $setting['value'], array('id' => $elementId));
		} else if ($type[0] == 'php') {
			$setting['optionscode'] = substr($setting['optionscode'], 3);
			eval("\$code = \"{$setting['optionscode']}\";");
		} else if ($type[0] == 'forumselect') {
			$selected = '';
			if ($setting['value'] != '' &&
				$setting['value'] != -1) {
				$selected = explode(',', (string) $setting['value']);

				foreach ($selected as &$value) {
					$value = (int)$value;
				}
				unset($value);
			}

			$forumChecked = array(
				'all' => '',
				'custom' => '',
				'none' => ''
			);

			if ($setting['value'] == -1) {
				$forumChecked['all'] = 'checked="checked"';
			} elseif($setting['value'] != '') {
				$forumChecked['custom'] = 'checked="checked"';
			} else {
				$forumChecked['none'] = 'checked="checked"';
			}

			print_selection_javascript();

			$code = "
			<dl style=\"margin-top: 0; margin-bottom: 0; width: 100%\">
				<dt><label style=\"display: block;\"><input type=\"radio\" name=\"{$elementName}\" value=\"all\" {$forumChecked['all']} class=\"{$elementId}_forums_groups_check\" onclick=\"checkAction('{$elementId}');\" style=\"vertical-align: middle;\" /> <strong>{$lang->all_forums}</strong></label></dt>
				<dt><label style=\"display: block;\"><input type=\"radio\" name=\"{$elementName}\" value=\"custom\" {$forumChecked['custom']} class=\"{$elementId}_forums_groups_check\" onclick=\"checkAction('{$elementId}');\" style=\"vertical-align: middle;\" /> <strong>{$lang->select_forums}</strong></label></dt>
				<dd style=\"margin-top: 4px;\" id=\"{$elementId}_forums_groups_custom\" class=\"{$elementId}_forums_groups\">
					<table cellpadding=\"4\">
						<tr>
							<td valign=\"top\"><small>{$lang->forums_colon}</small></td>
							<td>".$form->generate_forum_select('select['.$setting['name'].'][]', $selected, array('id' => $elementId, 'multiple' => true, 'size' => 5))."</td>
						</tr>
					</table>
				</dd>
				<dt><label style=\"display: block;\"><input type=\"radio\" name=\"{$elementName}\" value=\"none\" {$forumChecked['none']} class=\"{$elementId}_forums_groups_check\" onclick=\"checkAction('{$elementId}');\" style=\"vertical-align: middle;\" /> <strong>{$lang->none}</strong></label></dt>
			</dl>
			<script type=\"text/javascript\">
				checkAction('{$elementId}');
			</script>";
			
			$setting['optionscode'] = substr($setting['optionscode'], 3);
			eval("\$code = \"{$setting['optionscode']}\";");
		} else if ($type[0] == 'forumselectsingle') {
			$selectedValue = (int) $setting['value'];
			$code = $form->generate_forum_select($elementName, $selectedValue, array('id' => $elementId, 'main_option' => $lang->none));
		} else if ($type[0] == 'groupselect') {
			$selected = '';
			if ($setting['value'] != '' &&
				$setting['value'] != -1) {
				$selected = explode(',', (string) $setting['value']);

				foreach ($selected as &$value) {
					$value = (int)$value;
				}
				unset($value);
			}

			$groupChecked = array(
				'all' => '',
				'custom' => '',
				'none' => ''
			);

			if ($setting['value'] == -1) {
				$groupChecked['all'] = 'checked="checked"';
			} elseif ($setting['value'] != '') {
				$groupChecked['custom'] = 'checked="checked"';
			} else {
				$groupChecked['none'] = 'checked="checked"';
			}

			print_selection_javascript();

			$code = "
			<dl style=\"margin-top: 0; margin-bottom: 0; width: 100%\">
				<dt><label style=\"display: block;\"><input type=\"radio\" name=\"{$elementName}\" value=\"all\" {$groupChecked['all']} class=\"{$elementId}_forums_groups_check\" onclick=\"checkAction('{$elementId}');\" style=\"vertical-align: middle;\" /> <strong>{$lang->all_groups}</strong></label></dt>
				<dt><label style=\"display: block;\"><input type=\"radio\" name=\"{$elementName}\" value=\"custom\" {$groupChecked['custom']} class=\"{$elementId}_forums_groups_check\" onclick=\"checkAction('{$elementId}');\" style=\"vertical-align: middle;\" /> <strong>{$lang->select_groups}</strong></label></dt>
				<dd style=\"margin-top: 4px;\" id=\"{$elementId}_forums_groups_custom\" class=\"{$elementId}_forums_groups\">
					<table cellpadding=\"4\">
						<tr>
							<td valign=\"top\"><small>{$lang->groups_colon}</small></td>
							<td>".$form->generate_group_select('select['.$setting['name'].'][]', $selected, array('id' => $elementId, 'multiple' => true, 'size' => 5))."</td>
						</tr>
					</table>
				</dd>
				<dt><label style=\"display: block;\"><input type=\"radio\" name=\"{$elementName}\" value=\"none\" {$groupChecked['none']} class=\"{$elementId}_forums_groups_check\" onclick=\"checkAction('{$elementId}');\" style=\"vertical-align: middle;\" /> <strong>{$lang->none}</strong></label></dt>
			</dl>
			<script type=\"text/javascript\">
				checkAction('{$elementId}');
			</script>";
		} else if ($type[0] == 'groupselectsingle') {
			$selected = (int) $setting['value'];
			$code = $form->generate_group_select($elementName, $selected, array('id' => $elementId, 'main_option' => $lang->none));
		} else {
			$typecount = count($type);

			if ($type[0] == 'checkbox') {
				$multivalue = explode(',', $setting['value']);
			}

			for ($i=0; $i < $typecount; $i++) {
				$optionsexp = explode('=', $type[$i]);
				if (!$optionsexp[1]) {
					continue;
				}

				if ($type[0] == 'select') {
					$optionList[$optionsexp[0]] = htmlspecialchars_uni($optionsexp[1]);
				} else if ($type[0] == 'radio') {
					if ($setting['value'] == $optionsexp[0]) {
						$optionList[$i] = $form->generate_radio_button($elementName, $optionsexp[0], htmlspecialchars_uni($optionsexp[1]), array('id' => $elementId.'_'.$i, 'checked' => 1, 'class' => $elementId));
					} else {
						$optionList[$i] = $form->generate_radio_button($elementName, $optionsexp[0], htmlspecialchars_uni($optionsexp[1]), array('id' => $elementId.'_'.$i, 'class' => $elementId));
					}
				} else if($type[0] == 'checkbox') {
					if (in_array($optionsexp[0], $multivalue)) {
						$optionList[$i] = $form->generate_check_box($elementName, $optionsexp[0], htmlspecialchars_uni($optionsexp[1]), array('id' => $elementId.'_'.$i, 'checked' => 1, 'class' => $elementId));
					} else {
						$optionList[$i] = $form->generate_check_box($elementName, $optionsexp[0], htmlspecialchars_uni($optionsexp[1]), array('id' => $elementId.'_'.$i, 'class' => $elementId));
					}
				}
			}

			if ($type[0] == 'select') {
				$code = $form->generate_select_box($elementName, $optionList, $setting['value'], array('id' => $elementId));
			} else {
				$code = implode('<br />', $optionList);

				if ($type[0] == 'checkbox') {
					$code .= $form->generate_hidden_field("isvisible_{$setting['name']}", 1);
				}
			}
			$optionList = array();
		}

		$formContainer->output_row($label, $description, $code, '', array(), array('id' => 'row_'.$elementId));
	}

	/**
	 * customize load
	 *
	 * @return string the return of the module routine
	 */
	public function load($module)
	{
		if (!parent::load($module)) {
			return false;
		}

		$this->hasSettings = !empty($this->settings);
		return true;
	}

	/**
	 * output settings
	 *
	 * @return string the return of the module routine
	 */
	public function outputSettings($formContainer)
	{
		ConfigurableModule010101::outputModuleSettings($this, $formContainer);
	}
}