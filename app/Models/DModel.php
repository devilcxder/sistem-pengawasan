<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DModel extends Model
{
    use \Backpack\CRUD\app\Models\Traits\CrudTrait;
    use HasFactory;
    protected $fillable = ['category_id','model_name','model_desc','data_split','accuracy'];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }
}
