<?php

/**
 * Implements the "Money" pattern.
 * 
 * @package geoform
 * @subpackage model
 */
class GeoLocation extends Location implements CompositeDBField {

	/**
	 * @var string $getAddress()
	 */
	protected $address;
	
	/**
	 * @param array
	 */
	static $composite_db = array(
		"Address" => "Varchar(255)"
	);
	
	public function __construct($name = null) {
		parent::__construct($name);
	}
	
	public function compositeDatabaseFields() {
		return array_merge(self::$composite_db, parent::$composite_db);
	}

	public function requireField() {
		$fields = $this->compositeDatabaseFields();
		if($fields) foreach($fields as $name => $type){
			DB::requireField($this->tableName, $this->name.$name, $type);
		}
	}

	public function writeToManipulation(&$manipulation) {
		if($this->getAddress()) {
			$manipulation['fields'][$this->name.'Address'] = $this->prepValueForDB($this->getAddress());
		} else {
			$manipulation['fields'][$this->name.'Address'] = DBField::create_field('Varchar', $this->getAddress())->nullValue();
		}
                parent::writeToManipulation($manipulation);
	}

	public function addToQuery(&$query) {
		parent::addToQuery($query);
		$query->selectField(sprintf('"%sAddress"', $this->name));
	}

	public function setValue($value, $record = null, $markChanged = true) {
		// TODO @kaspiCZ refactor to handle only additional values and utilize parent::setValue
		$address = $this->nullValue();
		$lat = $this->nullValue();
		$lng = $this->nullValue();

		if ($value instanceof GeoLocation && $value->exists()) {
			$address = $value->getAddress();
			$lat = $value->getLatitude();
			$lng = $value->getLongditude();
		} else if(is_null($value) && array_key_exists($this->name . 'Address', $record)
			&& array_key_exists($this->name . 'Latitude', $record)
			&& array_key_exists($this->name . 'Longditude', $record)
			&& is_string($record[$this->name . 'Address'])
			&& is_numeric($record[$this->name . 'Latitude'])
			&& is_numeric($record[$this->name . 'Longditude'])
		) {
			$address = $record[$this->name . 'Address'];
			$lat = $record[$this->name . 'Latitude'];
			$lng = $record[$this->name . 'Longditude'];
		} else if (is_array($value)) {
			if (array_key_exists('Address', $value)) {
				$address = $value['Address'];
			}
			if (array_key_exists('Latitude', $value)) {
				$lat = $value['Latitude'];
			}
			if (array_key_exists('Longditude', $value)) {
				$lng = $value['Longditude'];
			}
		}

		$this->setAddress($address, $markChanged);
		$this->setLatitude($lat, $markChanged);
		$this->setLongditude($lng, $markChanged);

		if($markChanged) $this->isChanged = true;
	}

	/**
	 * @return string
	 */
	public function Nice($size = 400) {
		$size = $size.'x'.$size;
		$loc = $this->latitude.",".$this->longditude;
		$marker = 'color:blue%7C'.$loc;
		$imageurl = "https://maps.googleapis.com/maps/api/staticmap?center=".$loc."&size=".$size."&language=".i18n::get_tinymce_lang()."&markers=".$marker."&maptype=roadmap&zoom=14";
		return '<img src="'.$imageurl.'" />';
	}

	/**
	 * @return string
	 */
	public function getAddress() {
		return $this->address;
	}
	
	/**
	 * @param string
	 */
	public function setAddress($address, $markChanged = true) {
		$this->address = $address;
		if($markChanged) $this->isChanged = true;
	}
	
	/**
	 * @return boolean
	 */
	public function exists() {
		return ($this->getAddress() && parent::exists());
	}
	
	/**
	 * Returns a CompositeField instance used as a default
	 * for form scaffolding.
	 *
	 * Used by {@link SearchContext}, {@link ModelAdmin}, {@link DataObject::scaffoldFormFields()}
	 * 
	 * @param string $title Optional. Localized title of the generated instance
	 * @return FormField
	 */
	function scaffoldFormField($title = null) {
		$field = new GeoLocationField($this->name);
		$field->setLocale($this->getLocale());
		
		return $field;
	}
	
	/**
	 * 
	 */
	public function __toString() {
		return (string)$this->getAddress();
	}
}
