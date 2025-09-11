<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Page extends Model
{
    use HasFactory;

    protected $fillable = [
        'title','slug','content','excerpt','featured_image',
        'meta_title','meta_description','meta_keywords',
        'status','published_at','author_id','parent_id',
        'template','sort_order','is_homepage'
    ];

    protected $casts = [
        'published_at' => 'datetime',
        'is_homepage'  => 'boolean',
    ];

    public function author()  { return $this->belongsTo(User::class, 'author_id'); }
    public function parent()  { return $this->belongsTo(Page::class, 'parent_id'); }
    public function children(){ return $this->hasMany(Page::class, 'parent_id'); }

    // Yayında olanlar için scope
    public function scopePublished($q){
        return $q->where('status','published')->whereNotNull('published_at');
    }
}
