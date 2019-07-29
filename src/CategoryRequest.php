<?php
namespace AmazonMWS;
use AmazonMWS\util\XSDParser;

class CategoryRequest {
	public function loadCategories() {
		$categories = array();
		// error_log('Loading categories');
		
		foreach($this->parser->getType()->getFields() as $index => $field) {
			if(!is_string($field->getType()) && $field->getType()->getIsChoice()) {
				foreach($field->getType()->getFields() as $cat => $subfield) {
					$noChoice = true;
					foreach($subfield->getType()->getFields() as $subcat => $subsubfield) {
						if(!is_string($subsubfield->getType()) && $subsubfield->getType()->getIsChoice()) {
							foreach($subsubfield->getType()->getFields() as $subsubcat => $subsubsubfield) {
								$noChoice = false;
								// error_log($cat.' > '.$subsubcat);
								$categories[] = array($cat, $subsubcat);
							}
						}
					}

					if($noChoice) {
						// error_log($cat);
						$categories[] = array($cat);
					}
				}
			}
		}

		return $categories;
	}

	public function loadFields($categories) {
		$fields = array();
 
		foreach($this->parser->getType()->getFields() as $index => $field) {
			if(!is_string($field->getType()) && $field->getType()->getIsChoice()) {
				foreach($field->getType()->getFields() as $cat => $subfield) {
					if($cat == $categories[0]) {
						$fields[$cat] = $subfield->getType()->getFields();

						if(count($categories) > 1) {
							foreach($subfield->getType()->getFields() as $subcat => $subsubfield) {
								if(!is_string($subsubfield->getType()) && $subsubfield->getType()->getIsChoice()) {
									foreach($subsubfield->getType()->getFields() as $subsubcat => $subsubsubfield) {
										if($subsubcat == $categories[1]) {
											$fields[$subsubcat] = $subsubfield->getType()->getFields();
											break;
										}
									}
								}
							}
						}

						break;
					}
				}
			}
		}

		return $fields;
	}

	public function __construct() {
		$this->parser = new XSDParser('Product');
	}
}