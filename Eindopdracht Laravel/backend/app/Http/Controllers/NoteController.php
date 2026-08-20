<?php

namespace App\Http\Controllers;

use App\Models\Note;
use Illuminate\Http\Request;

class NoteController extends Controller
{
    // KLA-10658: Interne notities ophalen (ALLEEN ADMIN)
    public function index(Request $request, $ticketId)
    {
        if (!$request->user()->is_admin) {
            return response()->json(['message' => 'Alleen admins.'], 403);
        }

        return Note::where('ticket_id', $ticketId)->get();
    }

    // KLA-10659: Interne notitie toevoegen (ALLEEN ADMIN)
    public function store(Request $request, $ticketId)
    {
        if (!$request->user()->is_admin) {
            return response()->json(['message' => 'Alleen admins.'], 403);
        }

        $request->validate(['content' => 'required|string']);

        $note = Note::create([
            'ticket_id' => $ticketId,
            'user_id' => $request->user()->id,
            'content' => $request->content,
        ]);

        return response()->json($note, 201);
    }
}