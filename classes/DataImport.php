<?php namespace Tiipiik\Import\Classes;

use PHPExcel;
use Exception;
use SimpleXMLElement;
use PHPExcel_IOFactory;

/*
 * Based on http://codepad.org/J36upJup
 */

class DataImport
{

    /*
     * Based on :
     * http://php.net/manual/fr/simplexmlelement.children.php
     * coldshine1 at rambler dot ru
     */
    public static function ParseXML($node, &$nodes = [], $only_child = true)
    {
        //Current node name
        $node_name = $node->getName();
        $nodes[] = $node_name;
        
        //Let's count children
        $only_child = true;
        if($node->count() > 1 ) $only_child = false;

        //Get children
        $count = 0;
        foreach ($node->children() as $child_name=>$child_node) {
            if(!$only_child) //If there are siblings then we'll add node to the end of the array
                self::ParseXML($child_node, $nodes[$node_name], $only_child);
            else
                self::ParseXML($child_node, $nodes[$child_name], $only_child);
            $count++;
        }
        return $nodes;
    }
    
    /*
     * Get file headers to display in dropdown
     *
     */
    public static function getFileHeaders($file, $fileType)
    {
        $fields = null;
        
        if ($fileType == 'application/xml')
        {
            $xmlstr = file_get_contents($file);
            
            $xml = simplexml_load_string($xmlstr, 'SimpleXMLElement', LIBXML_NOCDATA);
            $namespaces = $xml->getNameSpaces(true);
            if(!$xml)
                throw new Exception('Failed To Parse XML');
            
            //$sxe = new SimpleXMLElement($xmlstr);
            
            /*
            $nodes = self::ParseXML($sxe);
            echo '<pre>';
                var_dump($nodes);
            echo 'Size : '.sizeof($nodes);
            foreach ($nodes as $key=>$node)
            {
                echo $key.'<br>';
            }
            */
            
            //  Where is this file from ?
            //$is_wp = $xml->xpath('//channel/wp:wxr_version');
            $generator = $xml->xpath('//channel/generator');
            if ($generator)
            {
                if (strpos($generator[0], 'wordpress') !== false)
                {
                    // this is a WP file, could check version to be more retro compat but...
                    
                    $list = get_filtered_wp_xml($file);
                    echo '<pre>';
                        var_dump($list);
                    
                    /*
                    * This is for parsing file, here we just want to pick nodes
                    */
                    /*
                    foreach($xml->channel->item as $item)
                    {
                        //$title = $xml->xpath('//title');
                        //$content = $item->children( $namespaces['content']);
                        $dc = $item->children( $namespaces['dc']);
                        $wp = $xml->channel->children( $namespaces['wp']);

                        // tags
                        
                        // categories
                        
                        // posts
                        if ( (string)$wp->post_type == 'post')
                        {
                            
                        }
                        // comments
                    }
                    */
                }
            }
            
            die;
        }
        else if ($fileType == 'text/csv')
        {
            $objReader = PHPExcel_IOFactory::createReaderForFile($file);
            $objReader->setReadDataOnly(true);
            $objReader->load($file);
            $objPHPExcel = $objReader->load($file);
            
            $rows = $objPHPExcel->getActiveSheet()->toArray(null, true, true, true);
            
            // get the column names
            $xls_fields = isset($rows[1]) ? $rows[1] : array();
            if (!empty($xls_fields))
                unset($rows[1]);
            
            // xls returns $value = array('A'=>'value'); so we have to remove keys
            $fields = array();
            foreach ($xls_fields as $field)
            {
                $fields[] = strtolower($field);
            }
                    
            // free up memory
            unset($objPHPExcel);
            //unset($fields);
        }
        
        return $fields;
    }
}