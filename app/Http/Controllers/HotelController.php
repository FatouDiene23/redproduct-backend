<?php

namespace App\Http\Controllers;

use App\Models\Hotel;
use Illuminate\Http\Request;

class HotelController extends Controller
{
    
    public function index()
    {
        return response()->json(Hotel::all());
    }

    /**
     * Créer un nouvel hôtel.
     */
   public function store(Request $request)
{
    \Log::info("Données reçues dans /api/hotels", $request->all());

    try {
        $validated = $request->validate([
            'nom' => 'required|string|max:255',
            'email' => 'nullable|email',
            'prix_par_nuit' => 'nullable|numeric',
            'adresse' => 'nullable|string',
            'telephone' => 'nullable|string',
            'device' => 'nullable|string',
            'photo' => 'nullable|image|max:5120',
        ]);

        // Upload photo
        if ($request->hasFile('photo')) {
            $path = $request->file('photo')->store('hotels', 'public');
            $validated['photo'] = $path;
        }

        $hotel = Hotel::create($validated);

        \Log::info("Hôtel créé avec succès", $hotel->toArray());

        return response()->json($hotel, 201);

    } catch (\Exception $e) {
        \Log::error("Erreur lors de la création de l’hôtel : " . $e->getMessage(), [
            'trace' => $e->getTraceAsString()
        ]);
        return response()->json([
            'error' => 'Erreur lors de l’envoi du formulaire',
            'message' => $e->getMessage()
        ], 500);
    }
}

    /**
     * Afficher un hôtel.
     */
    public function show(Hotel $hotel)
    {
        return response()->json($hotel);
    }

    /**
     * Mettre à jour un hôtel.
     */
    public function update(Request $request, Hotel $hotel)
    {
        $validated = $request->validate([
            'nom' => 'sometimes|required|string|max:255',
            'email' => 'nullable|email',
            'prix_par_nuit' => 'nullable|numeric',
            'adresse' => 'nullable|string',
            'telephone' => 'nullable|string',
            'device' => 'nullable|string',
            'photo' => 'nullable|image|max:2048',
        ]);

        if ($request->hasFile('photo')) {
            $path = $request->file('photo')->store('hotels', 'public');
            $validated['photo'] = $path;
        }

        $hotel->update($validated);

        return response()->json($hotel);
    }

    /**
     * Supprimer un hôtel.
     */
    public function destroy(Hotel $hotel)
    {
        $hotel->delete();
        return response()->json(null, 204);
    }
}
