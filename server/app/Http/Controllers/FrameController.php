<?php

namespace App\Http\Controllers;

use App\Frame;
use App\VideoRecipe;
use Illuminate\Http\Request;

class FrameController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return Frame::orderBy('id', 'asc')->paginate(10);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $userDataPath = 'data/user_data';
        $numRepetition = $request->numRepetition;
        $videoRecipeId = $request->videoRecipeId;
        $userId = 222;
        $sourceImageId = VideoRecipe::with('frames')->find($videoRecipeId)->frames->count();
        $order = $request->order;
        error_log('$sourceImageId ' . $sourceImageId);
        $arrFrames = [];
        error_log('$numRepetition =' . $numRepetition);
        error_log('$videoRecipeId ='. $videoRecipeId);

        error_log('$_FILES =' . json_encode($_FILES));
        $successCount = 0;

        foreach ($_FILES['filesToUpload']['tmp_name'] as $key => $tmpName) {
            error_log('hahaha' . $tmpName);
        
            $tempName = $_FILES["filesToUpload"]["tmp_name"][$key];
            error_log('tempName' . $tempName);

            $name = $_FILES["filesToUpload"]["name"][$key];
            error_log('name' . $name);

            $ext = pathinfo($name, PATHINFO_EXTENSION);
            error_log('ext' . $ext);

            $srcFilename = "$userDataPath/" . $userId . '/source_images/source_image_' . (string)$sourceImageId . '.' . $ext;
            
            error_log('$srcFilename=' . $srcFilename);
            if (!file_exists("$userDataPath/" . $userId . '/source_images/')) {
                mkdir("$userDataPath/" . $userId . '/source_images/', 0777, true);
            }
            if (move_uploaded_file($tempName, $srcFilename) !== true) {
                $upload_sucess = false;
                error_log("Failed to upload the file.");
            } else {
                error_log("Successfully uploaded the file.");
                $successCount++;
                $sourceImageId++;
                $frame = new Frame();
                $frame->user_id = $userId;
                $frame->order = $order;
                $frame->src_filename = $srcFilename;
                $frame->num_repetition = $numRepetition;
                $frame->video_recipe_id = $videoRecipeId;
                $order++;
                error_log('$frame=' . $frame);
                array_push($arrFrames, $frame);
            }
        }
        $framesAfter = Frame::where([['video_recipe_id', $videoRecipeId], ['order', '>=', $request->order]])->get();
        foreach($framesAfter as $frameAfter)
        {
            error_log('$frameAfter' . $frameAfter);
            $frameAfter->order += $successCount;
            $frameAfter->save();
        }
        foreach($arrFrames as $frame)
        {
            $frame->save();
        }
        return $arrFrames;
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Frame  $frame
     * @return \Illuminate\Http\Response
     */
    public function show(Frame $frame)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Frame  $frame
     * @return \Illuminate\Http\Response
     */
    public function edit(Frame $frame)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Frame  $frame
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Frame $frame)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Frame  $frame
     * @return \Illuminate\Http\Response
     */
    public function destroy(Frame $frame)
    {
        //
    }
}
