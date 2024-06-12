<?php

namespace App\Http\Controllers\Api;

use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\TemporaryMedia;
use App\Http\Controllers\Controller;

class TemporaryMediaController extends Controller
{
    public function uploadFile(Request $request)
    {
        $request->validate([
            'file' => 'required|file',
        ]);

        $temporaryMedia = new TemporaryMedia();
        $temporaryMedia->uuid = Str::uuid();
        $temporaryMedia->collection_name = $request->input('collection_name');
        $temporaryMedia->addMedia($request->file('file'))->toMediaCollection($request->input('collection_name'));
        $temporaryMedia->save();

        return response()->json(['uuid' => $temporaryMedia->uuid]);
    }

    public function createModel(Request $request)
{
    $request->validate([
        'name' => 'required|string',
        // Add other validation rules as needed
        'file_uuids' => 'nullable|array',
    ]);

    $model = TemporaryMedia::create([
        'name' => $request->input('name'),
        // Add other fields as needed
    ]);

    if ($request->has('file_uuids')) {
        foreach ($request->input('file_uuids') as $uuid) {
            $temporaryMedia = TemporaryMedia::where('uuid', $uuid)->first();
            if ($temporaryMedia) {
                $media = $temporaryMedia->getFirstMedia('temporary_files');
                $media->model_type = TemporaryMedia::class;
                $media->model_id = $model->id;
                $media->save();
            }
        }
    }

    return response()->json($model);
}
}
