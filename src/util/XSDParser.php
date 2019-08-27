<?php
namespace AmazonMWS\util;

class XSDParser {
	private $_type = array();

	private $_parsed = array();

	private $_types = array();

	private $xsdSrc;

	const RELEASES = array(
		'release_4_1',
		'release_1_9'
	);

	public function __construct($type) {
		$this->xsdSrc = dirname(__FILE__).'/../../xsd';
		$this->parse($type);

		$this->_type = $this->loadTypes($type);
	}

	public function getType() {
		return $this->_type;
	}

    private function loadTypes($index, $path = array()) {
        if(!isset($this->_types[$index])) throw new \Exception('Type '.$index.' not found');
		
        if($this->_types[$index] instanceof XSDType) {
            foreach($this->_types[$index]->getFields(false) as $key => $field) {
				$this->loadSubtypes($field);
				// if(is_string($field->getType())) {
				// 	$name = $path;
				// 	$name[] = $field->getName();
				// 	$type = explode(':', $field->getType());

				// 	if(count($type) > 1) {
				// 		if(count($type) > 2) {
				// 			if($type[1] === 'union') {
				// 				$types = explode('|', $type[2]);

				// 				foreach($types as $t) {
				// 					$field->setType($this->loadTypes($t, $name));
				// 				}
				// 			}
				// 		} else {
				// 			$field->setType($this->loadTypes($type[1], $name));
				// 		}
				// 	}
				// } else if($field->getType() instanceof XSDType) {
				// 	foreach($field->getType()->getFields(false) as $key => $field) {
				// 		if(is_string($field->getType())) {
				// 			$name = $path;
				// 			$name[] = $field->getName();
				// 			$type = explode(':', $field->getType());

				// 			if(count($type) > 1) {
				// 				if(count($type) > 2) {
				// 					if($type[1] === 'union') {
				// 						$types = explode('|', $type[2]);

				// 						foreach($types as $t) {
				// 							$field->setType($this->loadTypes($t, $name));
				// 						}
				// 					}
				// 				} else {
				// 					$field->setType($this->loadTypes($type[1], $name));
				// 				}
				// 			}
				// 		}
				// 	}
				// }
            }
        } else if($this->_types[$index] instanceof XSDField) {
			$this->loadSubtypes($this->_types[$index]);
			// if(is_string($this->_types[$index]->getType())) {
			// 	$type = explode(':', $this->_types[$index]->getType());

			// 	if(count($type) > 1) {
			// 		if(count($type) > 2) {
			// 			if($type[1] === 'union') {
			// 				$types = explode('|', $type[2]);

			// 				foreach($types as $t) {
			// 					$this->_types[$index]->setType($this->loadTypes($t, $path));
			// 				}
			// 			}
			// 		} else {
			// 			$this->_types[$index]->setType($this->loadTypes($type[1], $path));
			// 		}
			// 	}
			// } else if($this->_types[$index]->getType() instanceof XSDType) {
			// 	foreach($this->_types[$index]->getType()->getFields(false) as $key => $field) {
			// 		if(is_string($field->getType())) {
			// 			$name = $path;
			// 			$name[] = $field->getName();
			// 			$type = explode(':', $field->getType());

			// 			if(count($type) > 1) {
			// 				if(count($type) > 2) {
			// 					if($type[1] === 'union') {
			// 						$types = explode('|', $type[2]);

			// 						foreach($types as $t) {
			// 							$field->setType($this->loadTypes($t, $name));
			// 						}
			// 					}
			// 				} else {
			// 					$field->setType($this->loadTypes($type[1], $name));
			// 				}
			// 			}
			// 		}
			// 	}
			// }
		} else throw new \Exception('Type '.$index.' is invalid '.get_class($this->_types));
		
		return $this->_types[$index];
	}
	
	private function loadSubtypes($field) {
		if(is_string($field->getType())) {
			$type = explode(':', $field->getType());

			if(count($type) > 1) {
				if(count($type) > 2) {
					if($type[1] === 'union') {
						$types = explode('|', $type[2]);

						foreach($types as $t) {
							$field->setType($this->loadTypes($t));
						}
					}
				} else {
					$field->setType($this->loadTypes($type[1]));
				}
			}
		} else {
			foreach($field->getType()->getFields(false) as $key => $field) {
				$this->loadSubtypes($field);
			}
		}
	}
	
	public function parse($type) {
		if(!isset($this->_parsed[$type])) {
			$filename = $this->getFilename($type);

			$doc = new \DOMDocument();

			$try = 0;

			do {
				$xsd = @file_get_contents($filename);
				$try++;
			} while($xsd == false && $try <= 10);

			$doc->loadXML($xsd);

			foreach($doc->childNodes as $childNode) {
				$this->parseNode($childNode);
			}

			$this->_parsed[$type] = true;
		}

		return true;
	}

	private function getFilename($type) {
		if(!is_dir($this->xsdSrc)) mkdir($this->xsdSrc);
		
		foreach(self::RELEASES as $release) {
			$filename= 'https://images-na.ssl-images-amazon.com/images/G/01/rainier/help/xsd/'.$release.'/'.$type.'.xsd';
			$headers = @get_headers($filename);

			if(empty($headers[0])) return $this->getFilename($type);
			else {
				if($headers[0] == 'HTTP/1.1 200 OK' || $headers[0] == 'HTTP/1.0 200 OK') {
					if(!file_exists($this->xsdSrc.'/'.$release.'-'.$type.'.xsd')) {
						$try = 0;

						do {
							$xsd = @file_get_contents($filename);
							$try++;
						} while($xsd == false && $try <= 10);
						
						if($xsd) file_put_contents($this->xsdSrc.'/'.$release.'-'.$type.'.xsd', $xsd);
						else throw new \Exception('Type '.$type.' could not be loaded. ('.'https://images-na.ssl-images-amazon.com/images/G/01/rainier/help/xsd/'.$release.'/'.$type.'.xsd'.')');
					}

					return $this->xsdSrc.'/'.$release.'-'.$type.'.xsd';
				}
			}
		}

		throw new \Exception('Type '.$type.' could not be found. ('.'https://images-na.ssl-images-amazon.com/images/G/01/rainier/help/xsd/{RELEASE}/'.$type.'.xsd'.')');
	}

	private function parseNode($node) {
		switch($node->nodeName) {
			case 'xsd:schema':
				foreach($node->childNodes as $childNode) {
					$this->parseNode($childNode);
				}
				break;
			case 'xsd:include':
				try {
					$name = str_replace('.xsd', '', $node->attributes->getNamedItem('schemaLocation')->value);
					$this->parse($name);
				} catch(\Exception $e) {
					error_log($e->getMessage());
				}
				break;
			case 'xsd:complexType':
				$type = new XSDType($node->attributes->getNamedItem('name')->value, $node, $this->_types);

				$this->_types[$type->getName()] = $type;
				break;
			case 'xsd:element':
				$type = $node->attributes->getNamedItem('type');

				if($type) {
					if(!($node->attributes->getNamedItem('name') && $node->attributes->getNamedItem('type') && $node->attributes->getNamedItem('name')->value == $node->attributes->getNamedItem('type')->value)) {
						XSDField::loadFields($node, $this->_types, $this->_types);
					}
				} else {
					$simple = false;

					foreach($node->childNodes as $childNode) {
						if($childNode->nodeName === 'xsd:simpleType') {
							$simple = true;
							break;
						}
					}
					if($simple) {
						XSDField::loadFields($node, $this->_types, $this->_types);
					} else {
						$type = new XSDType($node->attributes->getNamedItem('name')->value, $node, $this->_types);

						$this->_types[$type->getName()] = $type;
					}
				}
				break;
			case 'xsd:simpleType':
				if(!($node->attributes->getNamedItem('name') && $node->attributes->getNamedItem('type') && $node->attributes->getNamedItem('name')->value == $node->attributes->getNamedItem('type')->value)) {
					XSDField::loadFields($node, $this->_types, $this->_types);
				}
				break;
			case 'xsd:annotation':
				// ignore
				break;
			default:
				if($node instanceof \DOMElement) error_log('Document Unhandled '.$node->nodeName);
				break;
		}
	}
}

class XSDType {
	protected $_fields = array();

	private $name;

	private $isChoice;

	private $choice;

	public function getName() {
		return $this->name;
	}

	public function getIsChoice() {
		return $this->isChoice;
	}

	public function setChoice($choice) {
		$this->choice = $choice;
	}

	public function getFields($choose = true) {
		if($this->isChoice && $choose) {
			return (isset($this->_fields[$this->choice]) ? array($this->choice => $this->_fields[$this->choice]) : array());
		} else return $this->_fields;
	}

	public function addField($name, $field) {
		$this->fields[$name] = $field;
	}

	public function mergeType(XSDType $type) {
		foreach($type->_fields as $key => $field) {
			$this->_fields[$key] = $field;
		}
	}

	public function __construct($name, $node, &$types) {
		$this->name = $name;
		$this->isChoice = false;
		$this->parseNode($node, $types);
	}

	private function parseNode($node, &$types) {
		switch($node->nodeName) {
			case 'xsd:complexType':
				foreach($node->childNodes as $childNode) {
					$this->parseNode($childNode, $types);
				}
				break;
			case 'xsd:sequence':
				foreach($node->childNodes as $childNode) {
					if(($childNode instanceof \DOMElement)) {
						XSDField::loadFields($childNode, $types, $this->_fields);
					}
				}
				break;
			case 'xsd:element':
				foreach($node->childNodes as $childNode) {
					$this->parseNode($childNode, $types);
				}

				break;
			case 'xsd:simpleType':
			case 'xsd:simpleContent':
				foreach($node->childNodes as $childNode) {
					if(($childNode instanceof \DOMElement)) {
						XSDField::loadFields($childNode, $types, $this->_fields);
					}
				}

				break;
			case 'xsd:attribute':
				XSDField::parseNode($node, '', $types, $this->_fields, true);
				break;
			case 'xsd:choice':
				$this->isChoice = true;
				foreach($node->childNodes as $childNode) {
					if(($childNode instanceof \DOMElement)) {
						XSDField::loadFields($childNode, $types, $this->_fields);
					}
				}
				break;
			case 'xsd:annotation':
				// skip and do nothing
				break;
			default:
				if($node instanceof \DOMElement) error_log('Type Unhandled '.$node->nodeName);
				break;
		}
	}
}

class XSDField {
	protected $name;

	protected $required;

	protected $minOccurs;

	protected $maxOccurs;

	protected $type;

	protected $options;

	protected $isAttribute;

	protected $isArray;

	protected $restrictions = array();

	public function getName() {
		return $this->name;
	}

	public function getType() {
		return $this->type;
	}

	public function isRequired() {
		return $this->required;
	}

	public function isArray() {
		return $this->isArray;
	}

	public function setType($type) {
		if($type instanceof XSDType && $this->type instanceof XSDType) {
			$this->type->mergeType($type);
		} else if($type instanceof XSDField) {
			if(is_string($type->getType())) {
				$this->copy($type);
			} else {
				if(array_keys($type->getType()->getFields()) == array('')) {
					$this->copy($type->getType()->getFields()['']);
				} else {
					$this->copy($type);
				}
			}
		} else $this->type = $type;
	}

	protected function copy($field) {
		$this->type = $field->type;
		$this->options = $field->options;
		$this->restrictions = $field->restrictions;
	}

	public function getOptions() {
		return $this->options;
	}

	public static function loadFields($node, &$types, &$fields) {
		$name = $node->attributes->getNamedItem('name');
		$ref = $node->attributes->getNamedItem('ref');

		if($name) $name = $name->value;
		else if($ref) $name = $ref->value;

		self::parseNode($node, $name, $types, $fields);
	}

	public function __construct($name, $type, $minOccurs = 0, $maxOccurs = null, $isAttribute = false) {
		$this->name = $name;
		$this->type = $type;
		$this->minOccurs = $minOccurs;
		$this->maxOccurs = $maxOccurs;
		$this->required = $this->minOccurs > 0;
		$this->isAttribute = $isAttribute;
		$this->isArray = $maxOccurs > 0;

		if(is_string($this->type)) {
			if(strpos($this->type, 'xsd:') === 0) $this->type = substr($this->type, 4);
			else $this->type = 'complex:'.$this->type;
		}
	}

	public static function parseNode($node, $path, &$types, &$fields, $isAttribute = false) {
		if(($node instanceof \DOMElement)) {
			$minOccurs = $node->attributes->getNamedItem('minOccurs');
			$maxOccurs = $node->attributes->getNamedItem('maxOccurs');

			if($minOccurs) $minOccurs = intval($minOccurs->value);
			else $minOccurs = 0;
			if($maxOccurs) $maxOccurs = intval($maxOccurs->value);
			else $maxOccurs = null;

			switch($node->nodeName) {
				case 'xsd:complexType':
					foreach($node->childNodes as $childNode) {
						self::parseNode($childNode, $path, $types, $fields, $isAttribute);
					}
					break;
				case 'xsd:sequence':
					foreach($node->childNodes as $childNode) {
						if(($childNode instanceof \DOMElement)) {
							$names = array($path);

							$name = $childNode->attributes->getNamedItem('name');
							if($name) $names[] = $name->value;

							self::parseNode($childNode, implode('/', $names), $types, $fields, $isAttribute);
						}
					}
					break;
				case 'xsd:simpleContent':
					foreach($node->childNodes as $childNode) {
						self::parseNode($childNode, $path, $types, $fields, $isAttribute);
					}
					break;
				case 'xsd:extension':
					$names = array();
					if(!empty($path)) $names[] = $path;
					$names[] = '_';

					$name = implode('/', $names);

					$fields[$name] = new XSDField($name, $node->attributes->getNamedItem('base')->value, 1);
					foreach($node->childNodes as $childNode) {
						self::parseNode($childNode, $path, $types, $fields, $isAttribute);
					}
					break;
				case 'xsd:element':
					$type = $node->attributes->getNamedItem('type');
					$ref = $node->attributes->getNamedItem('ref');
					$name = $node->attributes->getNamedItem('name');

					if($type) {
						$fields[$path] = new XSDField($path, $type->value, $minOccurs, $maxOccurs, $isAttribute);
					} else if($ref) $fields[$path] = new XSDField($path, $ref->value, $minOccurs, $maxOccurs, $isAttribute);
					else {
						$complex = true;

						foreach($node->childNodes as $childNode) {
							if($childNode->nodeName === 'xsd:simpleType') {
								self::parseNode($childNode, $path, $types, $fields, $isAttribute);
								$complex = false;
								break;
							}
						}

						if($complex) {
							$fields[$path] = new XSDField($path, new XSDType($name->value, $node, $types), $minOccurs, $maxOccurs, $isAttribute);
						}
					}
					break;
				case 'xsd:simpleType':
					foreach($node->childNodes as $childNode) {
						self::parseNode($childNode, $path, $types, $fields, $isAttribute);
					}
					break;
				case 'xsd:attribute':
					$name = $node->attributes->getNamedItem('name');
					$type = $node->attributes->getNamedItem('type');
					$use = $node->attributes->getNamedItem('use');

					$names = array();
					if(!empty($path)) $names[] = $path;
					$names[] = $name->value;

					$name = implode('/', $names);

					if($type) {
						$base = $node->attributes->getNamedItem('base');

						if($base) $fields[$name] = new XSDField($name, $base->value, ($use && $use->value == 'required' ? 1 : 0), null, true);
						else $fields[$name] = new XSDField($name, $type->value, ($use && $use->value == 'required' ? 1 : 0), null, true);
					} else {
						foreach($node->childNodes as $childNode) {
							self::parseNode($childNode, $name, $types, $fields, true);
						}
					}
					break;
				case 'xsd:restriction':
					$fields[$path] = new XSDField($path, $node->attributes->getNamedItem('base')->value, $minOccurs, $maxOccurs, $isAttribute);
					foreach($node->childNodes as $childNode) {
						self::parseNode($childNode, $path, $types, $fields, $isAttribute);
					}
					break;
				case 'xsd:enumeration':
					$fields[$path]->options[] = array(
						'name' => ucwords(str_replace('_', ' ', $node->attributes->getNamedItem('value')->value)),
						'value' => $node->attributes->getNamedItem('value')->value
					);
					break;
				case 'xsd:choice':
					$name = $node->parentNode->parentNode->attributes->getNamedItem('name');

					if($name) {
						$fields[$path] = new XSDField($path, new XSDType($name->value, $node, $types), $minOccurs, $maxOccurs, $isAttribute);
					} else {
						foreach($node->childNodes as $childNode) {
							if(($childNode instanceof \DOMElement)) {
								$names = array($path);
	
								$name = $childNode->attributes->getNamedItem('name');
								if($name) $names[] = $name->value;
	
								self::parseNode($childNode, implode('/', array_filter($names)), $types, $fields, $isAttribute);
							}
						}
					}
					break;
				case 'xsd:union':
					$fields[$path] = new XSDField($path, 'union:'.implode('|', explode(' ', $node->attributes->getNamedItem('memberTypes')->value)), $minOccurs, $maxOccurs, $isAttribute);
					break;
				case 'xsd:length':
				case 'xsd:minLength':
				case 'xsd:totalDigits':
				case 'xsd:fractionDigits':
				case 'xsd:maxLength':
				case 'xsd:minInclusive':
				case 'xsd:maxInclusive':
				case 'xsd:pattern':
				case 'xsd:minExclusive':
					$fields[$path]->restrictions[substr($node->nodeName, 4)] = $node->attributes->getNamedItem('value')->value;
					break;
				case 'xsd:annotation':
					// ignore these
					break;
				default:
					if($node instanceof \DOMElement) error_log('Field Unhandled '.$node->nodeName);
					break;
			}
		}
	}
}