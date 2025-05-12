<?php

namespace App\Http\Controllers;

use App\Http\Requests\Request\StatusRequest;
use App\Http\Requests\Request\StoreRequest;
use App\Http\Resources\RequestResource;
use App\Models\Instructor;
use App\Models\Request;
use App\Models\User;
use App\Services\RequestService;

class RequestController extends Controller
{
    protected $requestService;

    public function __construct(RequestService $requestService)
    {
        $this->requestService = $requestService;
    }

    public function store(StoreRequest $request)
    {
        $this->requestService->store($request->validationData());
        return self::success(null, 'added successfully');
    }

    public function index()
    {
        $data = Request::where('status', 'pending')->get();
        return self::success(RequestResource::collection($data));
    }

    public function changeStatus(StatusRequest $statusRequest, Request $request,)
    {
        $this->requestService->changeStatus($request, $statusRequest->status);
        return self::success();
    }


}
