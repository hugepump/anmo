<?php
/**
 * JSON Schema generate/validate
 *
 */
namespace app\Common;
class JsonSchema {

	/**
	 * JSON
	 *
	 * @var string
	 */
	private $json;
	/**
	 * Last error
	 *
	 * @var array
	 */
	private $errors;
	/**
	 * Extend types
	 *
	 * @var map
	 */
	private $complexTypes;

	/**
	 *
	 * @param string $json
	 */
	function __construct($json) {
		$this -> errors = array();
		$this -> complexTypes = array();
		$this -> json = json_decode($json);

	}

	/**
	 * Generate JSON Schema
	 *
	 * @return string JSON Schema
	 */
	public function getSchema() {
		$schema = null;
		$schema = $this -> genByType($this -> json);
		return json_encode($schema);
	}

	/**
	 * Generate JSON Schema by type
	 * @param mixed $value
	 * @return object
	 */
	private function genByType($value) {
		$type = gettype($value);
		$schema = array();
		switch ($type) {
			case 'boolean' :
				$schema['type'] = 'boolean';
				$schema['default'] = false;
				break;
			case 'integer' :
				$schema['type'] = 'integer';
				$schema['default'] = 0;
				$schema['minimum'] = 0;
				$schema['maximum'] = PHP_INT_MAX;
				$schema['exclusiveMinimum'] = 0;
				$schema['exclusiveMaximum'] = PHP_INT_MAX;
				break;
			case 'double' :
				$schema['type'] = 'number';
				$schema['default'] = 0;
				$schema['minimum'] = 0;
				$schema['maximum'] = PHP_INT_MAX;
				$schema['exclusiveMinimum'] = 0;
				$schema['exclusiveMaximum'] = PHP_INT_MAX;
				break;
			case 'string' :
				$schema['type'] = 'string';
				$schema['format'] = 'regex';
				$schema['pattern'] = '/^[a-z0-9]+$/i';
				$schema['minLength'] = 0;
				$schema['maxLength'] = PHP_INT_MAX;
				break;
			case 'array' :
				$schema['type'] = 'array';
				$schema['minItems'] = 0;
				$schema['maxItems'] = 20;
				$items = array();
				foreach ($value as $value) {
					$items = $this -> genByType($value);
					break;
				}
				$schema['items'] = $items;
				break;
			case 'object' :
				$schema['type'] = 'object';
				$items = array();
				$value = get_object_vars($value);
				foreach ($value as $key => $value) {
					$items[$key] = $this -> genByType($value);
				}
				$schema['properties'] = $items;
				break;
			case 'null' :
				// any in union types
				$schema['type'] = 'null';
				break;
			default :
				break;
		}
		return $schema;
	}

	/**
	 * Set type schema
	 * @param string $typeSchema
	 */
	public function addType($typeSchema) {
		if (empty($typeSchema)) {
			return;
		}
		$typeSchema = json_decode($typeSchema, true);
		if (is_array($typeSchema) && isset($typeSchema['id'])) {
			$this -> complexTypes[$typeSchema['id']] = $typeSchema;
		}
	}

	/**
	 * Get type schema
	 *
	 * @param string ref
	 * @return string schema
	 */
	private function getType($ref) {
		if (isset($this -> complexTypes[$ref])) {
			return $this -> complexTypes[$ref];
		}
		return null;
	}

	/**
	 * Validate JSON
	 *
	 * @param string $schema JSON Schema
	 * @return boolean
	 */
	public function validate($schema) {
		$isVali = false;
		do {
			$schema = json_decode($schema, true);
			if (!is_array($schema) || !isset($schema['type'])) {
				$this -> addError('100', 'schema parse error. (PHP 5 >= 5.3.0) see json_last_error(void).');
				break;
			}

			$isVali = $this -> checkByType($this -> json, $key = null, $schema);
		} while (false);
		return $isVali;
	}

	/**
	 * check type: string
	 * http://tools.ietf.org/html/draft-zyp-json-schema-03#section-5.1
	 *
	 * @param string $value
	 * @param array $schema
	 */
	private function checkString($value, $key, $schema) {
		// string
		$isVali = false;
		do {
			if (!is_string($value) && !is_numeric($value)) {
				$this -> addError('101', $key . ' : ' . json_encode($value) . ' is not a string.', $key);
				break;
			}
			$len = strlen(trim($value));
			if (isset($schema['minLength'])) {
				if ($schema['minLength'] > $len) {
					$this -> addError('102', $key . ': ' . json_encode($value) . ' is too short.', $key);
					break;
				}
			}
			if (isset($schema['maxLength'])) {
				if ($schema['maxLength'] < $len) {
					$this -> addError('102', $key . ': ' . json_encode($value) . ' is too long.', $key);
					break;
				}
			}

			if (isset($schema['format'])) {
				switch ($schema['format']) {

					case 'date-time' :
						/**
						 * date-time This SHOULD be a date in ISO 8601 format of YYYY-MM-
						 * DDThh:mm:ssZ in UTC time. This is the recommended form of date/
						 * timestamp.
						 */
						break;
					case 'date' :
						/**
						 * date This SHOULD be a date in the format of YYYY-MM-DD. It is
						 * recommended that you use the"date-time"format instead of"date"
						 * unless you need to transfer only the date part.
						 */
						break;
					case 'time' :
						/**
						 * time This SHOULD be a time in the format of hh:mm:ss. It is
						 * recommended that you use the"date-time"format instead of"time"
						 * unless you need to transfer only the time part.
						 */
						break;
					case 'utc-millisec' :
						/**
						 * utc-millisec This SHOULD be the difference, measured in
						 * milliseconds, between the specified time and midnight, 00:00 of
						 * January 1, 1970 UTC. The value SHOULD be a number (integer or
						 * float).
						 */
						break;
					case 'regex' :
						/**
						 * regex A regular expression, following the regular expression
						 * specification from ECMA 262/Perl 5.
						 */
						if (isset($schema['pattern'])) {
							$pattern = $schema['pattern'];

							if (preg_match($pattern, $value)) {
								$isVali = true;
							} else {
								$this -> addError('101', $key . ':' . $value . ' does not match ' . $pattern, $key);
							}
						} else {
							$this -> addError('101', 'format-regex: pattern is undefined.', $key);
						}

						break;
					case 'color' :
						/**
						 * color This is a CSS color (like"#FF0000"or"red"), based on CSS
						 * 2.1 [W3C.CR-CSS21-20070719].
						 */
						break;
					case 'style' :
						/**
						 * style This is a CSS style definition (like"color: red; background-
						 * color:#FFF"), based on CSS 2.1 [W3C.CR-CSS21-20070719].
						 */
						break;
					case 'phone' :
						/**
						 * phone This SHOULD be a phone number (format MAY follow E.123).
						 * http://en.wikipedia.org/wiki/E.123
						 */
						if (preg_match("/^((0?[0-9]{2}) d{3,4}s?d{4}|+d{2} d{2} d{3,4}s?d{4})$/", $value)) {
							$isVali = true;
						} else {
							$this -> addError('101', $key . ': ' . $value . ' is not a phone number.', $key);
						}
						break;
					case 'uri' :
						/**
						 * uri This value SHOULD be a URI..
						 */
						if (filter_var($value, FILTER_VALIDATE_URL, FILTER_FLAG_QUERY_REQUIRED)) {
							$isVali = true;
						} else {
							$this -> addError('101', $key . ': ' . $value . ' is not a URI.', $key);
						}
						break;
					case 'email' :
						/**
						 * email This SHOULD be an email address.
						 */
						if (filter_var($value, FILTER_VALIDATE_EMAIL)) {
							$isVali = true;
						} else {
							$this -> addError('101', $key . ': ' . json_encode($value) . ' is not a email.', $key);
						}
						break;
					case 'ip-address' :
						/**
						 * ip-address This SHOULD be an ip version 4 address.
						 */
						if (filter_var($value, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
							$isVali = true;
						} else {
							$this -> addError('101', $key . ': ' . json_encode($value) . ' is not a ipv4 address.', $key);
						}

						break;
					case 'ipv6' :
						/**
						 * ipv6 This SHOULD be an ip version 6 address.
						 */
						if (filter_var($value, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
							$isVali = true;
						} else {
							$this -> addError('101', $key . ': ' . json_encode($value) . ' is not a ipv6 address.', $key);
						}
						break;
					case 'host-name' :
						/**
						 * host-name This SHOULD be a host-name.
						 */
						break;

					default :
						$this -> addError('101', $schema['format'] . ' is undefined.', $key);
						break;
				}
				break;
			}

			$isVali = true;
		} while (false);
		return $isVali;
	}

	/**
	 * check type: integer/double
	 *
	 * @param number $value
	 * @param array $schema
	 * @return boolean
	 */
	private function checkNumber($value, $key, $schema = null) {
		// number
		$isVali = false;
		do {

			if (!is_numeric($value)) {
				$this -> addError($value . ' is not a number.');
				break;
			}
			if (isset($schema['minimum'])) {
				if ($schema['minimum'] > $value) {
					$this -> addError('103', $key . ':' . json_encode($value) . ' is less than ' . $schema['minimum'], $key);
					break;
				}
			}
			if (isset($schema['maximum'])) {
				if ($schema['maximum'] < $value) {
					$this -> addError('103', $key . ':' . json_encode($value) . ' is bigger than ' . $schema['maximum'], $key);
					break;
				}
			}
			if (isset($schema['exclusiveMinimum'])) {
				if ($schema['exclusiveMinimum'] >= $value) {
					$this -> addError('103', $key . ':' . json_encode($value) . ' is less or than ' . $schema['exclusiveMinimum'] . ' or equal', $key);
					break;
				}
			}
			if (isset($schema['exclusiveMaximum'])) {
				if ($schema['exclusiveMaximum'] <= $value) {
					$this -> addError('103', $key . ':' . $value . ' is bigger than ' . $schema['exclusiveMaximum'] . ' or equal', $key);
					break;
				}
			}
			$isVali = true;
		} while (false);

		return $isVali;
	}

	/**
	 * check type: integer
	 *
	 * @param integer $value
	 * @param array $schema
	 * @return boolean
	 */
	private function checkInteger($value, $key, $schema) {
		// integer
		if (!is_integer($value)) {
			$this -> addError('101', $key . ': ' . $value . ' is not a integer', $key);
			return false;
		}
		return $this -> checkNumber($value, $schema);
	}

	/**
	 * check type: boolean
	 *
	 * @param boolean $value
	 * @param array $schema
	 * @return boolean
	 */
	private function checkBoolean($value, $key, $schema) {
		// boolean
		if (!is_bool($value)) {
			$this -> addError('101', $key . ': ' . json_encode($value) . ' is not a boolean.', $key);
			return false;
		}
		return true;
	}

	/**
	 * check type: object
	 *
	 * @param object $valueProp
	 * @param array $schema
	 * @return boolean
	 */
	private function checkObject($value, $key = null, $schema = null) {
		// object
		$isVali = false;

		do {
			if (!is_object($value)) {
				$this -> addError('101', $key . ': ' . json_encode($value) . ' is not an object.', $key);
				break;
			}
			if (isset($schema['properties']) && !empty($schema['properties'])) {
				$schemaProp = $schema['properties'];
				// 正确的required应该与properties同级
				if (!isset($schema['required']))
					$schema['required'] = null;
				if (!isset($schemaProp['required']))
					$schemaProp['required'] = null;
				$requireds = $schemaProp['required'] ? : $schema['required'];
				$valueProp = get_object_vars($value);
				$valueKeys = array_keys($valueProp);
				$schemaKeys = array_keys($schemaProp);
				$diffKeys = array_diff($valueKeys, $schemaKeys);
				if (!empty($diffKeys)) {
					foreach ($diffKeys as $key) {
						// property not defined / not optional

						if (!isset($schemaProp[$key]) || !isset($schemaProp[$key]['optional']) || !$schemaProp[$key]['optional']) {

							$this -> addError('101', $key . ': ' . json_encode($value) . ' is not exist,And its not a optional property.', $key);
							break 2;
						}
					}
				}
				if (is_array($requireds)) {
					if (is_object($value)) {
						foreach ($value as $key => $v) {
							if (isset($v)) {
							} else {
								if (in_array($key, $requireds)) {
									$this -> addError('101', $key . ': ' . json_encode($value) . ' is not exist,And its not a optional property.', $key);
									break 2;
								}
							}
							$key_arr[] = $key;
						}
						foreach ($requireds as $reval) {
							if (!in_array($reval, $key_arr)) {
								$this -> addError('101', $reval . ' is not exist,And its not a optional property.', $reval);
								break 2;
							}
						}
					}
				}

				foreach ($schemaProp as $key => $sch) {
					if (!isset($valueProp[$key])) {
						continue;
					}

					if (!$this -> checkByType($valueProp[$key], $key, $sch)) {
						break 2;
					}
				}
			}
			$isVali = true;
		} while (false);
		return $isVali;
	}

	/**
	 * check type: array
	 *
	 * @param array $value
	 * @param array $schema
	 * @return boolean
	 */
	private function checkArray($value, $key, $schema) {
		$isVali = false;
		do {
			if (!is_array($value)) {
				$this -> addError('101', $key . ' : ' . json_encode($value) . ' is not an array.', $key);
				break;
			}

			if (!isset($schema['items'])) {
				$this -> addError('101', $this -> error('schema: items schema is undefined.'), $key);
				break;
			}
			$size = count($value);
			if (isset($schema['minItems'])) {
				if ($schema['minItems'] > $size) {
					$this -> addError('102', $this -> error('array size: ') . $size . $this -> error(' is less than ') . $schema['minItems'] . '.', $key);
					break;
				}
			}
			if (isset($schema['maxItems'])) {
				if ($schema['maxItems'] < $size) {
					$this -> addError('102', $this -> error('array size: ') . $size . $this -> error(' is bigger than ') . $schema['maxItems'] . '.', $key);
					break;
				}
			}

			foreach ($value as $val) {
				if (!$this -> checkByType($val, '', $schema['items'])) {
					break 2;
				}
			}

			$isVali = true;
		} while (false);
		return $isVali;
	}

	/**
	 * check value based on type
	 *
	 * @param mixed $value
	 * @param array $schema
	 * @return boolean
	 */
	private function checkByType($value, $key = null, $schema) {

		$isVali = false;
		if ($schema && isset($schema['type'])) {
			// union types 联合类型
			if (is_array($schema['type'])) {
				$types = $schema['type'];
				foreach ($types as $type) {

					$schema['type'] = $type;
					$isVali = $this -> checkByType($value, $key, $schema);

					if ($isVali) {
						break;
					}
				}
			} else {
				$type = $schema['type'];

				switch ($type) {
					case 'boolean' :
						$isVali = $this -> checkBoolean($value, $key, $schema);
						break;
					case 'integer' :
						if (preg_match("/^[0-9]+$/", $value)) {
							$value = intval($value);
						}
						$isVali = $this -> checkInteger($value, $key, $schema);
						break;
					case 'number' :
						$isVali = $this -> checkNumber($value, $key, $schema);
						break;
					case 'string' :
						$isVali = $this -> checkString($value, $key, $schema);
						break;
					case 'array' :
						$isVali = $this -> checkArray($value, $key, $schema);
						break;
					case 'object' :
						$isVali = $this -> checkObject($value, $key, $schema);

						break;
					case 'enum' :
						$isVali = is_null($value);
						break;
					case 'null' :
						$isVali = is_null($value);
						break;
					case 'any' :
						$isVali = true;
						break;
					default :
						$this -> addError('101', $this -> error('type_schema : ') . $value . $this -> error(' is undefined.'), $key);
						break;
				}
			}
		}
		if (isset($schema['$ref'])) {
			$isVali = $this -> checkByType($value, $key, $this -> getType($schema['$ref']));
		}
		return $isVali;
	}

	/**
	 * Get errors
	 *
	 * @return array errors
	 */
	public function getErrors() {

		return json_encode(['error' => $this -> errors], true);
	}
	/**
	 * add error message
	 * @param string $msg
	 */
	protected function addError($code = null, $msg = null, $key = null) {
		$this -> errors = array('contents' => $this -> error('validate fail'), 'return_code' => '1100', 'message' => $this -> error($key) . $this -> error(': the format is not correct.'), 'timestamp' => time());
	}

	/**
	 * 报错信息
	 */
	protected function error($message) {
		// 入口已经处理了语种，这里再加没有意义
		// $header = getallheaders();
		// //获取头部信息，得到需要返回的语言
		// $lan = $header['lan'];
		// if($lan == 'zh_CN'){
		// 	putenv('LANG=zh_CN');
		// 	setlocale(LC_ALL, 'zh_CN'); //指定要用的语系，如：en_US、zh_CN、zh_TW
		// }elseif ($lan == 'zh_TW'){
		// 	putenv('LANG=zh_TW');
		// 	setlocale(LC_ALL, 'zh_TW'); //指定要用的语系，如：en_US、zh_CN、zh_TW
		// }elseif ($lan == 'en_US') {
		// 	putenv('LANG=en_US');
		// 	setlocale(LC_ALL, 'en_US'); //指定要用的语系，如：en_US、zh_CN、zh_TW
		// }
		// if ($lan == null) {
		// 	putenv('LANG=en_US');
		// 	setlocale(LC_ALL, 'en_US');
		// }
		//   //默认开启zh_CN
		// $locale = "zh_CN.utf8";
		// setlocale(LC_ALL, $locale);
		// //设置翻译文本域，下面的代码就会让程序去locale/zh_CN/LC_MESSAGES/default.mo去寻找翻译文件
		// bindtextdomain("Education", $_SERVER['DOCUMENT_ROOT'].'locale');
		// textdomain("Education");
		$message = _($message);
		return $message;
	}

}
?>