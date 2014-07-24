<?php namespace Tiipiik\Import\Models;

use App;
use Model;
use Flash;
use Tiipiik\Import\Classes\DataImport;
use October\Rain\Support\ValidationException;

/**
 * Imports Model
 *
 * @author Tiipiik
 */
class Import extends Model
{
    protected $table = 'tiipiik_import_imports';
    protected $guarded = ['*'];
    protected $fillable = [];
    
    public $rules = [
        'title' => 'required|min:3',
    ];
    
    public $attachOne = [
        'imported_file' => ['System\Models\File']
    ];
    
    public $headers = null;
    
    /*
    public function afterFetch()
    {
        $file = $this->imported_file ? $this->imported_file->file_name : null;
    }
    */
    
    public function getHeadersOptions()
    {
        $file = null;
        $aDatas = [];
        if ($this->imported_file)
        {
            $file = $this->imported_file->getPath();  
            $fileType = $this->imported_file->content_type; 
        }
        
        if ($file != null && is_file('../'.$file))
        {
            $headers = DataImport::getFileHeaders('../'.$file, $fileType);
        }
        
        return $aDatas;
    }
}