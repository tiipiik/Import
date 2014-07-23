<?php namespace Tiipiik\Import\Models;

use App;
use Model;
use Flash;
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
        'file' => 'required|mimes:csv',
    ];
    
    public $attachOne = [
        'file' => ['System\Models\File']
    ];
    
}