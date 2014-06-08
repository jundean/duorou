<?php
/**
 * This file contains the foundation classes for component-based programming.
 */

/**
 * CComponent is the base class for all components.
 *
 * CComponent implements the protocol of defining, using properties.
 *
 * A property is defined by a getter method, and/or a setter method.
 * Properties can be accessed in the way like accessing normal object members.
 * Reading or writing a property will cause the invocation of the corresponding
 * getter or setter method, e.g
 * <pre>
 * $a=$component->text;     // equivalent to $a=$component->getText();
 * $component->text='abc';  // equivalent to $component->setText('abc');
 * </pre>
 * The signatures of getter and setter methods are as follows,
 * <pre>
 * // getter, defines a readable property 'text'
 * public function getText() { ... }
 * // setter, defines a writable property 'text' with $value to be set to the property
 * public function setText($value) { ... }
 * </pre>
 *
 * Property names are case-insensitive.
 */
class CComponent {
    /**
     * Init this component. Can be overwrited.
     */
    public function init() {
    }

    /**
     * Returns a property value based on its name.
     * Do not call this method. This is a PHP magic method that we override
     * to allow using the following syntax to read a property:
     * <pre>
     * $value=$component->propertyName;
     * </pre>
     * @param string $name the property name
     * @return mixed the property value
     * @throws CException if the property is not defined
     * @see __set
     */
    public function __get($name) {
        $getter = 'get' . $name;
        if (method_exists($this, $getter))
            return $this->$getter();
        throw new CException(sprintf('Property "%s.%s" is not defined.', get_class($this), $name));
    }

    /**
     * Sets value of a component property.
     * Do not call this method. This is a PHP magic method that we override
     * to allow using the following syntax to set a property or attach an event handler
     * <pre>
     * $this->propertyName=$value;
     * </pre>
     * @param string $name the property name
     * @param mixed $value the property value
     * @return mixed
     * @throws CException if the property is not defined or the property is read only.
     * @see __get
     */
    public function __set($name, $value) {
        $setter = 'set' . $name;
        if (method_exists($this, $setter))
            return $this->$setter($value);
        if (method_exists($this, 'get' . $name))
            throw new CException(sprintf('Property "%s.%s" is read only.', get_class($this), $name));
        else
            throw new CException(sprintf('Property "%s.%s" is not defined.', get_class($this), $name));
    }

    /**
     * Checks if a property value is null.
     * Do not call this method. This is a PHP magic method that we override
     * to allow using isset() to detect if a component property is set or not.
     * @param string $name the property name
     * @return boolean
     */
    public function __isset($name) {
        $getter = 'get' . $name;
        if (method_exists($this, $getter))
            return $this->$getter() !== null;
        return false;
    }

    /**
     * Sets a component property to be null.
     * Do not call this method. This is a PHP magic method that we override
     * to allow using unset() to set a component property to be null.
     * @param string $name the property name
     * @throws CException if the property is read only.
     * @return mixed
     */
    public function __unset($name) {
        $setter = 'set' . $name;
        if (method_exists($this, $setter))
            $this->$setter(null);
        elseif (method_exists($this, 'get' . $name))
            throw new CException(sprintf('Property "%s.%s" is read only.', get_class($this), $name));
    }

    /**
     * Determines whether a property is defined.
     * A property is defined if there is a getter or setter method
     * defined in the class. Note, property names are case-insensitive.
     * @param string $name the property name
     * @return boolean whether the property is defined
     * @see canGetProperty
     * @see canSetProperty
     */
    public function hasProperty($name) {
        return method_exists($this, 'get' . $name) || method_exists($this, 'set' . $name);
    }

    /**
     * Determines whether a property can be read.
     * A property can be read if the class has a getter method
     * for the property name. Note, property name is case-insensitive.
     * @param string $name the property name
     * @return boolean whether the property can be read
     * @see canSetProperty
     */
    public function canGetProperty($name) {
        return method_exists($this, 'get' . $name);
    }

    /**
     * Determines whether a property can be set.
     * A property can be written if the class has a setter method
     * for the property name. Note, property name is case-insensitive.
     * @param string $name the property name
     * @return boolean whether the property can be written
     * @see canGetProperty
     */
    public function canSetProperty($name) {
        return method_exists($this, 'set' . $name);
    }
}
