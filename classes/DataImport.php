<?php namespace Tiipiik\Classes\DataImport

class DataImport
{
    /*
     * Get file headers to display in dropdown
     *
     */
    public static function getFileHeaders()
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
        
        return $fields;
    }
}