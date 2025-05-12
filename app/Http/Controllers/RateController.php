<?php

namespace App\Http\Controllers;

use App\Http\Requests\Rate\StoreRequest;
use App\Http\Requests\Rate\UpdateRequest;
use App\Models\Rate;

class RateController extends Controller
{


    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreRequest $request)
    {
        $data = $request->validated();
        $data['user_id'] = auth()->user()->id;
        Rate::create($data);
        return self::success(null, 'added successfully');
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateRequest $request, Rate $rate)
    {
        $data = array_filter($request->validated());
        $rate->update($data);
        return self::success($rate, 'updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Rate $rate)
    {
        $rate->delete();
        return self::success(null, 'deleted successfully');
    }
}
