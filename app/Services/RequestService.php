<?php

namespace App\Services;

use App\Models\Instructor;
use App\Models\Request;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class RequestService
{
    public function store(array $data)
    {
        $data['cv'] = Storage::disk('public')->put('/cv', $data['cv']);
        $data['password']=bcrypt($data['password']);
        Request::create($data);
    }

    public function changeStatus(Request $request,$status){
        if ($request->status != "accepted" && $status == "accepted") {
            DB::transaction(function () use ($request){
                $user = User::create([
                    'name'=>$request->name,
                    'email'=>$request->email,
                    'password'=>$request->password
                ]);
                $user->assignRole('instructor');
                Instructor::create([
                    'user_id' => $user->id,
                    'education' => $request->education,
                    'specialization' => $request->specialization,
                    'summery' => $request->summery,
                    'cv' => $request->cv
                ]);
            });
        }
        $request->update(['status' => $status]);
    }
}
