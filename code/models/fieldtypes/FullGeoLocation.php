<?php

/**
 * Implements the "Money" pattern.
 *
 * @package geoform
 * @subpackage model
 */
class FullGeoLocation extends GeoLocation implements CompositeDBField
{

	/**
	 * @var string $getCountry ()
	 */
	protected $country;

	/**
	 * @var string $getRegion ()
	 */
	protected $region;

	/**
	 * @var string $getCity ()
	 */
	protected $city;

	/**
	 * @var string $getStreet ()
	 */
	protected $street;

	/**
	 * @var string $getAreaCode ()
	 */
	protected $areaCode;

	/**
	 * @param array
	 */
	static $composite_db = array(
		"Country" => "Varchar(255)",
		"Region" => "Varchar(255)",
		"City" => "Varchar(255)",
		"Street" => "Varchar(255)",
		"AreaCode" => "Varchar(255)",
	);

	public function compositeDatabaseFields()
	{
		return array_merge(self::$composite_db, parent::$composite_db);
	}

	public function writeToManipulation(&$manipulation)
	{
		if ($this->getCountry()) {
			$manipulation['fields'][$this->name . 'Country'] = $this->prepValueForDB($this->getCountry());
		} else {
			$manipulation['fields'][$this->name . 'Country'] = DBField::create_field('Varchar', $this->getCountry())->nullValue();
		}

		if ($this->getRegion()) {
			$manipulation['fields'][$this->name . 'Region'] = $this->prepValueForDB($this->getRegion());
		} else {
			$manipulation['fields'][$this->name . 'Region'] = DBField::create_field('Varchar', $this->getRegion())->nullValue();
		}

		if ($this->getCity()) {
			$manipulation['fields'][$this->name . 'City'] = $this->prepValueForDB($this->getCity());
		} else {
			$manipulation['fields'][$this->name . 'City'] = DBField::create_field('Varchar', $this->getCity())->nullValue();
		}

		if ($this->getStreet()) {
			$manipulation['fields'][$this->name . 'Street'] = $this->prepValueForDB($this->getStreet());
		} else {
			$manipulation['fields'][$this->name . 'Street'] = DBField::create_field('Varchar', $this->getStreet())->nullValue();
		}

		if ($this->getAreaCode()) {
			$manipulation['fields'][$this->name . 'AreaCode'] = $this->prepValueForDB($this->getAreaCode());
		} else {
			$manipulation['fields'][$this->name . 'AreaCode'] = DBField::create_field('Varchar', $this->getAreaCode())->nullValue();
		}

		parent::writeToManipulation($manipulation);
	}

	public function addToQuery(&$query)
	{
		parent::addToQuery($query);
		$query->selectField(sprintf('"%sCountry"', $this->name));
		$query->selectField(sprintf('"%sRegion"', $this->name));
		$query->selectField(sprintf('"%sCity"', $this->name));
		$query->selectField(sprintf('"%sStreet"', $this->name));
		$query->selectField(sprintf('"%sAreaCode"', $this->name));
	}

	/**
	 * @param array|DBField|mixed $value
	 * @param null $record
	 * @param bool $markChanged
	 */
	public function setValue($value, $record = null, $markChanged = true)
	{
		// TODO @kaspiCZ refactor to handle only additional values and utilize parent::setValue
		$address = $this->nullValue();
		$lat = $this->nullValue();
		$lng = $this->nullValue();
		$country = $this->nullValue();
		$region = $this->nullValue();
		$city = $this->nullValue();
		$street = $this->nullValue();
		$areaCode = $this->nullValue();

		if ($value instanceof FullGeoLocation && $value->exists()) {
			$address = $value->getAddress();
			$lat = $value->getLatitude();
			$lng = $value->getLongditude();

			$country = $value->getCountry();
			$region = $value->getRegion();
			$city = $value->getCity();
			$street = $value->getStreet();
			$areaCode = $value->getAreaCode();
		} else if (is_null($value)
			&& array_key_exists($this->name . 'Address', $record)
			&& array_key_exists($this->name . 'Latitude', $record)
			&& array_key_exists($this->name . 'Longditude', $record)
			&& is_string($record[$this->name . 'Address'])
			&& is_numeric($record[$this->name . 'Latitude'])
			&& is_numeric($record[$this->name . 'Longditude'])
		) {
			$address = $record[$this->name . 'Address'];
			$lat = $record[$this->name . 'Latitude'];
			$lng = $record[$this->name . 'Longditude'];

			if (isset($record[$this->name . 'Country'])) {
				$country = $record[$this->name . 'Country'];
			}

			if (isset($record[$this->name . 'Region'])) {
				$region = $record[$this->name . 'Region'];
			}

			if (isset($record[$this->name . 'City'])) {
				$city = $record[$this->name . 'City'];
			}

			if (isset($record[$this->name . 'Street'])) {
				$street = $record[$this->name . 'Street'];
			}

			if (isset($record[$this->name . 'AreaCode'])) {
				$areaCode = $record[$this->name . 'AreaCode'];
			}
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

			if (array_key_exists('Country', $value)) {
				$country = $value['Country'];
			}
			if (array_key_exists('Region', $value)) {
				$region = $value['Region'];
			}
			if (array_key_exists('City', $value)) {
				$city = $value['City'];
			}
			if (array_key_exists('Street', $value)) {
				$street = $value['Street'];
			}
			if (array_key_exists('AreaCode', $value)) {
				$areaCode = $value['AreaCode'];
			}
		}

		$this->setAddress($address, $markChanged);
		$this->setLatitude($lat, $markChanged);
		$this->setLongditude($lng, $markChanged);
		$this->setCountry($country, $markChanged);
		$this->setRegion($region, $markChanged);
		$this->setCity($city, $markChanged);
		$this->setStreet($street, $markChanged);
		$this->setAreaCode($areaCode, $markChanged);

		if ($markChanged) $this->isChanged = true;
	}

	/**
	 * @return string
	 */
	public function getCountry()
	{
		return $this->country;
	}

	/**
	 * @param string
	 */
	public function setCountry($country, $markChanged = true)
	{
		$this->country = $country;
		if ($markChanged) $this->isChanged = true;
	}

	/**
	 * @return string
	 */
	public function getRegion()
	{
		return $this->region;
	}

	/**
	 * @param string
	 */
	public function setRegion($region, $markChanged = true)
	{
		$this->region = $region;
		if ($markChanged) $this->isChanged = true;
	}

	/**
	 * @return string
	 */
	public function getCity()
	{
		return $this->city;
	}

	/**
	 * @param string
	 */
	public function setCity($city, $markChanged = true)
	{
		$this->city = $city;
		if ($markChanged) $this->isChanged = true;
	}

	/**
	 * @return string
	 */
	public function getStreet()
	{
		return $this->street;
	}

	/**
	 * @param string
	 */
	public function setStreet($street, $markChanged = true)
	{
		$this->street = $street;
		if ($markChanged) $this->isChanged = true;
	}

	/**
	 * @return string
	 */
	public function getAreaCode()
	{
		return $this->areaCode;
	}

	/**
	 * @param string
	 */
	public function setAreaCode($areaCode, $markChanged = true)
	{
		$this->areaCode = $areaCode;
		if ($markChanged) $this->isChanged = true;
	}

	/**
	 * @return boolean
	 */
	public function exists()
	{
		return ($this->getCountry() && parent::exists());
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
	function scaffoldFormField($title = null)
	{
		$field = new FullBackendGeoLocationField($this->name);
		$field->setLocale($this->getLocale());

		return $field;
	}
}
