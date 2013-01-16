<?php

/**
 * Scrapes car ad data from all sites listed in config/sites.ini, and writes
 * it to the database
 * 
 * @author Cormac Parle
 */

ini_set("max_execution_time", 86400);

require '../library/simple_html_dom.php';
require 'factories/ScraperFactory.php';
require 'Scraper.php';
require 'scrapers/CarzoneScraper.php';
require 'factories/AdFactory.php';
require 'Ad.php';
require 'ads/CarzoneAd.php';

$applicationConfig = parse_ini_file('config/application.ini', true);
$pdo = new PDO('mysql:'.
    'host='. $applicationConfig["db"]["host"] . '; '.
    'dbname='. $applicationConfig["db"]["database"],
    $applicationConfig["db"]["user"],
    $applicationConfig["db"]["password"]
);

$scraperFactory = new \carAdsScraper\factories\ScraperFactory();
$sites = parse_ini_file('config/sites.ini', true);

foreach ($sites as $siteSpecificConfig) {
  
  echo "Scraping data from ".htmlentities($siteSpecificConfig["source"])."<br />\n";
  $scraper = $scraperFactory->manufacture($siteSpecificConfig, $pdo);
  $adCount = $scraper->run();
  echo intval($adCount)." valid ads found<br />\n";
  
}

echo "Finished<br />\n";