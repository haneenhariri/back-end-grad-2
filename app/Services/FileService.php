<?php

namespace App\Services;

use App\Models\File;
use Illuminate\Support\Facades\Storage;

class FileService
{

    public function updatePhoto($file, $path, $new_path)
    {
        if ($path && Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
        return Storage::disk('public')->put($new_path, $file);
    }

    public function storeFileLesson(array $data)
    {
        $data['origin_name'] = $data['path']->getClientOriginalName();
        $data['extension'] = $data['path']->getClientOriginalExtension();
        $data['path'] = Storage::disk('public')->put('/lesson', $data['path']);
        File::create($data);

    }

    public function deleteFileLesson(File $file)
    {
        if (Storage::disk('public')->exists($file->path)) {
            Storage::disk('public')->delete($file->path);
        }
        $file->delete();
    }
}
