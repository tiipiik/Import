<?php namespace Tiipiik\Import\Models;

use App;
use Model;
use Flash;
use Exception;
use Tiipiik\Import\Classes\DataImport;
use Tiipiik\Import\Classes\WpImport;
use October\Rain\Support\ValidationException;


/**
 * Imports Model
 *
 * @author Tiipiik
 */
class Import extends Model
{
    use \October\Rain\Database\Traits\Validation;
    
    protected $table = 'tiipiik_import_imports';
    protected $guarded = ['*'];
    protected $fillable = [];
    
    public $rules = [
        'title' => 'required',
    ];
    
    public $attachOne = [
        'imported_file' => ['System\Models\File']
    ];
    
    public $headers = null;
    public $import_posts = null;
    public $import_authors = null;
    public $import_comments = null;
    public $import_tags = null;
    public $import_categories = null;
    public $xml_file = null;
    public $csv_file = null;
    public $droplistin = null;
    public $droplistout = null;
    
    /*
    public function afterFetch()
    {
        $file = $this->imported_file ? $this->imported_file->file_name : null;
    }
    */
    
    public function beforeSave()
    {
        $file = $this->imported_file ? $this->imported_file->file_name : null;
        if ($file)
        {
            $list = WpImport::get_filtered_wp_xml($file);
            //echo '<pre>';
              //  var_dump($list);
            
            $list = WpImport::pages($list);
            //throw new Exception('Working...'.$list);
        }
        
    }
    
    /*
     * Get headers and/or datas from uploaded file
     * Needs to ba able to deal with WP XML, XML and CSV files.
     *
     */
    public function getHeadersOptions()
    {
        $file = null;
        $aOptions = [];
        
        
        $file = $this->imported_file ? $this->imported_file->file_name : null;
        
        
        // If file is WP XML or XML
            // If file is WP, pre-proccess fields to make them XML compatible 
        $list = WpImport::get_filtered_wp_xml($file);
        $pages = WpImport::posts($list);    
        //echo '<pre>';
        //    var_dump($pages);
        
        // else if file is CSV
        
            
        if ($this->imported_file)
        {
            $file = $this->imported_file->getPath();  
            $fileType = $this->imported_file->content_type;
            
        }
        /*
        if ($file != null && is_file('../'.$file))
        {
            $headers = DataImport::getFileHeaders('../'.$file, $fileType);
        }
        */
        
        return $aOptions;
    }
    
    /*
     * Get tables and fields to select where to send datas
     * Needs to make it seachable cause of the long list of table / fields
     *
     */
    public function getDBOptions()
    {
        $aOptions = [];
        
        return $aOptions;
    }
}