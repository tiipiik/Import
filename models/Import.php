<?php namespace Tiipiik\Import\Models;

use App;
use Model;
use Flash;
use PHPExcel;
use DataImport;
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
    
    public function afterFetch()
    {
        $file = $this->imported_file ? $this->imported_file->file_name : null;
    }
    
    public function getHeadersOptions()
    {
        return [];
    }
}