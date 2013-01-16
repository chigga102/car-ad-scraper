<?php
namespace carAdsScraper;

/**
 * Scraper class
 * 
 * Scrapes the data and inserts it into the database
 * 
 * @author Cormac Parle
 */

class Scraper
{
  protected $pdo;
  protected $config;
  
  
  /**
   * @param array $config
   * @param PDO $pdo 
   */
  public function __construct($config, $pdo) {
    $this->pdo = $pdo;
    $this->config = $config;
  }
  
  /**
   * Run the scraper
   * 
   * @return int Number ads added to database
   */
  public function run()
  {
    $adCount = 0;
    
    $url = $this->config["initialUrl"];
    
    do {
      
      $dom = str_get_html(file_get_contents($url));
      $adUrls = $this->getAdUrlsFromListPageDom($dom);
      foreach($adUrls as $adUrl) {
        if ($this->scrapeAd($adUrl)) {
          $adCount++;
        }
        if ($this->config["sleepTime"]) {
          sleep($this->config["sleepTime"]);
        }
      }
      
    } while ($url = $this->getNextListPageUrlFromDom($dom));
    
    return $adCount;
  }
  
  /**
   * Returns an array of ad link urls from an ad list page DOM object
   * 
   * @param string $url
   * @return array
   */
  public function getAdUrlsFromListPageDom($dom)
  {
    $adLinks = $dom->find($this->config["adLinkSelector"]);
    $adUrls = array();
    foreach ($adLinks as $adLink) {
      if ($adLink->href) {
        $adUrls[] = $adLink->href;
      }
    }
    return $adUrls;
  }
  
  /**
   * Returns the url of the next page of ads, false if not found
   * 
   * @param simple_html_dom $dom
   * @return string|bool 
   */
  public function getNextListPageUrlFromDom($dom)
  {
    $nextLink = $dom->find($this->config["nextListPageSelector"]);
    if ($nextLink) {
      return $this->transformNextListPageUrl($nextLink->href);
    } else {
      return false;
    }
  }
  
  /**
   * Scrapes an individual ad page
   * 
   * @param type $adUrl
   * @return bool True if the ad was valid, false otherwise 
   */
  public function scrapeAd($adUrl)
  {
    $adFactory = new \carAdsScraper\factories\AdFactory;
    $ad = $adFactory->manufacture(
      $adUrl, 
      $this->config, 
      $this->pdo
    );
    return $ad->parseAndSave();
  }
  
  /**
   * Transforms the link to the next page based on the config
   * 
   * @param string $url
   * @return string 
   */
  protected function transformNextListPageUrl($url)
  {
    if (isset($this->config["nextListPageUrlTransformMatch"])) {
      return preg_replace($this->config["nextListPageUrlTransformMatch"],
        $this->config["nextListPageUrlTransformReplace"],
        $url);
    } else {
      return $url;
    }
  }
  
}
