<?php

namespace App\Http\Controllers;

use App\Models\Reserva;
use Illuminate\Http\Request;
use App\Models\users;
use Illuminate\Support\Facades\Auth;
use Carbon\CarbonPeriod;
use Carbon\Carbon;

class ReservaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return response()->json([Reserva::all(), 200]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function storeReservas(Request $request)
    {
        $request->validate([
            'data_inicial' => 'required|date_format:Y-m-d', 
            'data_final' => 'required|date_format:Y-m-d',
            'gmail' => 'required', 
        ]);

        $data_inicial = $request->data_inicial;
        $data_final = $request->data_final;

        $existingReservations = Reserva::where(function ($query) use ($data_inicial, $data_final) {
            $query->whereBetween('data_inicial', [$data_inicial, $data_final])
                ->orWhereBetween('data_final', [$data_inicial, $data_final])
                ->orWhere(function ($query) use ($data_inicial, $data_final) {
                    $query->where('data_inicial', '<=', $data_inicial)
                            ->where('data_final', '>=', $data_final);
                });
        })->exists();

        if ($existingReservations) {
            return response()->json([
                'status' => 'error',
                'message' => 'Las fechas que quieres reservar están ocupadas.',
            ], 400);
        }

        $Reserva = new Reserva;
        $Reserva->data_inicial = $data_inicial;
        $Reserva->data_final = $data_final;
        $Reserva->gmail = $request->gmail;
        $Reserva->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Reserva creada exitosamente',
            'meta' => $Reserva,
        ], 201);
    }


    public function EditReservas(Request $request, $id)
    {
        $request->validate([
            'data_inicial' => 'required|date_format:Y-m-d', 
            'data_final' => 'required|date_format:Y-m-d',
        ]);

        $data_inicial = $request->data_inicial;
        $data_final = $request->data_final;

        $existingReservations = Reserva::where('id', '!=', $id)
            ->where(function ($query) use ($data_inicial, $data_final) {
                $query->whereBetween('data_inicial', [$data_inicial, $data_final])
                    ->orWhereBetween('data_final', [$data_inicial, $data_final])
                    ->orWhere(function ($query) use ($data_inicial, $data_final) {
                        $query->where('data_inicial', '<=', $data_inicial)
                                ->where('data_final', '>=', $data_final);
                    });
            })->exists();

        if ($existingReservations) {
            return response()->json([
                'status' => 'error',
                'message' => 'No se puede editar la reserva porque las nuevas fechas se solapan con otras reservas existentes.',
            ], 400);
        }

        $reserva = Reserva::findOrFail($id);
        $reserva->update([
            'data_inicial' => $data_inicial,
            'data_final' => $data_final,
        ]);
        
        return response()->json([
            'status' => 'success',
            'message' => 'Reserva editada exitosamente',
            'meta' => $reserva,
        ], 200); 
    }


    public function getDaysWithoutReservations($data_inicial, $data_final)
    {
    
        try {
            $startDate = Carbon::createFromFormat('Y-m-d', $data_inicial);
            $endDate = Carbon::createFromFormat('Y-m-d', $data_final);
    
            if ($startDate->gt($endDate)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'La fecha inicial no puede ser posterior a la fecha final',
                ], 400);
            }
    
            $period = new CarbonPeriod($startDate, '1 day', $endDate);
    
            $allDates = [];
            foreach ($period as $date) {
                $allDates[] = $date->format('Y-m-d');
            }
    
            $reservas = Reserva::whereDate('data_inicial', '>=', $data_inicial)
                                ->whereDate('data_final', '<=', $data_final)
                                ->get();
    
            $reservedDates = [];
            foreach ($reservas as $reserva) {
                $resPeriod = new CarbonPeriod($reserva->data_inicial, '1 day', $reserva->data_final);
    
                foreach ($resPeriod as $resDate) {
                    $reservedDates[] = $resDate->format('Y-m-d');
                }
            }
    
            $reservedDates = array_unique($reservedDates);
            $availableDates = array_diff($allDates, $reservedDates);
    
            return response()->json([
                'status' => 'success',
                'available_dates' => array_values($availableDates),
            ]);
    
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error en las fechas proporcionadas: ' . $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Reserva $reserva)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Reserva $reserva)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Reserva $reserva)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Reserva $id)
    {
    $id->delete();
    return response()->json([
        'status' => 'success',
        'message' => 'Reserva cancelada exitosamente',
    ], 200); 
    }   

}
