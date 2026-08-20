<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    // READ: Haal alle categorieën op (KLA-10652)
    public function index()
    {
        return Category::all();
    }

    // READ: Haal één specifieke categorie op
    public function show(Category $category)
    {
        return $category;
    }

    // CREATE: Nieuwe categorie aanmaken (KLA-10653)
    public function store(Request $request)
    {
        // Check of de gebruiker admin is
        if (!$request->user()->is_admin) {
            return response()->json(['message' => 'Alleen admins mogen dit doen.'], 403);
        }

        $request->validate(['name' => 'required|string|max:255']);
        
        return Category::create(['name' => $request->name]);
    }

    // UPDATE: Bestaande categorie aanpassen (KLA-10654)
    public function update(Request $request, Category $category)
    {
        if (!$request->user()->is_admin) {
            return response()->json(['message' => 'Alleen admins mogen dit doen.'], 403);
        }

        $request->validate(['name' => 'required|string|max:255']);
        
        $category->update(['name' => $request->name]);
        return $category;
    }

    // DELETE: Categorie verwijderen (KLA-10655)
    public function destroy(Request $request, Category $category)
    {
        if (!$request->user()->is_admin) {
            return response()->json(['message' => 'Alleen admins mogen dit doen.'], 403);
        }

        $category->delete();
        return response()->json(['message' => 'Categorie succesvol verwijderd.']);
    }
}