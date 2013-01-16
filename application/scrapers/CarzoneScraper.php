<?php
namespace carAdsScraper\scrapers;

/**
 * Carzone-specific scraper class
 * 
 * @author Cormac Parle
 */

class CarzoneScraper extends \carAdsScraper\Scraper
{
  /**
   * Returns the url of the next page of ads, false if not found
   * 
   * @param simple_html_dom $dom
   * @return string|bool 
   */
  public function getNextListPageUrlFromDom($dom)
  {
    $lastPaginationLink = $dom->find($this->config["nextListPageSelector"], -1);
    if (!$lastPaginationLink) {
      return false;
    } else {
      if (strpos($lastPaginationLink->innertext(), 'Next') === 0) {
        return $this->transformNextListPageUrl($lastPaginationLink->href);
      } else {
        return false;
      }
    }
  }
  
}
