<?php

/**
 * PostCodeLocation
 * 
 * @package geoform
 * @subpackage model
 */
class Location extends DBField implements CompositeDBField {

	/**
	 * @var double $getLatitude()
	 */
	protected $latitude;

	/**
	 * @var double $getLongditude()
	 */
	protected $longditude;

	/**
	 * @var boolean $isChanged
	 */
	protected $isChanged = false;
	
	/**
	 * @var string $locale
	 */
	protected $locale = null;
	
	/**
	 * @param array
	 */
	static $composite_db = array(
		"Latitude" => 'Double',
		"Longditude" => 'Double'
	);
	
	public function __construct($name = null) {
		parent::__construct($name);
	}
	
	public function compositeDatabaseFields() {
		return self::$composite_db;
	}

	public function requireField() {
		$fields = $this->compositeDatabaseFields();
		if($fields) foreach($fields as $name => $type){
			DB::requireField($this->tableName, $this->name.$name, $type);
		}
	}

	public function writeToManipulation(&$manipulation) {
		if($this->getLatitude()) {
			$manipulation['fields'][$this->name.'Latitude'] = $this->getLatitude();
		} else {
			$manipulation['fields'][$this->name.'Latitude'] = DBField::create_field('Double', $this->getLatitude())->nullValue();
		}
		
		if($this->getLongditude()) {
			$manipulation['fields'][$this->name.'Longditude'] = $this->getLongditude();
		} else {
			$manipulation['fields'][$this->name.'Longditude'] = DBField::create_field('Double', $this->getLongditude())->nullValue();
		}
	}
	
	public function addToQuery(&$query) {
		parent::addToQuery($query);
		$query->selectField(sprintf('"%sLatitude"', $this->name));
		$query->selectField(sprintf('"%sLongditude"', $this->name));
	}

	public function setValue($value, $record = null, $markChanged = true) {
		// TODO @kaspiCZ refactor to handle only additional values and utilize parent::setValue
		$lat = $this->nullValue();
		$lng = $this->nullValue();
		if ($value instanceof Location && $value->exists()) {
			$lat = $value->getLatitude();
			$lng = $value->getLongditude();
		} else if(is_null($value)
			&& array_key_exists($this->name . 'Latitude', $record)
			&& array_key_exists($this->name . 'Longditude', $record)
			&& is_numeric($record[$this->name . 'Latitude'])
			&& is_numeric($record[$this->name . 'Longditude'])
		) {
			$lat = $record[$this->name . 'Latitude'];
			$lng = $record[$this->name . 'Longditude'];
		} else if (is_array($value)) {
			if (array_key_exists('Latitude', $value)) {
				$lat = $value['Latitude'];
			}
			if (array_key_exists('Longditude', $value)) {
				$lng = $value['Longditude'];
			}
		}

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
	 * @return double
	 */
	public function getLatitude() {
		return $this->latitude;
	}

	/**
	 * @param double $amount
	 */
	public function setLatitude($latitude, $markChanged = true) {
		$this->latitude = (double)$latitude;
		if($markChanged) $this->isChanged = true;
	}

	/**
	 * @return double
	 */
	public function getLongditude() {
		return $this->longditude;
	}

	/**
	 * @param double $amount
	 */
	public function setLongditude($longditude, $markChanged = true) {
		$this->longditude = (double)$longditude;
		if($markChanged) $this->isChanged = true;
	}
	
	/**
	 * @return boolean
	 */
	public function exists() {
		return (is_numeric($this->getLatitude()) && is_numeric($this->getLongditude()));
	}
	
	public function isChanged() {
		return $this->isChanged;
	}
		
	/**
	 * @param string $locale
	 */
	public function setLocale($locale) {
		$this->locale = $locale;
	}
	
	/**
	 * @return string
	 */
	public function getLocale() {
		return ($this->locale) ? $this->locale : i18n::get_locale();
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
	public function scaffoldFormField($title = null) {
		$field = new LocationField($this->name);
		$field->setLocale($this->getLocale());
		
		return $field;
	}
	
	/**
	 * 
	 */
	public function __toString() {
		return (string)$this->getLatitude().', '.(string)$this->getLongditude();
	}
        
	/**
	 * return a SQL Bounce for WHERE Clause
	 */
	public function getSQLFilter($radius, $scale = 'km'){
		// set Latitude and Longditude Columnnames
		GeoFunctions::$Latitude = $this->name.'Latitude';
		GeoFunctions::$Longditude = $this->name.'Longditude';

		return GeoFunctions::getSQLSquare($this->getLatitude(), $this->getLongditude(), $radius, $scale);
	}

	/**
	 * return the Distance SQL String
	 */
	public function getSQLOrder(){
		// set Latitude and Longditude Columnnames
		return 'geodistance('.$this->name.'Latitude,'.$this->name.'Longditude,'.$this->getLatitude().','.$this->getLongditude().')';
	}

	 /**
	 * return the Distance to the given lat/lng
	 */
	public function getDistance($lat, $lng, $scale = 'km'){
		return GeoFunctions::getDistance($this->getLatitude(), $this->getLongditude(), $lat, $long, $scale);
	}

	/**
	 * return the Distance to the given location
	 */
	public function getDistanceFromLocation(Location $location, $scale = 'km'){
		return GeoFunctions::getDistance($this->getLatitude(), $this->getLongditude(), $location->getLatitude(), $location->getLongditude(), $scale);
	}
}
