<?php
/*
 * Wildcard Helper Classes
 * PortableObject Class Structure
 */

/*
 * interface PortableObjectInterface
 *
 * provides a standard interface for object import/export
 */
interface PortableObjectInterface
{
	public function export($options = '');
	public function import($xml);
	public function build_row();
}

/*
 * abstract class PortableObject
 *
 * provides functionality to import and export any StorableObject as an XML file and to output a row to be included in a collection exported by an outside function
 */
abstract class PortableObject extends StorableObject implements PortableObjectInterface
{
	/*
	 * public function export()
	 *
	 * provides export functionality for any StorableObject
	 *
	 * @param - $options 	- (array) basic export options:
	 *									-	['charset'] def: MyBB default/UTF-8
	 *									-	['version'] def: 2.0
	 *									-	['website'] def: mine :p
	 *									-	['filename'] def: a unique filename built from either the
	 * 																name, title or id, whichever is available
	 */
	public function export($options = '')
	{
		if(!$this->table_name || !$this->id)
		{
			return false;
		}

		$row = $this->build_row();
		$id = (int) $this->id;

		if(!$row)
		{
			return false;
		}

		$name = $this->get_clean_identifier();
		$default_values = array
			(
				"charset" => 'UTF-8',
				"version" => '2.0',
				"website" => 'http://www.rantcentralforums.com',
				"filename" => "{$this->table_name}_{$name}-backup.xml"
			);

		// try to get MyBB default charset
		global $lang;
		if(isset($lang->settings['charset']))
		{
			$default_values['charset'] = $lang->settings['charset'];
		}

		if(is_array($options) && !empty($options))
		{
			foreach($default_values as $key => $value)
			{
				if(!isset($options[$key]) || !$options[$key])
				{
					$options[$key] = $value;
				}
			}
		}
		else
		{
			$options = $default_values;
		}

		$xml = <<<EOF
<?xml version="1.0" encoding="{$options['charset']}"?>
<{$this->table_name} version="{$options['version']}" xmlns="{$options['website']}">
<{$this->table_name}_{$id}>
{$row}	</{$this->table_name}_{$id}>
</{$this->table_name}>
EOF;
		// send out headers (opens a save dialogue)
		header("Content-Disposition: attachment; filename={$options['filename']}");
		header('Content-Type: application/xml');
		header('Content-Length: ' . strlen($xml));
		header('Pragma: no-cache');
		header('Expires: 0');
		echo $xml;
		return true;
	}

	/*
	 * public function import()
	 *
	 * @param - $xml - (string) the contents of the XML file to be imported
	*/
	public function import($xml)
	{
		if($xml)
		{
			require_once MYBB_ROOT . 'inc/class_xml.php';
			$parser = new XMLParser($xml);
			$tree = $parser->get_tree();

			// only doing a single backup, fail if multi detected
			if(is_array($tree) && is_array($tree[$this->table_name]))
			{
				foreach($tree[$this->table_name] as $property => $this_entry)
				{
					// skip the info
					if(in_array($property, array('tag', 'value', 'attributes')))
					{
						continue;
					}

					// if there is data
					if(is_array($this_entry) && !empty($this_entry))
					{
						foreach($this_entry as $key => $value)
						{
							// skip the info
							if(in_array($key, array('tag', 'value')))
							{
								continue;
							}

							// get the field name from the array key
							$key_array = explode('-', $key);
							$newkey = $key_array[0];

							// is it a valid property name for this object?
							if(property_exists($this, $newkey))
							{
								// then store it
								$this->$newkey = $value['value'];
							}
						}
					}
				}
				return true;
			}
		}
		return false;
	}

	/*
	 * public function build_row()
	 *
	 * build a single row of XML markup for this object
	 */
	public function build_row()
	{
		// object must have been saved (it exists in the db) in order to be exported
		if($this->table_name && $this->id)
		{
			$row = '';
			$id = (int) $this->id;
			foreach($this as $property => $value)
			{
				// skip inherited properties
				if(in_array($property, $this->no_store))
				{
					continue;
				}
				$row .= <<<EOF
	<{$property}-{$id}><![CDATA[{$value}]]></{$property}-{$id}>

EOF;
			}
			return $row;
		}
		return false;
	}

	/*
	 * get_clean_identifier()
	 *
	 * returns the name, title or ID (first available-- in that order) to be used as a unique identifier
	 */
	public function get_clean_identifier()
	{
		if(property_exists($this, 'name') && trim($this->name))
		{
			$name = $this->name;
		}
		else if(property_exists($this, 'title') && trim($this->title))
		{
			$name = $this->title;
		}

		// using a string, clean it
		if($name)
		{
			// clean and return
			$find = array
				(
					"#(\s)+#s",
					"#[^\w_]#is"
				);
			$replace = array
				(
					'_',
					''
				);
			return preg_replace($find, '', strtolower(trim($name)));
		}
		// no name or title, return ID (all Storables have an ID)
		return (int) $this->id;
	}
}

?>
