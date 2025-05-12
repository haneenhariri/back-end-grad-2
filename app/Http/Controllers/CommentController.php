<?php

namespace App\Http\Controllers;

use App\Http\Requests\Comment\storeRequest;
use App\Http\Requests\Comment\UpdateRequest;
use App\Http\Resources\CommentResource;
use App\Models\Comment;

class CommentController extends Controller
{
    public function store(storeRequest $request)
    {
        Comment::create($request->validationData());
        return self::success();
    }

    public function update(UpdateRequest $request, Comment $comment)
    {
        $comment->update(['content' => $request->get('content')]);
        return self::success(new CommentResource($comment));
    }

    public function destroy(Comment $comment)
    {
        $comment->deleteOrFail();
        return self::success(null, 'deleted successfully');
    }

}
