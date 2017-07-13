<?php

namespace Envo\Support;

class Validator
{
	public $data = [];
    public $errors = [];
	public $rules = [];
	
	/**
	 * Validate given data
	 *
	 * @param $data
	 * @param $validations
	 *
	 * @return bool|array|self
	 */
	public static function make($data, $validations)
	{
		$instance = resolve(self::class);
		$instance->data = $data;

		$response = array();
		foreach ($validations as $key => $validation) {
			$response[$key] = $instance->validate($key, $validation);
		}

        $instance->errors = array_filter($response);

        return $instance;
	}

    public function fails()
    {
        return $this->errors ? true : false;
    }

    public function isValid()
    {
        return $this->errors ? false : true;
    }

    public function errors()
    {
        return $this->errors;
    }

    public function getErrors()
    {
        return $this->error();
    }

	public function validate($key, $validations)
	{
		$validators = explode('|', $validations);
        $isObject = is_object($this->data);

		$response = array();
		foreach ($validators as $validator) {
			$parameters = null;
			if( strpos($validator, ':') !== false){
				$parts = explode(':', $validator);
				$validator = $parts[0];
				$parameters = $parts[1];
			}
			$func = 'validate' . ucfirst($validator);

			if( ! method_exists($this, $func) ) throw new \Exception("Validator method {$func} not found", 500);
            if( $isObject ) {
                $value = isset($this->data->$key) ? $this->data->$key : null;
            } else {
                $value = isset($this->data[$key]) ? $this->data[$key] : null;
            }

			$resp = $this->$func($key, $value, $parameters);

			if( ! $resp || is_string($resp) ) {
                $response[] = $this->addError($validator, $key, $parameters, $value);
            }
		}

		return $response ?: null;
	}

	protected function addError($validator, $attribute, $parameters, $value = null)
	{
		$message = \_t('validation.' . $validator, [$attribute]);
        $message = $this->doReplacements($message, $attribute, $validator, $parameters);

        if( is_array($message) ) {
            $type = $value ? $this->getType($value) : 'numeric';
            if( isset($message[$type]) ) {
                $message = $message[$type];
            }
        }

        return $this->errors[] = $message;
	}

    protected function getType($value)
    {
        if( is_array($value) ) {
            return 'array';
        }
        if( is_numeric($value) ) {
            return 'numeric';
        }
        if( is_string($value) ) {
            return 'string';
        }

        return 'file';
    }

	protected function validateRequired($attribute, $value)
	{
		if (is_null($value)) {
            return false;
        } elseif (is_string($value) && trim($value) === '') {
            return false;
        } elseif ((is_array($value) || $value instanceof \Countable) && count($value) < 1) {
            return false;
        }

        return true;
	}

	protected function validateSame($attribute, $value, $parameters)
	{
		$this->requireParameterCount(1, $parameters, 'same');
		$other = resolve(Arr::class)->get($this->data, $parameters);
        return isset($other) && $value === $other;
	}

	/**
     * Require a certain number of parameters to be present.
     *
     * @param  int    $count
     * @param  array  $parameters
     * @param  string  $rule
     * @return void
     *
     * @throws \InvalidArgumentException
     */
    protected function requireParameterCount($count, $parameters, $rule)
    {
        if (count($parameters) < $count) {
            throw new \InvalidArgumentException("Validation rule $rule requires at least $count parameters.");
        }
    }

    /**
     * Validate that an attribute is a valid e-mail address.
     *
     * @param  string  $attribute
     * @param  mixed   $value
     * @return bool
     */
    protected function validateEmail($attribute, $value)
    {
        return filter_var($value, FILTER_VALIDATE_EMAIL) !== false;
    }

    /**
     * Validate that an attribute is a valid IP.
     *
     * @param  string  $attribute
     * @param  mixed   $value
     * @return bool
     */
    protected function validateIp($attribute, $value)
    {
        return filter_var($value, FILTER_VALIDATE_IP) !== false;
    }

    /**
     * Validate that an attribute contains only alphabetic characters.
     *
     * @param  string  $attribute
     * @param  mixed   $value
     * @return bool
     */
    protected function validateAlpha($attribute, $value)
    {
        return is_string($value) && preg_match('/^[\pL\pM]+$/u', $value);
    }

    /**
     * Validate that an attribute contains only alpha-numeric characters.
     *
     * @param  string  $attribute
     * @param  mixed   $value
     * @return bool
     */
    protected function validateAlphaNum($attribute, $value)
    {
        if (! is_string($value) && ! is_numeric($value)) {
            return false;
        }

        return preg_match('/^[\pL\pM\pN]+$/u', $value) > 0;
    }

    /**
     * Validate that an attribute contains only alpha-numeric characters, dashes, and underscores.
     *
     * @param  string  $attribute
     * @param  mixed   $value
     * @return bool
     */
    protected function validateAlphaDash($attribute, $value)
    {
        if (! is_string($value) && ! is_numeric($value)) {
            return false;
        }

        return preg_match('/^[\pL\pM\pN_-]+$/u', $value) > 0;
    }

    /**
     * Validate that an attribute passes a regular expression check.
     *
     * @param  string  $attribute
     * @param  mixed   $value
     * @param  array   $parameters
     * @return bool
     */
    protected function validateRegex($attribute, $value, $parameters)
    {
        if (! is_string($value) && ! is_numeric($value)) {
            return false;
        }

        $this->requireParameterCount(1, $parameters, 'regex');

        return preg_match($parameters[0], $value) > 0;
    }

    /**
     * Validate that an attribute is a valid date.
     *
     * @param  string  $attribute
     * @param  mixed   $value
     * @return bool
     */
    protected function validateDate($attribute, $value)
    {
        if ($value instanceof \DateTime) {
            return true;
        }

        if ((! is_string($value) && ! is_numeric($value)) || strtotime($value) === false) {
            return false;
        }

        $date = date_parse($value);

        return checkdate($date['month'], $date['day'], $date['year']);
    }

    /**
     * Validate that an attribute matches a date format.
     *
     * @param  string  $attribute
     * @param  mixed   $value
     * @param  array   $parameters
     * @return bool
     */
    protected function validateDateFormat($attribute, $value, $parameters)
    {
        $this->requireParameterCount(1, $parameters, 'date_format');

        if (! is_string($value) && ! is_numeric($value)) {
            return false;
        }

        $parsed = date_parse_from_format($parameters[0], $value);

        return $parsed['error_count'] === 0 && $parsed['warning_count'] === 0;
    }

    /**
     * Validate that an attribute is a valid timezone.
     *
     * @param  string  $attribute
     * @param  mixed   $value
     * @return bool
     */
    protected function validateTimezone($attribute, $value)
    {
        try {
            new \DateTimeZone($value);
        } catch (\Exception $e) {
            return false;
        }

        return true;
    }

    /**
     * Validate that an attribute exists even if not filled.
     *
     * @param  string  $attribute
     * @param  mixed   $value
     * @return bool
     */
    protected function validatePresent($attribute, $value)
    {
        return resolve(Arr::class)->has(array_merge($this->data, $this->files), $attribute);
    }

    /**
     * Validate the given attribute is filled if it is present.
     *
     * @param  string  $attribute
     * @param  mixed   $value
     * @return bool
     */
    protected function validateFilled($attribute, $value)
    {
        if (resolve(Arr::class)->has(array_merge($this->data, $this->files), $attribute)) {
            return $this->validateRequired($attribute, $value);
        }

        return true;
    }

    /**
     * Determine if any of the given attributes fail the required test.
     *
     * @param  array  $attributes
     * @return bool
     */
    protected function anyFailingRequired(array $attributes)
    {
        foreach ($attributes as $key) {
            if (! $this->validateRequired($key, $this->getValue($key))) {
                return true;
            }
        }

        return false;
    }

    /**
     * Determine if all of the given attributes fail the required test.
     *
     * @param  array  $attributes
     * @return bool
     */
    protected function allFailingRequired(array $attributes)
    {
        foreach ($attributes as $key) {
            if ($this->validateRequired($key, $this->getValue($key))) {
                return false;
            }
        }

        return true;
    }

    /**
     * Validate that an attribute exists when any other attribute exists.
     *
     * @param  string  $attribute
     * @param  mixed   $value
     * @param  mixed   $parameters
     * @return bool
     */
    protected function validateRequiredWith($attribute, $value, $parameters)
    {
        if (! $this->allFailingRequired($parameters)) {
            return $this->validateRequired($attribute, $value);
        }

        return true;
    }

    /**
     * Validate that an attribute exists when all other attributes exists.
     *
     * @param  string  $attribute
     * @param  mixed   $value
     * @param  mixed   $parameters
     * @return bool
     */
    protected function validateRequiredWithAll($attribute, $value, $parameters)
    {
        if (! $this->anyFailingRequired($parameters)) {
            return $this->validateRequired($attribute, $value);
        }

        return true;
    }

    /**
     * Validate that an attribute exists when another attribute does not.
     *
     * @param  string  $attribute
     * @param  mixed   $value
     * @param  mixed   $parameters
     * @return bool
     */
    protected function validateRequiredWithout($attribute, $value, $parameters)
    {
        if ($this->anyFailingRequired($parameters)) {
            return $this->validateRequired($attribute, $value);
        }

        return true;
    }

    /**
     * Validate that an attribute exists when all other attributes do not.
     *
     * @param  string  $attribute
     * @param  mixed   $value
     * @param  mixed   $parameters
     * @return bool
     */
    protected function validateRequiredWithoutAll($attribute, $value, $parameters)
    {
        if ($this->allFailingRequired($parameters)) {
            return $this->validateRequired($attribute, $value);
        }

        return true;
    }

    /**
     * Validate that an attribute exists when another attribute has a given value.
     *
     * @param  string  $attribute
     * @param  mixed   $value
     * @param  mixed   $parameters
     * @return bool
     */
    protected function validateRequiredIf($attribute, $value, $parameters)
    {
        $this->requireParameterCount(2, $parameters, 'required_if');

        $data = resolve(Arr::class)->get($this->data, $parameters[0]);

        $values = array_slice($parameters, 1);

        if (is_bool($data)) {
            array_walk($values, function (&$value) {
                if ($value === 'true') {
                    $value = true;
                } elseif ($value === 'false') {
                    $value = false;
                }
            });
        }

        if (in_array($data, $values)) {
            return $this->validateRequired($attribute, $value);
        }

        return true;
    }

    /**
     * Validate that an attribute exists when another attribute does not have a given value.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @param  mixed  $parameters
     * @return bool
     */
    protected function validateRequiredUnless($attribute, $value, $parameters)
    {
        $this->requireParameterCount(2, $parameters, 'required_unless');

        $data = resolve(Arr::class)->get($this->data, $parameters[0]);

        $values = array_slice($parameters, 1);

        if (! in_array($data, $values)) {
            return $this->validateRequired($attribute, $value);
        }

        return true;
    }

    /**
     * Get the number of attributes in a list that are present.
     *
     * @param  array  $attributes
     * @return int
     */
    protected function getPresentCount($attributes)
    {
        $count = 0;

        foreach ($attributes as $key) {
            if (resolve(Arr::class)->get($this->data, $key) || Arr::get($this->files, $key)) {
                $count++;
            }
        }

        return $count;
    }

    /**
     * Validate that the values of an attribute is in another attribute.
     *
     * @param  string  $attribute
     * @param  mixed   $value
     * @param  array   $parameters
     * @return bool
     */
    protected function validateInArray($attribute, $value, $parameters)
    {
        $this->requireParameterCount(1, $parameters, 'in_array');

        $explicitPath = $this->getLeadingExplicitAttributePath($parameters[0]);

        $attributeData = $this->extractDataFromPath($explicitPath);

        $otherValues = resolve(Arr::class)->where(resolve(Arr::class)->dot($attributeData), function ($value, $key) use ($parameters) {
            return \Str::is($parameters[0], $key);
        });

        return in_array($value, $otherValues);
    }

    /**
     * Validate that an attribute has a matching confirmation.
     *
     * @param  string  $attribute
     * @param  mixed   $value
     * @return bool
     */
    protected function validateConfirmed($attribute, $value)
    {
        return $this->validateSame($attribute, $value, [$attribute.'_confirmation']);
    }

    /**
     * Get the explicit part of the attribute name.
     *
     * E.g. 'foo.bar.*.baz' -> 'foo.bar'
     *
     * Allows us to not spin through all of the flattened data for some operations.
     *
     * @param  string  $attribute
     * @return string
     */
    protected function getLeadingExplicitAttributePath($attribute)
    {
        return rtrim(explode('*', $attribute)[0], '.') ?: null;
    }

    protected function validateMin($attribute, $value, $parameters)
    {
        return $this->getSize($attribute, $value) >= $parameters[0];
    }

    /**
     * Get the size of an attribute.
     *
     * @param  string  $attribute
     * @param  mixed   $value
     * @return mixed
     */
    protected function getSize($attribute, $value)
    {
        // $hasNumeric = $this->hasRule($attribute, $this->numericRules);

        // This method will determine if the attribute is a number, string, or file and
        // return the proper size accordingly. If it is a number, then number itself
        // is the size. If it is a file, we take kilobytes, and for a string the
        // entire length of the string will be considered the attribute size.
        if (is_numeric($value)) {
            return $value;
        } elseif (is_array($value)) {
            return count($value);
        } elseif ($value instanceof File) {
            return $value->getSize() / 1024;
        }

        return mb_strlen($value);
    }

    /**
     * Determine if the given attribute has a rule in the given set.
     *
     * @param  string  $attribute
     * @param  string|array  $rules
     * @return bool
     */
    public function hasRule($attribute, $rules)
    {
        return ! is_null($this->getRule($attribute, $rules));
    }

    /**
     * Get a rule and its parameters for a given attribute.
     *
     * @param  string  $attribute
     * @param  string|array  $rules
     *
	 * @return array|bool|null
	 */
    protected function getRule($attribute, $rules)
    {
        if (! array_key_exists($attribute, $this->rules)) {
            return false;
        }

        $rules = (array) $rules;

        foreach ($this->rules[$attribute] as $rule) {
            list($rule, $parameters) = $this->parseRule($rule);

            if (in_array($rule, $rules)) {
                return [$rule, $parameters];
            }
        }
    }

    /**
     * Replace all error message place-holders with actual values.
     *
     * @param  string  $message
     * @param  string  $attribute
     * @param  string  $rule
     * @param  array   $parameters
     * @return string
     */
    protected function doReplacements($message, $attribute, $rule, $parameters)
    {
        $value = ($attribute);

        $message = str_replace(
            [':attribute', ':ATTRIBUTE', ':Attribute'],
            [$value, Str::upper($value), Str::ucfirst($value)],
            $message
        );

        if (isset($this->replacers[Str::snake($rule)])) {
            $message = $this->callReplacer($message, $attribute, Str::snake($rule), $parameters);
        } elseif (method_exists($this, $replacer = "replace{$rule}")) {
            $message = $this->$replacer($message, $attribute, $rule, $parameters);
        }

        return $message;
    }

    /**
     * Replace all place-holders for the min rule.
     *
     * @param  string  $message
     * @param  string  $attribute
     * @param  string  $rule
     * @param  array   $parameters
     * @return string
     */
    protected function replaceMin($message, $attribute, $rule, $parameters)
    {
        return str_replace(':min', $parameters[0], $message);
    }

    /**
     * Replace all place-holders for the same rule.
     *
     * @param  string  $message
     * @param  string  $attribute
     * @param  string  $rule
     * @param  array   $parameters
     * @return string
     */
    protected function replaceSame($message, $attribute, $rule, $parameters)
    {
        return str_replace(':other', $parameters, $message);
    }
}