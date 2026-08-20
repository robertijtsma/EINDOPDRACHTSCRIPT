<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'category_id',
        'admin_id',
        'title',
        'description',
        'status',
    ];

    // Relatie naar de gebruiker (klant) die de ticket heeft ingediend
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Relatie naar de categorie
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    // Relatie naar de toegewezen beheerder (admin)
    public function admin()
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

    // Relatie naar de openbare reacties
    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    // Relatie naar de interne beheerdersnotities
    public function notes()
    {
        return $this->hasMany(Note::class);
    }
}