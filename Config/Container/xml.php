<?php
//+----------------------------------------------------------------------+
// | PHP Version 4                                                       |
//+----------------------------------------------------------------------+
// | Copyright (c) 1997, 1998, 1999, 2000, 2001 The PHP Group            |
//+----------------------------------------------------------------------+
// | This source file is subject to version 2.0 of the PHP license,      |
// | that is bundled with this package in the file LICENSE, and is       |
// | available at through the world-wide-web at                          |
// | http://www.php.net/license/2_02.txt.                                |
// | If you did not receive a copy of the PHP license and areunable to   |
// | obtain it through the world-wide-web, please send a note to         |
// | license@php.net so we can mail you a copy immediately.              |
//+----------------------------------------------------------------------+
// | Authors: Christian Stocker <chregu@phant.ch>                        |
// |          Alexander Merz <alexander.merz@t-online.de>                |
//+----------------------------------------------------------------------+
//

require_once( "Config/Container.php" ) ;

/**
* Config-API-Implemtentation for XML-Files
*
* This class implements the Config-API based on ConfigDefault
*
* @author      Christian Stocker <chregu@nomad.ch>
* @access      public
* @version     Config_xml.php, 2000/04/16
* @package     Config
*/


class Config_Container_xml extends Config_Container {

    /**
    * contains the features given by parseInput
    * @var array
    * @see parseInput()
    */
    var $feature = array ("IncludeContent" => True,
        "MasterAttribute" => False,
        "IncludeMasterAttribute" => True,
        "IncludeChildren" => True,
        "KeyAttribute" => False      
    );

    var $tagname = "tagname";
    
    var $allowed_options = array();
    /**
    * parses the input of the given data source
    *
    * The Data Source is a file, so datasrc requires a existing file.
    * The feature-array have to contain the comment char array("cc" => Comment char)
    *
    * @access public
    * @param string $datasrc  Name of the datasource to parse
    * @param array $feature   Contains a hash of features
    * @return mixed             returns a PEAR_ERROR, if error occurs
    */

    function parseInput( $datasrc = "",$feature = array() )
    {

        $this -> datasrc = $datasrc ;
        $this->setFeatures($feature,  array_merge($this->allowed_options, array('IncludeContent', 'MasterAttribute','IncludeMasterAttribute','IncludeChildren','KeyAttribute')));
        if( file_exists( $datasrc ) )
        {
            //xmldocfile is broken in 4.0.7RC1
            $fd = fopen( $datasrc, "r" );
            $xmlstring = fread( $fd, filesize( $datasrc ) );
            fclose( $fd );
            $xml = xmldoc($xmlstring);

            $root = $xml->root();
            //PHP 4.0.6 had $root->name as tagname, check for that here...            
            if (!isset($root->{$this->tagname}))
            {
                $this->tagname = "name";
            }
            $this->addAttributes($root);
            $this->parseElement($root,"/".$root->{$this->tagname});
        }
        else
        {
            return new PEAR_Error( "File '".$datasrc."' doesn't
                                   exists!", 31, PEAR_ERROR_RETURN, null, null );
        }
    } // end func parseInput

    /**
    * parses the input of the XML_ELEMENT_NODE into $this->data
    *
    * @access private
    * @param object XML_ELEMENT_NODE $element
    * @param string $parent xpath of parent ELEMENT_NODE
    */

    function parseElement ($element,$parent = "/") {

        foreach($element->children() as $tag => $value)
        {
            if (isset($value->type) && XML_ELEMENT_NODE == $value->type)
            {
                //if feature KeyAttribute is set and the name is an attribute in the xml, take this as key for the array
                if ($this->feature["KeyAttribute"] && $value->get_attribute($this->feature["KeyAttribute"]))
                {
                    $value->{$this->tagname} = $value->get_attribute($this->feature["KeyAttribute"]);
                }
                
                $this->addAttributes($value,$parent);
                
                if ($value->children())
                {
                    $this->parseElement($value,$parent."/".$value->{$this->tagname});
                }
            }
        }
    }
    //end func parseElement

    /**
    * ?? ask Christian
    *
    * @access private
    * @param string             $element    the element to add perhaps?
    * @param object I_dont_know $parent     the parent element?
    */


    function addAttributes($element,$parent="")
    {
        $parentslash =""; //E_ALL fix
        if ($parent=="") {
                       //this is only for the root element
                        $parentslash ="/";
        }


        if ($this->feature["IncludeChildren"] ) {
            $this->data["$parent"."$parentslash"]["children"][] = $element->{$this->tagname};

        }
        //php-4.0.7 gets the content differently than 4.0.6 (the second one in the elseif)
        //php 4.2 does it another way again, it has finally the method get_content
        if (method_exists($element,"get_content")) {
            $element->content = $element->get_content();
        } elseif (!isset($element->content)) {
            $element->content = ""; //E_ALL fix
            if (is_array($element->children()))
            {
                $element->content ="";
                foreach ($element->children() as $children)
                {
                   if (isset($children->content))
                   {
                        $element->content .= $children->content;
                   }
                }
            }
         }
            
        if (($this->feature["IncludeContent"]|| $this->feature["MasterAttribute"] == "content") && $element->content)
        {
                   
            if ($this->feature["MasterAttribute"] == "content")
            {
                   $this->data["$parent"."$parentslash"][$element->{$this->tagname}] =$element->content;
            }
            if ($this->feature["IncludeMasterAttribute"] || $this->feature["MasterAttribute"] != "content")
            {
                $this->data["$parent/".$element->{$this->tagname}]["content"] =$element->content;
            }
        }
        if ($element->attributes() )
        {
         
            foreach ($element->attributes() as $attribute => $attributeObject)
            {
                
                if ($this->feature["MasterAttribute"] && $attributeObject->name == $this->feature["MasterAttribute"])
                {
                    $this->data[$parent."$parentslash"][$element->{$this->tagname}] = $element->get_attribute($attributeObject->name);
                }
                if ($this->feature["IncludeMasterAttribute"] || $attributeObject->name != $this->feature["MasterAttribute"])
                {
                    $this->data["$parent/".$element->{$this->tagname}][$attributeObject->name] = $element->get_attribute($attributeObject->name);
                }
            }
        }
    }
    //endfunc addAttributes
};


?>
