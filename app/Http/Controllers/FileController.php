<?php

namespace App\Http\Controllers;

use App\Http\Requests\File\StoreRequest;
use App\Models\File;
use App\Services\FileService;

class FileController extends Controller
{
    protected $fileService;

    public function __construct(FileService $fileService)
    {
        $this->fileService = $fileService;
    }

    public function store(StoreRequest $request)
    {
        $this->fileService->storeFileLesson($request->validated());
        return self::success(null, 'added successfully');
    }

    public function destroy(File $file)
    {
        $this->fileService->deleteFileLesson($file);
        return self::success(null, 'deleted successfully');
    }
}
