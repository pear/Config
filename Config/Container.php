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

/**
* Interface for Config containers
*
* @author   Bertrand Mansion <bmansion@mamasam.com>
* @package  Config
*/
class Config_Container {

    /**
    * Container object type
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
    * Contains the options used by the parser
    * @var array
    */
    var $options = array();

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
    * Adds an item to this item.
    * @param  string  type      type of item: directive, section, comment, blank...
    * @param  mixed   item      item name or an item container object
    * @param  string  content   object content
    * @return object  reference to new item
    */
    function &addItem($type = null, $item = null, $content = '')
    {
        $index = sizeof($this->children);
        if (is_null($item))
            $item = '';
        if (is_object($item) && is_a($item, 'config_container')) {
            $this->children[$index] = $item;
            $this->children[$index]->parent =& $this;
        } elseif (is_string($item)) {
            $currentContainer = get_class($this);
            $this->children[$index] = new $currentContainer($type, $item, $content);
            $this->children[$index]->parent =& $this;
        } else {
            return PEAR::raiseError('Cannot add child in Config_Container::addItem.', null, PEAR_ERROR_RETURN);
        }
        return $this->children[$index];
    } // end func &addItem

    /**
    * Adds a comment to this item.
    * This is a helper method that calls addItem
    *
    * @param  string  content   object content
    * @return object  reference to new item
    */
    function &addComment($content = '')
    {
        $item =& $this->addItem('comment', '', $content);
        return $item;
    } // end func &addComment

    /**
    * Adds a blank line to this item.
    * This is a helper method that calls addItem
    *
    * @return object  reference to new item
    */
    function &addBlank()
    {
        $item =& $this->addItem('blank');
        return $item;
    } // end func &addBlank

    /**
    * Adds a directive to this item.
    * This is a helper method that calls addItem
    *
    * @param  string  name      Name of new directive
    * @param  string  content   Content of new directive
    * @return object  reference to new item
    */
    function &addDirective($name, $content)
    {
        $item =& $this->addItem('directive', $name, $content);
        return $item;
    } // end func &addDirective

    /**
    * Adds a section to this item.
    * This is a helper method that calls addItem
    *
    * @param  mixed   name      Name of new section or container object
    * @return object  reference to new item
    */
    function &addSection($section)
    {
        $item =& $this->addItem('section', $section);
        return $item;
    } // end func &addSection

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
        $testFields = array();
        if ($type == '') {
            return PEAR::raiseError('You must specify an existing type in Config_Container::getItem.', null, PEAR_ERROR_RETURN);
        } else {
            $testFields[] = 'type';
        }
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
                $itemObj = new $currentContainer($type, $item, $content);
                $this->children[$index] =& $itemObj;
                $this->children[$index]->parent =& $this; 
            }
            return $this->children[$index];
        } else {
            return $this->addItem($type, $item, $content);
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
    * Interface method
    * @access   public
    * @return null
    */
    function toString()
    {
        return;
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
    * Imports the requested options if allowed
    *
    * @param    array   List of options to set
    * @access   public
    */
    function setOptions($options)
    {
        foreach ($this->options as $key => $value) {
            if (isset($options[$key]))
                $this->options[$key] = $options[$key];
        }
    } // end func setOptions
    
    /**
    * Writes the configuration to a file
    * Must be overriden in case you don't use files.
    * @param  string datasrc    path to the configuraton file
    * @access public
    * @return PEAR_ERROR or true
    */
    function writeDatasrc($datasrc)
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