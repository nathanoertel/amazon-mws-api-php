<?php
namespace AmazonMWS;
use AmazonMWS\util\XSDParser;

class CategoryRequest {
	public function loadCategories() {
		$categories = array();
		
		foreach($this->parser->getType()->getFields() as $index => $field) {
			if(!is_string($field->getType()) && $field->getType()->getIsChoice()) {
				foreach($field->getType()->getFields(false) as $cat => $subfield) {
					$noChoice = true;
					foreach($subfield->getType()->getFields() as $subcat => $subsubfield) {
						if(!is_string($subsubfield->getType()) && $subsubfield->getType()->getIsChoice()) {
							foreach($subsubfield->getType()->getFields(false) as $subsubcat => $subsubsubfield) {
								$noChoice = false;
								$categories[] = array($cat, $subsubcat);
							}
						}
					}

					if($noChoice) {
						$categories[] = array($cat);
					}
				}
			}
		}

		return $categories;
	}

	public function loadFields($categories) {
		$fields = array();
 
		$type = $this->parser->getType();

		while(count($categories)) {
			$category = array_shift($categories);

			if(!is_string($type)) {
				foreach($type->getFields() as $index => $field) {
					if(!is_string($field->getType()) && $field->getType()->getIsChoice()) {
						$field->getType()->setChoice($category);
						$type = $field->getType()->getFields()[$category]->getType();
						break;
					}
				}
			}
		}

		return $this->parser->getType()->getFields();
	}

	public function __construct($remote = false) {
		$this->parser = new XSDParser('Product', $remote);
	}
}