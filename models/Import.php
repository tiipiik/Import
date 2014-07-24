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
        'file' => ['System\Models\File']
    ];
    
    public $headers = null;
    
    public function afterFetch()
    {
        $file = $this->file->file_name;
    }
    
    public function getHeadersOptions()
    {
        return [];
    }
    
}