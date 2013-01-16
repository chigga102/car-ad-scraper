<?php
namespace carAdsScraper;

/**
 * Class representing a single car ad
 * 
 * @author Cormac Parle
 */
class Ad
{
  protected $url;
  protected $dom;
  protected $config;
  protected $pdo;
  
  protected $source;
  protected $idSource;
  
  protected $idMake;
  protected $idModel;
  protected $idBodyType;
  protected $doors;
  protected $year;
  protected $idFuel;
  protected $engineSize;
  protected $mileage;
  protected $price;
  protected $idTransmission;
  protected $idLocation;
  protected $owners;
  
  /**
   *
   * @param url $url
   * @param array $config
   * @param PDO $pdo 
   */
  public function __construct($url, $config, $pdo)
  {
    $this->url = $url;
    $this->dom = str_get_html(file_get_contents($this->url));
    $this->config = $config;
    $this->pdo = $pdo;
  }
  
  /**
   * Parses the supplied url according to rules in config, and saves the ad
   * 
   * @return bool True if the ad was saved (i.e. if it had a price)
   */
  public function parseAndSave()
  {
    $this->parse();
    return $this->save();
  }
  
  /**
   * Parses the supplied url according to rules in config
   * 
   * @throws Exception
   */
  protected function parse()
  {
    $tmp = preg_match($this->config["adUrlSourceIdMatch"], $this->url, $matches);
    if (($tmp !== false) && isset($matches[$this->config["adUrlSourceIdMatchArrayId"]])) {
      $this->idSource = $matches[$this->config["adUrlSourceIdMatchArrayId"]];
    } else {
      throw new \Exception("Failed to determine source id for $url");
    }
    
    $this->source = $this->config["source"];
    
    $this->setRequiredForeignKeyFieldFromDom("idMake");
    $this->setForeignKeyFieldFromDom("idModel",
      array(
        "table" => "make",
        "id"    => $this->idMake
      )
    );
    $this->setForeignKeyFieldFromDom("idBodyType");
    $this->setFieldFromDom("doors");
    $this->setFieldFromDom("year");
    $this->setForeignKeyFieldFromDom("idFuel");
    $this->setFieldFromDom("engineSize");
    $this->setFieldFromDom("mileage");
    $this->setPrice();
    $this->setForeignKeyFieldFromDom("idTransmission");
    $this->setForeignKeyFieldFromDom("idLocation");
    $this->setFieldFromDom("owners");
  }
  
  /**
   * Save the ad to the database
   * 
   * @return bool True if the ad was saved, false otherwise
   */
  protected function save()
  {
    static $preparedReplace;
    if (!isset($preparedReplace)) {
      $query = "replace into ad set
        idModel = ?,
        idBodyType = ?,
        doors = ?,
        year = ?,
        idFuel = ?,
        engineSize = ?,
        mileage = ?,
        price = ?,
        idTransmission = ?,
        idLocation = ?,
        owners = ?,
        source = ?,
        idSource = ?
      ";
      $preparedReplace = $this->pdo->prepare($query);
    }
    
    
    if ($this->price) {
      
      $data = array(
        $this->idModel,
        $this->idBodyType,
        $this->doors,
        $this->year,
        $this->idFuel,
        $this->engineSize,
        $this->mileage,
        $this->price,
        $this->idTransmission,
        $this->idLocation,
        $this->owners,
        $this->source,
        $this->idSource
      );
      $preparedReplace->execute($data);
      
      return true;
      
    } else {
      return false;
    }
    
  }
  
  /**
   * Sets the value of a foreign key field (field name starting with "id")
   * from the dom for the ad
   * 
   * Inserts the data into the parent table if it doesn't already exist there
   * 
   * @staticvar array $fieldValues Maps text to ids for each field
   * @param string $field The name of the object property to set
   * @param array $parent If the foreign key field refers to a row that itself 
   *  has a foreign key, this must be passed in here in the format
   *  array('table' => xxx, 'id' => yyy)
   */
  protected function setForeignKeyFieldFromDom($field, $parent = null)
  {
    static $fieldValues;
    
    $tableName = $this->findParentTableNameFromForeignKeyFieldName($field);
    $fieldText = $this->findFieldText($tableName);
    if ($fieldText) {
      
      if (!isset($fieldValues[$field][$fieldText])) {
      
        $fieldValues[$field][$fieldText] = $this->findForeignKeyValue(
          $field, 
          $fieldText, 
          $parent
        );

      }  
      
      $this->$field = $fieldValues[$field][$fieldText];
      
    } 
  }
  
  /**
   * Return the name of the parent table of a foreign key field
   * 
   * e.g. pass in "idMake" and "make" is returned
   * 
   * @param string $name
   * @return string 
   */
  protected function findParentTableNameFromForeignKeyFieldName($name)
  {
    return lcfirst(preg_replace('/^id/', '', $name));
  }
  
  /**
   * Gets the value of a foreign key field (field name starting with "id")
   * from its text value
   * 
   * Inserts the data into the parent table if it doesn't already exist there
   * 
   * @staticvar array $preparedSelects
   * @staticvar array $preparedInserts
   * @param string $field The name of the object property to set
   * @param string $fieldText The text value of $field
   * @param array $parent If the foreign key field refers to a row that itself 
   *  has a foreign key, this must be passed in here in the format
   *  array('table' => xxx, 'id' => yyy)
   */
  protected function findForeignKeyValue($field, $fieldText, $parent = null)
  {
    static $preparedSelects, $preparedInserts;
    
    $tableName = $this->findParentTableNameFromForeignKeyFieldName($field);
    if (!isset($preparedSelects[$field])) {
      $query = "select $field from $tableName where name = ?";
      if (is_array($parent)) {
        $query .= " and id" . ucfirst($parent["table"]). " = ?";
      }
      $preparedSelects[$field] = $this->pdo->prepare($query);
    }
    if (is_array($parent)) {
      $preparedSelects[$field]->execute(array($fieldText, $parent["id"]));
    } else {
      $preparedSelects[$field]->execute(array($fieldText));
    }
    $id = $preparedSelects[$field]->fetchColumn();
    if (!$id) {
      if (!isset($preparedInserts[$field])) {
        $query = "insert into $tableName set name = ?";
        if (is_array($parent)) {
          $query .= ", id" . ucfirst($parent["table"]). " = ?";
        }
        $preparedInserts[$field] = $this->pdo->prepare($query);
      }
      if (is_array($parent)) {
        $preparedInserts[$field]->execute(array($fieldText, $parent["id"]));
      } else {
        $preparedInserts[$field]->execute(array($fieldText));
      }
      $id = $this->pdo->lastInsertId();
    }
    
    return $id;
  }

  /**
   * Same as setForeignKeyFieldFromDom() except throws an exception if the 
   * field is blank
   * 
   * @see setForeignKeyFieldFromDom()
   * @param type $field 
   * @throws \Exception
   */
  protected function setRequiredForeignKeyFieldFromDom($field)
  {
    $this->setForeignKeyFieldFromDom($field);
    if (!$this->$field) {
      var_dump(debug_backtrace());
      throw new \Exception("Failed to determine $fieldName for $url");
    }
  }
  
  /**
   * Sets a text field from the dom
   * 
   * @param string $field 
   */
  protected function setFieldFromDom($field)
  {
    $fieldText = $this->findFieldText($field);
    if ($fieldText) {
      $this->$field = $fieldText;
    } 
  }
  
  /**
   * Finds the text for a particular field
   * 
   * @param string $field
   * @return string
   */
  protected function findFieldText($field)
  {
    $fieldSelector = $field."Selector";
    $fieldNode = $this->dom->find($this->config[$fieldSelector], 0);
    if ($fieldNode) {
      return trim(str_replace('&nbsp;',' ',$fieldNode->innerText()));
    } else {
      return null;
    }
  }
  
  /**
   * Remove everything that's not a number (e.g. currency, commas, etc)
   * 
   */
  protected function setPrice()
  {
    $this->setFieldFromDom("price");
    $this->price = preg_replace('/[^0-9\.]*/', '' , $this->price);
  }
  
}