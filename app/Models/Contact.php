<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Contact extends Model
{
    protected $fillable = [
        'prefix',
        'firstname',
        'lastname',
        'title',
        'company',
        'phone',
        'email',
        'cellphone',
        'address',
        'address2',
        'post_box',
        'zip_code',
        'country',
        'city',
        'language',
        'owner_id',
        'category_id',
        'subcategory_id',
        'status',
    ];

    public function category()
    {
        return $this->belongsTo(ContactCategory::class, 'category_id');
    }

    public function subcategory()
    {
        return $this->belongsTo(ContactSubcategory::class, 'subcategory_id');
    }

    public function getCreatedAtAttribute() {
        return date('d-m-Y H:i', strtotime($this->attributes['created_at']));
    }

    public function getUpdatedAtAttribute() {
        return date('d-m-Y H:i', strtotime($this->attributes['updated_at']));
    }
}
