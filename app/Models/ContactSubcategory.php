<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContactSubcategory extends Model
{
    protected $fillable = [
        'title',
        'info',
        'category_id',
        'status',
    ];

    public function contacts()
    {
        return $this->hasMany(Contact::class, 'subcategory_id');
    }

    public function category()
    {
        return $this->belongsTo(ContactCategory::class, 'category_id');
    }

    public function getCreatedAtAttribute() {
        return date('d-m-Y H:i', strtotime($this->attributes['created_at']));
    }

    public function getUpdatedAtAttribute() {
        return date('d-m-Y H:i', strtotime($this->attributes['updated_at']));
    }
}
