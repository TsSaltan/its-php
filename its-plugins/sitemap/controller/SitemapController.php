<?php
namespace tsframe\controller;

use tsframe\Http;
use tsframe\controller\AbstractController;
use tsframe\module\SitemapGenerator;
use tsframe\module\SitemapItem;

/**
 * @route GET /sitemap.[txt:format]
 * @route GET /sitemap.[xml:format]
 */ 
class SitemapController extends AbstractController {
	public function response(){
		$sitemap = SitemapGenerator::loadData();
		$content = null;

		switch($this->params['format']){
			case 'txt':
				$this->responseType = Http::TYPE_PLAIN;
				$content = implode(PHP_EOL, array_keys($sitemap));
				break;

			case 'xml':
				$this->responseType = Http::TYPE_XML;
				$dom = new \DOMDocument('1.0', 'utf-8');
				$urlset = $dom->createElement('urlset');
				$urlset->setAttribute('xmlns','http://www.sitemaps.org/schemas/sitemap/0.9');

				foreach($sitemap as $item){
					$url = $dom->createElement('url');

					$loc = $dom->createElement('loc');
					$text = $dom->createTextNode(
						htmlentities($item->getLoc(), ENT_QUOTES)
					);
					$loc->appendChild($text);
					$url->appendChild($loc);

					$changefreq = $dom->createElement('changefreq');
					$text = $dom->createTextNode($item->getChangefreq());
					$changefreq->appendChild($text);
					$url->appendChild($changefreq);

					$lastmod = $dom->createElement('lastmod');
					$text = $dom->createTextNode($item->getLastmod());
					$lastmod->appendChild($text);
					$url->appendChild($lastmod);

					$priority = $dom->createElement('priority');
					$text = $dom->createTextNode($item->getPriority());
					$priority->appendChild($text);
					$url->appendChild($priority);
 
					$urlset->appendChild($url);
				}

				$dom->appendChild($urlset);
				$dom->preserveWhiteSpace = false;
				$dom->formatOutput = true;
				$content = $dom->saveXML();
		}

		return $content;
	}
}