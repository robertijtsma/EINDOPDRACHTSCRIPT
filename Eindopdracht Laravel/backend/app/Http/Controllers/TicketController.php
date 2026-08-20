<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use Illuminate\Http\Request;

class TicketController extends Controller
{
    // KLA-10646: Haal de lijst met tickets op
    public function index(Request $request)
    {
        // Als de gebruiker een admin is, geef alle tickets
        if ($request->user()->is_admin) {
            return Ticket::all();
        }

        // Anders, geef alleen de tickets van deze specifieke klant
        return Ticket::where('user_id', $request->user()->id)->get();
    }

    // KLA-10647: Maak een nieuw ticket aan
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'category_id' => 'required|exists:categories,id',
        ]);

        $ticket = Ticket::create([
            'user_id' => $request->user()->id, // Koppel automatisch aan de ingelogde klant
            'category_id' => $request->category_id,
            'title' => $request->title,
            'description' => $request->description,
            'status' => 'open', // Een nieuw ticket is altijd 'open'
        ]);

        return response()->json($ticket, 201);
    }

    // KLA-10649: Laat alle gegevens van één ticket zien
    public function show(Request $request, Ticket $ticket)
    {
        // Beveiliging: Een normale klant mag niet in andermans tickets kijken
        if (!$request->user()->is_admin && $ticket->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Dit is niet jouw ticket!'], 403);
        }

        return $ticket;
    }

    // KLA-10648, KLA-10650, KLA-10651: Ticket aanpassen
    public function update(Request $request, Ticket $ticket)
    {
        // Beveiliging: Alleen de eigenaar of een admin mag het aanpassen
        if (!$request->user()->is_admin && $ticket->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Geen toegang.'], 403);
        }

        // De klant mag alleen titel, beschrijving en categorie aanpassen
        $updateData = $request->only(['title', 'description', 'category_id']);

        // Als de gebruiker een admin is, mag hij óók de status en toegewezen beheerder aanpassen
        if ($request->user()->is_admin) {
            if ($request->has('status')) {
                $updateData['status'] = $request->status;
            }
            if ($request->has('admin_id')) {
                $updateData['admin_id'] = $request->admin_id;
            }
        }

        $ticket->update($updateData);

        return $ticket;
    }

    // DELETE: Ticket verwijderen (Optioneel, maar goed om te hebben)
    public function destroy(Request $request, Ticket $ticket)
    {
        if (!$request->user()->is_admin && $ticket->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Geen toegang.'], 403);
        }

        $ticket->delete();
        return response()->json(['message' => 'Ticket verwijderd.']);
    }
}