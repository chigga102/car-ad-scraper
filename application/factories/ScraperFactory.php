<?php
namespace carAdsScraper\factories;

/**
 * Factory to manufacture scrapers
 * 
 * @author Cormac Parle
 */

class ScraperFactory
{
  
  /**
   * Manufacture a scraper based on its type
   * 
   * @param array $config
   * @param PDO $pdo
   * @return Scraper 
   */
  public function manufacture($config, $pdo)
  {
    $manufactureMethodName = 'manufacture'.ucfirst($config["source"]);
    if (method_exists($this, $manufactureMethodName)) {
      return $this->$manufactureMethodName($config, $pdo);
    } else {
      return $this->manufactureDefault($config, $pdo);
    }
  }
  
  /**
   * Manufacture the default scraper
   * 
   * @param array $config
   * @param PDO $pdo
   * @return Scraper 
   */
  protected function manufactureDefault($config, $pdo)
  {
    return new \carAdsScraper\Scraper($config, $pdo);
  }
  
  /**
   * Manufacture scraper for carzone.ie
   * 
   * @param array $config
   * @param PDO $pdo
   * @return CarzoneScraper 
   */
  protected function manufactureCarzone($config, $pdo)
  {
    return new \carAdsScraper\scrapers\CarzoneScraper($config, $pdo);
  }
  
}
