<?php
// +---------------------------------------------------------------------+
// | PHP Version 4                                                       |
// +---------------------------------------------------------------------+
// | Copyright (c) 1997, 1998, 1999, 2000, 2001 The PHP Group            |
// +---------------------------------------------------------------------+
// | This source file is subject to version 2.0 of the PHP license,      |
// | that is bundled with this package in the file LICENSE, and is       |
// | available at through the world-wide-web at                          |
// | http://www.php.net/license/2_02.txt.                                |
// | If you did not receive a copy of the PHP license and are unable to  |
// | obtain it through the world-wide-web, please send a note to         |
// | license@php.net so we can mail you a copy immediately.              |
// +---------------------------------------------------------------------+
// | Author: Bertrand Mansion <bmansion@mamasam.com>                     |
// +---------------------------------------------------------------------+
//
// $Id$

require_once('Config.php');

/**
* Interface for Config containers
*
* @author   Bertrand Mansion <bmansion@mamasam.com>
* @package  Config
*/
class Config_Container {

    /**
    * Container object type
    * Ex: section, directive, comment, blank
    * @var  string
    */
    var $type;

    /**
    * Container object name
    * @var  string
    */
    var $name = '';

    /**
    * Container object content
    * @var  string
    */
    var $content = '';

    /**
    * Container object children
    * @var  array
    */
    var $children;

    /**
    * Reference to container object's parent
    * @var  object
    */
    var $parent;

    /**
    * Constructor
    *
    * @param  string  type      (optional)Type of container object
    * @param  string  name      (optional)Name of container object
    * @param  string  content   (optional)Content of container object
    */
    function Config_Container($type = '', $name = '', $content = '')
    {
        $this->type       = $type;
        $this->name       = $name;
        $this->content    = $content;
        $this->parent     = null;
    } // end constructor

    /**
    * Create a child for this item.
    * @param  string  type      type of item: directive, section, comment, blank...
    * @param  mixed   item      item name
    * @param  string  content   item content
    * @return object  reference to new item or Pear_Error
    */
    function &createItem($type, $item, $content, $where = 'bottom', $target = null)
    {
        if ($this->type != 'section') {
            return PEAR::raiseError('Config_Container::createItem must be called on a section type object.', null, PEAR_ERROR_RETURN);
        }
        if (is_null($target)) {
            $target =& $this;
        }
        if (!is_object($target) || !is_a($target, 'Config_Container')) {
            return PEAR::raiseError('Target must be a Config_Container object in Config_Container::createItem.', null, PEAR_ERROR_RETURN);
        }

        switch ($where) {
            case 'before':
                $index = $target->getItemIndex();
                break;
            case 'after':
                $index = $target->getItemIndex()+1;
                break;
            case 'top':
                $index = 0;
                break;
            case 'bottom':
                $index = -1;
                break;
            default:
                return PEAR::raiseError('Use only top, bottom, before or after in Config_Container::insertItem.', null, PEAR_ERROR_RETURN);
        }
        if (isset($index) && $index >= 0) {
            array_splice($this->children, $index, 0, 'tmp');
        } else {
            $index = sizeof($this->children);
        }
        $currentContainer = get_class($this);
        $itemObj =& new $currentContainer($type, $item, $content);
        $this->children[$index] =& $itemObj;
        $this->children[$index]->parent =& $this;

        return $this->children[$index];
    } // end func &createItem
    
    /**
    * Adds an item to this item.
    * @param  object   item      a container object
    * @return true on success, Pear_Error on error
    */
    function addItem(&$item, $where = 'bottom', $target = null)
    {

        $index = sizeof($this->children);
        if (is_object($item) && is_a($item, 'config_container')) {
            $this->children[$index] =& $item;
            $this->children[$index]->parent =& $this;
            return true;
        } else {
            return PEAR::raiseError('Child must be a Config_Container object for Config_Container::addItem.', null, PEAR_ERROR_RETURN);
        }
    } // end func addItem

    /**
    * Adds a comment to this item.
    * This is a helper method that calls createItem
    *
    * @param  string  content   object content
    * @return object  reference to new item
    */
    function &createComment($content = '', $where = 'bottom', $target = null)
    {
        $item =& $this->createItem('comment', null, $content, $where, $target);
        return $item;
    } // end func &createComment

    /**
    * Adds a blank line to this item.
    * This is a helper method that calls createItem
    *
    * @return object  reference to new item
    */
    function &createBlank($where = 'bottom', $target = null)
    {
        $item =& $this->createItem('blank', null, null, $where, $target);
        return $item;
    } // end func &createBlank

    /**
    * Adds a directive to this item.
    * This is a helper method that calls createItem
    *
    * @param  string  name      Name of new directive
    * @param  string  content   Content of new directive
    * @return object  reference to new item
    */
    function &createDirective($name, $content, $where = 'bottom', $target = null)
    {
        $item =& $this->createItem('directive', $name, $content, $where, $target);
        return $item;
    } // end func &createDirective

    /**
    * Adds a section to this item.
    * This is a helper method that calls createItem
    *
    * @param  mixed   name      Name of new section or container object
    * @return object  reference to new item
    */
    function &createSection($section, $content = null, $where = 'bottom', $target = null)
    {
        $item =& $this->createItem('section', $section, $content, $where, $target);
        return $item;
    } // end func &createSection

    /**
    * Tries to find the specified item(s) and returns the objects.
    *
    * Examples:
    * $directives =& $obj->getItem('directive');
    * $directive_bar_4 =& $obj->getItem('directive', 'bar', null, 4);
    * $section_foo =& $obj->getItem('section', 'foo');
    *
    * This method can only be called on an object of type 'section'.
    * Note that root is a section.
    * This method is not recursive and tries to keep the current structure.
    *
    * @param  string    type    type of item: directive, section, comment, blank...
    * @param  mixed     name    (optional)item name
    * @param  mixed     content (optional)find item with this content
    * @param  int       index   (optional)index of the item in the returned object list.
    *                           If it is not set, will try to return the last item with this name.
    * @return mixed  reference to item found or false when not found
    */
    function &getItem($type, $name = null, $content = null, $index = -1)
    {
        if ($this->type != 'section') {
            return PEAR::raiseError('Config_Container::getItem must be called on a section type object.', null, PEAR_ERROR_RETURN);
        }
        $testFields[] = 'type';
        if (!is_null($name)) {
            $testFields[] = 'name';
        }
        if (!is_null($content)) {
            $testFields[] = 'content';
        }
        $itemsArr = array();
        $fieldsToMatch = count($testFields);
        for ($i = 0; $i < count($this->children); $i++) {
            $match = 0;
            reset($testFields);
            foreach($testFields as $field) {
                if ($this->children[$i]->$field == ${$field}) {
                    $match++;
                }
            }
            if ($match == $fieldsToMatch) {
                $itemsArr[] =& $this->children[$i];
            }
        }
        if ($index >= 0) {
            if (isset($itemsArr[$index])) {
                return $itemsArr[$index];
            } else {
                return false;
            }
        } else {
            if (count($itemsArr) > 0) {
                return $itemsArr[count($itemsArr)-1];
            } else {
                return false;
            }
        }
    } // end func &getItem

    /**
    * Returns how many children this container has
    *
    * @param  string    type    (optional)type of children counted
    * @param  string    type    (optional)name of children counted
    * @return int  number of children found
    */
    function countChildren($type = null, $name = null)
    {
        if ($this->type != 'section') {
            return PEAR::raiseError('Config_Container::getChildrenNum must be called on a section type object.', null, PEAR_ERROR_RETURN);
        }
        if (is_null($type) && is_null($name)) {
            return count($this->children);
        }
        $count = 0;
        if (isset($name) && isset($type)) {
            for ($i = 0; $i < count($this->children); $i++) {
                if ($this->children[$i]->name == $name && 
                    $this->children[$i]->type == $type) {
                    $count++;
                }
            }
            return $count;
        }
        if (isset($type)) {
            for ($i = 0; $i < count($this->children); $i++) {
                if ($this->children[$i]->type == $type) {
                    $count++;
                }
            }
            return $count;
        }
        if (isset($name)) {
            // Some directives can have the same name
            for ($i = 0; $i < count($this->children); $i++) {
                if ($this->children[$i]->name == $name) {
                    $count++;
                }
            }
            return $count;
        }
    } // end func &countChildren

    /**
    * Inserts an item to a specified position.
    * The position is relative to a target object if it is defined.
    * Example:
    * $obj->insertItem('section', 'new section', '', 'top');
    * $obj->insertItem('comment', 'A comment line', 'before', $servertype);
    *
    * This method can only be called on an object of type 'section'.
    * This method is not recursive and tries to keep the current structure.
    *
    * @param  string    type    type of item: directive, section, comment, blank...
    * @param  mixed     item    item name or item object
    * @param  string    content (optional)item content
    * @param  string    where   (optional)position: top, bottom, before or after.
    * @param  object    target  (optional)object to insert before or after.
    * @return mixed  reference to inserted item or PEAR_Error
    */
    function &insertItem($type, $item, $content = '', $where = 'bottom', $target = null)
    {
        if ($this->type != 'section') {
            return PEAR::raiseError('Config_Container::insertItem must be called on a section type object.', null, PEAR_ERROR_RETURN);
        }
        if (is_null($target)) {
            $target =& $this;
        }
        if (!is_object($target)) {
            return PEAR::raiseError('Target must be an object in Config_Container::insertItem.', null, PEAR_ERROR_RETURN);
        }
        if (!is_a($target, 'Config_Container')) {
            return PEAR::raiseError('Target must be an Config_Container object in Config_Container::insertItem.', null, PEAR_ERROR_RETURN);
        }
        switch ($where) {
            case 'before':
                $index = $target->getItemIndex();
                break;
            case 'after':
                $index = $target->getItemIndex()+1;
                break;
            case 'top':
                $index = 0;
                break;
            case 'bottom':
                $index = -1;
                break;
            default:
                return PEAR::raiseError('Use only top, bottom, before or after in Config_Container::insertItem.', null, PEAR_ERROR_RETURN);
        }
        if (isset($index) && $index >= 0) {
            array_splice($this->children, $index, 0, 'tmp');
            if (is_object($item) && is_a($item, 'config_container')) {
                $this->children[$index] =& $item;
            } elseif (is_string($item)) {
                $currentContainer = get_class($this);
                $itemObj =& new $currentContainer($type, $item, $content);
                $this->children[$index] =& $itemObj;
                $this->children[$index]->parent =& $this; 
            }
            return $this->children[$index];
        } else {
            return $this->createItem($type, $item, $content);
        }
    } // end func &insertItem

    /**
    * Deletes an item (section, directive, comment...) from the current object
    * TODO: recursive remove in sub-sections
    * @return mixed  true if object was removed, false if not, or PEAR_Error if root
    */
    function removeItem()
    {
        if (is_null($this->parent)) {
            return PEAR::raiseError('Cannot remove root item in Config_Container::removeItem.', null, PEAR_ERROR_RETURN);
        }
        $index = $this->getItemIndex();
        if (!is_null($index)) {
            array_splice($this->parent->children, $index, 1);
            return true;
        }
        return false;
    } // end func removeItem

    /**
    * Returns the item position in its parent children array.
    * @return int  returns int or null if root object
    */
    function getItemIndex()
    {
        if (is_object($this->parent)) {
            // I couldn't think of a better way to compare object references
            // so I compare object contents for now.
            // Maybe I should use an ID or a flag ?
            $pchildren =& $this->parent->children;
            for ($i = 0; $i < count($pchildren); $i++) {
                if ($pchildren[$i]->name == $this->name &&
                    $pchildren[$i]->content == $this->content &&
                    $pchildren[$i]->type == $this->type) {
                    return $i;
                }
            }
        }
        return; 
    } // end func getItemIndex

    /**
    * Returns the item parent object.
    * @return object  returns parent object or null if root object
    */
    function &getParent()
    {
        return $this->parent;
    } // end func &getParent

    /**
    * Set this item's name.
    * @return void
    */
    function setName($name)
    {
        $this->name = $name;
    } // end func setName

    /**
    * Get this item's name.
    * @return string    item's type
    */
    function getName()
    {
        return $this->name;
    } // end func getName

    /**
    * Set this item's content.
    * @return void
    */
    function setContent($content)
    {
        $this->content = $content;
    } // end func setContent

    /**
    * Set a children directive content.
    * This is an helper method calling getItem and insertItem or setContent for you.
    * If the directive does not exist, it will be created at the bottom.
    *
    * @param  string    name    Name of the directive to look for
    * @param  mixed     content New content, a string or a container object
    * @param  int       index   Index of the directive to set,
    *                           in case there are more than one directive
    *                           with the same name
    * @return object    newly set directive
    */
    function &setDirective($name, $content, $index = -1)
    {
        $item =& $this->getItem('directive', $name, null, $index);
        if (PEAR::isError($item)) {
            // Directive does not exist, will create one
            unset($item);
            $item =& addItem('directive', $name, $content);
        } else {
            // Change existing directive value
            $item->setContent($content);
        }
        return $item;
    } // end func setDirective

    /**
    * Get this item's content.
    * @return mixed item's value
    */
    function getContent()
    {
        return $this->content;
    } // end func getContent

    /**
    * Set this item's type.
    * @return void
    */
    function setType($type)
    {
        $this->type = $type;
    } // end func setType

    /**
    * Get this item's type.
    * @return string    item's type
    */
    function getType()
    {
        return $this->type;
    } // end func getType

    /**
    * Is this item root, in a config container object
    * @return bool    true if item is root
    */
    function isRoot()
    {
        if (is_null($this->parent) && $this->name == 'root') {
            return true;
        }
        return false;
    } // end func isRoot

    /**
    * Interface method
    * @param    string  configType  Type of configuration used to generate the string
    * @access   public
    * @return null
    */
    function toString($configType = '')
    {
        $string = '';
        $configType = strtolower($configType);
        if (!Config::isConfigTypeRegistered($configType)) {
            return PEAR::raiseError("Configuration type '$configType' is not registered in Config_Container::toString.", null, PEAR_ERROR_RETURN);
        }
        $className = $GLOBALS['CONFIG_TYPES'][$configType][1];
        $includeFile = $GLOBALS['CONFIG_TYPES'][$configType][0];
        include_once($includeFile);
        return eval("return $className::toString('$configType');");
    } // end func toString

    /**
    * Returns a key/value pair array of the container and its children.
    * Format : section[directive][index] = value
    * index is here because multiple directives can have the same name.
    * @access   public
    * @return array
    */
    function toArray()
    {
        $array[$this->name] = array();
        switch ($this->type) {
            case 'directive':
                $array[$this->name] = $this->content;
                break;
            case 'section':
                if (count($this->children) > 0) {
                    for ($i = 0; $i < count($this->children); $i++) {
                        $newArr = $this->children[$i]->toArray();
                        if (!is_null($newArr)) {
                            foreach ($newArr as $key => $value) {
                                if (isset($array[$this->name][$key])) {
                                    if (!is_array($array[$this->name][$key])) {
                                        $array[$this->name][$key] = array($array[$this->name][$key], $value);
                                    } else {
                                        array_push($array[$this->name][$key], $value);
                                    }
                                } else {
                                    $array[$this->name][$key] = $value;
                                }
                            }
                        }
                    }
                }
                break;
            default:
                return null;
        }
        return $array;
    } // end func toArray
    
    /**
    * Writes the configuration to a file
    * Must be overriden in case you don't use files.
    * @param  string datasrc        path to the configuraton file
    * @param  string configType     type of configuration
    * @access public
    * @return PEAR_ERROR or true
    */
    function writeDatasrc($datasrc, $configType)
    {
        $fp = @fopen($datasrc, 'w');
        if ($fp) {
            $string = $this->toString();
            $len = strlen($string);
            @flock($fp, LOCK_EX);
            @fwrite($fp, $string, $len);
            @flock($fp, LOCK_UN);
            @fclose($fp);
            return true;
        } else {
            return PEAR::raiseError('Cannot open datasource for writing.', 1, PEAR_ERROR_RETURN);
        }
    } // end func writeDatasrc
} // end class Config_Container
?>