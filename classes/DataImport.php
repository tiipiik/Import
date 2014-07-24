<?php namespace Tiipiik\Import\Classes;

use PHPExcel;
use Exception;
use PHPExcel_IOFactory;

/*
 * Based on http://codepad.org/J36upJup
 */

class DataImport
{
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
            
            $xml = simplexml_load_file($file, 'SimpleXMLElement', LIBXML_NOCDATA);
            if(!$xml)
                throw new Exception('Failed To Parse XML');
            $namespaces = $xml->getNameSpaces(true);
            
            //  Where is this file from ?
            //$is_wp = $xml->xpath('//channel/wp:wxr_version');
            $generator = $xml->xpath('//channel/generator');
            if ($generator)
            {
                if (strpos($generator[0], 'wordpress') !== false)
                {
                    // this is a WP file, could check version to be more retro compat but...
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