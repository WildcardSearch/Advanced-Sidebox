<?php
/*
 * Wildcard Helper Classes
 * PortableObject Class Structure
 */

/**
 * provides functionality to import and export any StorableObject as an
 * XML file and to output a row to be included in a collection exported by
 * an outside function
 */
abstract class PortableObject010102 extends StorableObject010001 implements PortableObjectInterface010100
{
	/**
	 * provides export functionality for any StorableObject
	 *
	 * @param  array basic export options
	 * @return void
	 */
	public function export($options = '', $return = false)
	{
		if (!$this->tableName ||
			!$this->id) {
			return false;
		}

		$row = $this->buildRow();
		$id = (int) $this->id;

		if (!$row) {
			return false;
		}

		$options = $this->buildOptions($options);
		$xml = $this->buildXml($row, $options);

		if ($return === true) {
			return $xml;
		}

		$this->output($xml, $options);

		return true;
	}

	/**
	 * import an object from XML
	 *
	 * @param  string the contents of the XML file to be imported
	 * @return bool success/fail
	*/
	public function import($xml)
	{
		$tree = $this->getTree($xml);
		if (!is_array($tree) ||
			!is_array($tree[$this->tableName])) {
			return false;
		}

		foreach ($tree[$this->tableName] as $property => $entry) {
			// skip the info
			if (in_array($property, array('tag', 'value', 'attributes')) ||
				!is_array($entry) ||
				empty($entry)) {
				continue;
			}

			foreach ($entry as $key => $value) {
				// skip the info
				if (in_array($key, array('tag', 'value'))) {
					continue;
				}

				// get the field name from the array key
				$newKey = explode('-', $key);
				$newKey = $newKey[0];

				// is it a valid property name for this object?
				if (property_exists($this, $newKey)) {
					// then store it
					$this->$newKey = $value['value'];
				}
			}
		}
		return true;
	}

	public function output($xml, $options = '')
	{
		// send out headers (opens a save dialogue)
		header("Content-Disposition: attachment; filename={$options['filename']}");
		header('Content-Type: application/xml');
		header('Content-Length: ' . strlen($xml));
		header('Pragma: no-cache');
		header('Expires: 0');
		echo <<<EOF
{$xml}
EOF;
	}

	public function buildOptions($options = '')
	{
		global $lang;

		$name = $this->getCleanIdentifier();
		$defaultValues = array(
			"charset" => 'UTF-8',
			"version" => '1.0',
			"website" => 'http://www.rantcentralforums.com',
			"filename" => "{$this->tableName}_{$name}-backup.xml",
		);

		// try to get MyBB default charset
		if (isset($lang->settings['charset'])) {
			$defaultValues['charset'] = $lang->settings['charset'];
		}

		if (is_array($options) &&
			!empty($options)) {
			foreach ($defaultValues as $key => $value) {
				if (!isset($options[$key]) ||
					!$options[$key]) {
					$options[$key] = $value;
				}
			}
		} else {
			$options = $defaultValues;
		}
		return $options;
	}

	public function buildXml($rows, $options = '')
	{
		return <<<EOF
<?xml version="1.0" encoding="{$options['charset']}"?>
<{$this->tableName} version="{$options['version']}" xmlns="{$options['website']}">
<{$this->tableName}_{$this->id}>
{$rows}
</{$this->tableName}_{$this->id}>
</{$this->tableName}>
EOF;
	}

	/**
	 * build a single row of XML markup for this object
	 *
	 * @return string|bool the XML markup or false on fail
	 */
	public function buildRow()
	{
		// object must have been saved (it exists in the db) in order to be exported
		if (!$this->tableName ||
			!$this->id) {
			return false;
		}

		$row = '';
		$id = (int) $this->id;
		foreach ($this as $property => $value) {
			// skip inherited properties
			if (in_array($property, $this->noStore)) {
				continue;
			}
			$row .= <<<EOF
<{$property}-{$id}><![CDATA[{$value}]]></{$property}-{$id}>

EOF;
		}
		return $row;
	}

	/**
	 * process the XML
	 *
	 * @param  string
	 * @return array|bool
	 */
	protected function getTree($xml)
	{
		if (!$xml) {
			return false;
		}

		require_once MYBB_ROOT . 'inc/class_xml.php';
		$parser = new XMLParser($xml);
		return $parser->get_tree();
	}

	/**
	 * returns the name, title or id to be used as a unique identifier
	 *
	 * @access private
	 * @return string the identifier
	 */
	protected function getCleanIdentifier()
	{
		if (property_exists($this, 'name') &&
			trim($this->name)) {
			$name = $this->name;
		} else if (property_exists($this, 'title') &&
			trim($this->title)) {
			$name = $this->title;
		}

		if (!$name) {
			return (int) $this->id;
		}

		// clean and return
		$find = array(
			"#(\s)+#s",
			"#[^\w_]#is"
		);
		$replace = array(
			'_',
			''
		);
		return preg_replace($find, $replace, strtolower(trim($name)));
	}
}