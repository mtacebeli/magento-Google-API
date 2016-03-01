<?php

/**
 * created by Phpstorm 12.11.2011
 *
 * google class to create xml-file fpr google-shop
 * better via terminal
 */
class Googleshop
{
	private $_conditions = array('status' => 1, 'visibility' => 1);
	private $_productList;
	private $XMLWriter;

	private static  $typ = 'Baby- und Jugendwahre';
	private static  $availability = 'auf Lager';
	private static  $coutry = 'DE';
	private static  $shoppingPrice = 0.00;
	private static  $path = 'google_shopping_XML.xml';


	/**
	 * Google constructor.
	 * check and set conditional parameters
	 *
	 * @param $condition
	 */
	function __construct($condition)
	{
		if (is_array($condition) && count($condition) == 2) {
			if (count(array_diff_assoc($condition, $this->_conditions)) !== 0) {
				$this->_conditions = $condition;
			}
		}

	}

	/**
	 * get product Conoecttion objects
	 */
	public function getProductList()
	{

		$this->_productList = Mage::getModel('catalog/product')
			->getCollection()
			->addAttributeToSort('created_at', 'DESC')
			->addAttributeToSelect('*')
			->addAttributeToFilter($this->_conditions['status'], 1)
			->addAttributeToFilter($this->_conditions['visibility'], 4)
			->load();
	}

	/**
	 * creates head for xml
	 */
	private function xmlHead()
	{
		$this->XMLWriter = new XMLWriter();
		$this->XMLWriter->openMemory();
		$this->XMLWriter->startDocument('1.0', 'utf-8');
//main
		$this->XMLWriter->startElement('rss');
		$this->XMLWriter->writeAttribute('version', '2.0');
		$this->XMLWriter->writeAttribute('xmlns:g',	'http://base.google.com/ns/1.0');
		$this->XMLWriter->writeAttribute('xmlns:c',	'http://base.google.com/cns/1.0');
		$this->XMLWriter->setIndent(true);
		$this->XMLWriter->setIndentString("\t");
//root
		$this->XMLWriter->startElement('channel');

		$this->XMLWriter->writeElement('title', 'Wallenfels Online-Shop');
		$this->XMLWriter->writeElement('link', 'shop-url');
		$this->XMLWriter->writeElement('description', utf8_decode('description-string'));
	}


	/**
	 * @param $productName
	 * @param $googleKey
	 * @param $productValue
	 *
	 * writes product information into xml or sets Exception
	 */
	private function writeXml($productName, $googleKey, $productValue)
	{
		try {
			$this->XMLWriter->writeElement($googleKey, $productValue);
		} catch (Exception $e) {
			CustomLogger:('GoogleXML: product: ' . $productName  . ': '. $e->getMessage());
		}
	}


	/**
	 * generates xml body
	 */
	public function xmlBody()
	{
		$this->xmlHead();

		$this->XMLWriter->startElement('item');
		foreach ($this->_productList as $_product) {
			$productName = $_product->getName();

			$this->writeXml($productName, 'g:id',           $_product->getId());
			$this->writeXml($productName, 'g:produkttyp',   Googleshop::$typ);
			$this->writeXml($productName, 'g:google_produktkategorie', $_product->getCategory());
			$this->writeXml($productName, 'g:marke',        $_product->getAttributeText('manufacturer'));
			$this->writeXml($productName, 'g:bild_url',     $_product->getImageUrl());
			$this->writeXml($productName, 'g:title',        $productName);
			$this->writeXml($productName, 'g:description',  $_product->getDescription());
			$this->writeXml($productName, 'g:link',         $_product->getProductUrl());
			$this->writeXml($productName, 'g:availability', Googleshop::$availability);
			$this->writeXml($productName, 'g:preis',        number_format($_product->getPrice(), 2, ',', ' '));

			$this->XMLWriter->startElement('g:versand');
				$this->writeXml($productName, 'g:land',  Googleshop::$coutry);
				$this->writeXml($productName, 'g:price', Googleshop::$shoppingPrice);
			$this->XMLWriter->endElement();

		}

		$this->XMLWriter->endElement();
		$this->XMLWriter->endElement();

		$this->XMLWriter->endDocument();
		$XMLData = $this->XMLWriter->outputMemory();

		// create file
		file_put_contents(Googleshop::$path, $XMLData);

	}


}

