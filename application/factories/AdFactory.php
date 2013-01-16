<?php
namespace carAdsScraper\factories;

/**
 * Factory to manufacture ads
 * 
 * @author Cormac Parle
 */

class AdFactory
{
  
  /**
   * Manufacture an ad based on its type
   * 
   * @param string $url
   * @param array $config
   * @param PDO $pdo
   * @return Scraper 
   */
  public function manufacture($url, $config, $pdo)
  {
    $manufactureMethodName = 'manufacture'.ucfirst($config["source"]);
    if (method_exists($this, $manufactureMethodName)) {
      return $this->$manufactureMethodName($url, $config, $pdo);
    } else {
      return $this->manufactureDefault($url, $config, $pdo);
    }
  }
  
  /**
   * Manufacture the default ad type
   * 
   * @param string $url
   * @param array $config
   * @param PDO $pdo
   * @return Scraper 
   */
  protected function manufactureDefault($url, $config, $pdo)
  {
    return new \carAdsScraper\Ad($url, $config, $pdo);
  }
  
  /**
   * Manufacture ad for carzone.ie
   * 
   * @param string $url
   * @param array $config
   * @param PDO $pdo
   * @return CarzoneScraper 
   */
  protected function manufactureCarzone($url, $config, $pdo)
  {
    return new \carAdsScraper\ads\CarzoneAd($url, $config, $pdo);
  }
  
}
