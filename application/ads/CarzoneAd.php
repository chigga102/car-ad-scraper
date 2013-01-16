<?php
namespace carAdsScraper\ads;

/**
 * Class representing a single car ad on carzone
 * 
 * @author Cormac Parle
 */
class CarzoneAd extends \carAdsScraper\Ad
{
  
  /**
   * Parses the supplied url according to rules in config, and saves the ad
   * 
   */
  public function parse()
  {
    parent::parse();
    
    //transform mileage field
    $this->mileage = str_replace(
      ',', 
      '', 
      preg_replace('/ .*/', '', $this->mileage)
    );
    
    //parse engine field
    $engineFieldText = $this->dom->find($this->config["engineSelector"], 0)
      ->innerText();
    preg_match('/^([0-9\.]*)L?\s*([a-z]*).*$/i', trim($engineFieldText), $matches);
    
    $this->engineSize = $matches[1];
    $this->idFuel = $this->findForeignKeyValue('idFuel', $matches[2]);
  }
  
}