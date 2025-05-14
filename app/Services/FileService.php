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

    /**
     * تخزين ملف جديد
     *
     * @param \Illuminate\Http\UploadedFile $file
     * @param string $directory
     * @return string
     */
    public function storeFile($file, $directory)
    {
        return Storage::disk('public')->put($directory, $file);
    }

    /**
     * حذف ملف
     *
     * @param string $path
     * @return bool
     */
    public function deleteFile($path)
    {
        if ($path && Storage::disk('public')->exists($path)) {
            return Storage::disk('public')->delete($path);
        }

        return false;
    }
}

