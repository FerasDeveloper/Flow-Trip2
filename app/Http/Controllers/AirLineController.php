<?php

namespace App\Http\Controllers;

use App\Http\Requests\AirLineFormRequest;
use App\Services\AirLineService;


class AirLineController extends Controller
{
  protected AirLineService $service;

    public function __construct(AirLineService $service)
    {
        $this->service = $service;
    }

    // ------- [planes] ------- //

    public function add_plane(AirLineFormRequest $request)
    {
        return response()->json($this->service->add_plane($request));
    }

    public function edit_plane(AirLineFormRequest $request, $id)
    {
        return response()->json($this->service->edit_plane($request, $id));
    }

    public function get_all_planes()
    {
        return response()->json($this->service->get_all_planes());
    }

    public function get_single_plane($id)
    {
        return response()->json($this->service->get_single_plane($id));
    }

    public function delete_plane($id)
    {
        return response()->json($this->service->delete_plane($id));
    }

    // ------- [flights] ------- //

    public function add_flight(AirLineFormRequest $request)
    {
        return response()->json($this->service->add_flight($request));
    }

    public function edit_flight(AirLineFormRequest $request, $id)
    {
        return response()->json($this->service->edit_flight($request, $id));
    }

    public function edit_seats(AirLineFormRequest $request)
    {
        return response()->json($this->service->edit_seats($request));
    }

    public function get_all_flights()
    {
        return response()->json($this->service->get_all_flights());
    }

    public function get_flight_details($id)
    {
        return response()->json($this->service->get_flight_details($id));
    }

    public function delete_flight($id)
    {
        return response()->json($this->service->delete_flight($id));
    }

    // ------- [evaluations] ------- //

    public function get_evaluation()
    {
        return response()->json($this->service->get_evaluation());
    }

    // ------- [reservations] ------- //

    public function get_flight_reservations($id)
    {
        return response()->json($this->service->get_flight_reservations($id));
    }

    public function get_all_reservations()
    {
        return response()->json($this->service->get_all_reservations());
    }

    public function search_reservations_by_name(AirLineFormRequest $request)
    {
        return response()->json($this->service->search_reservations_by_name($request));
    }

}
