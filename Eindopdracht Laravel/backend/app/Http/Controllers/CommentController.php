<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Models\Comment;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    // KLA-10656: Reacties ophalen bij een specifiek ticket
    public function index(Request $request, $ticketId)
    {
        $ticket = Ticket::findOrFail($ticketId);

        // Beveiliging: Klanten mogen alleen reacties op hun eigen ticket zien
        if (!$request->user()->is_admin && $ticket->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Geen toegang.'], 403);
        }

        return Comment::where('ticket_id', $ticket->id)->get();
    }

    // KLA-10657: Reactie plaatsen
    public function store(Request $request, $ticketId)
    {
        $ticket = Ticket::findOrFail($ticketId);

        if (!$request->user()->is_admin && $ticket->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Geen toegang.'], 403);
        }

        $request->validate(['content' => 'required|string']);

        $comment = Comment::create([
            'ticket_id' => $ticket->id,
            'user_id' => $request->user()->id,
            'content' => $request->content,
        ]);

        return response()->json($comment, 201);
    }
}