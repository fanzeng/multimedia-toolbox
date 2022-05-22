<?php

namespace App\Http\Controllers;

use App\VideoRecipe;
use App\Frame;
use Session;

use Illuminate\Http\Request;

class VideoRecipeController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
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
        $videoRecipe = new VideoRecipe();
        $userId = 222;
        $videoRecipe->user_id = $userId;
        $videoRecipe->save();
        return response($videoRecipe)->header('sameSite', 'None')->header('withCredentials', 'true');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\VideoRecipe  $videoRecipe
     * @return \Illuminate\Http\Response
     */
    public function show(VideoRecipe $videoRecipe)
    {
        $videoRecipe = VideoRecipe::with('frames')->find($videoRecipe);
        error_log('$videoRecipe' . $videoRecipe);
        return $videoRecipe;
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\VideoRecipe  $videoRecipe
     * @return \Illuminate\Http\Response
     */
    public function edit(VideoRecipe $videoRecipe)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\VideoRecipe  $videoRecipe
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, VideoRecipe $videoRecipe)
    {
        //
        $videoRecipeUpdated = VideoRecipe::with('frames')->find($request->id);
        error_log('$videoRecipe->frames() =' . $videoRecipe->frames);
        // dd($request->get('frames'));
        foreach($videoRecipe->frames as $frame)
        {
            // $frame = Frame::find($frameUpdated['id']);
            $frameUpdated_ = array_filter($request->get('frames'), function($item) use($frame) {
                return $item['id'] == $frame->id;
            });
            if (count($frameUpdated_) > 0)
            {
                // dd($frameUpdated_[0]);
                $frameUpdated = array_pop($frameUpdated_);
                $frame->order = $frameUpdated['order'];
                $frame->num_repetition = $frameUpdated['numRepetition'];
                $frame->save();
            }
            else {
                $frame->delete();
            }
        }
        // foreach($request->get('frames') as $frameUpdated)
        // {
        //     $frame = Frame::find($frameUpdated['id']);
        //     $frame->order = $frameUpdated['order'];
        //     $frame->num_repetition = $frameUpdated['numRepetition'];
        //     $frame->save();
        // }
        $videoRecipeUpdated->save();
        return $videoRecipeUpdated;
    }

    function getTargetFileName($target_dir, $ext)
    {
        $frameId_str = sprintf('%05d', $this->frameId);
        error_log('$this->frameId =' . $this->frameId);
        $target_file_name = $target_dir . $frameId_str . '.morph.' . $ext;
        error_log('$target_file_name =' . $target_file_name);

        $this->frameId += 1;
        return $target_file_name;
    }

    function expandFrames(Request $request)
    {
        $userId = 222;
        error_log('$this->frameId' . $this->frameId);

        $userDataPath = 'data/user_data';
        $target_dir = "$userDataPath/" . $userId . "/expanded_frames/";
        if (ctype_alnum(substr($userDataPath, -1))) {
            exec("rm $userDataPath/" . $userId . "/expanded_frames/*.morph.jpg", $std_out);
        } else {
            die('$userDataPath is invalid!');
        }
        error_log('$request->videoRecipeId =' . $request->videoRecipeId);
        $videoRecipe = VideoRecipe::with('frames')->findOrFail($request->videoRecipeId);
        $frames = $videoRecipe->frames;
        $this->frameId = 0;
        for ($sourceImageNum = 0; $sourceImageNum < count($frames); $sourceImageNum++) {
            $frame = $frames[$sourceImageNum];
            error_log('$frame =' . $frame);
            $srcFilename = $frame->src_filename;
            $ext = pathinfo($srcFilename, PATHINFO_EXTENSION);
            if ($ext !== "jpg") {
                exec("convert " . $srcFilename . " " . pathinfo($srcFilename, PATHINFO_DIRNAME) . "/" . pathinfo($srcFilename, PATHINFO_FILENAME) . ".jpg");
            }
            for ($repeat_num = 0; $repeat_num < $frame->num_repetition; $repeat_num++) {
                $target_file_name = $this->getTargetFileName($target_dir, "jpg");
                exec("cp " . $srcFilename . " " . $target_file_name, $std_out);
            }
        }
    }
    private $frameId = 0;

    function makeVideo(Request $request)
    {
        $userDataPath = 'data/user_data';
        $userId = 222;
        exec('mkdir -p data/user_data/' . $userId . '/expanded_frames 2>&1', $std_out);

        $this->expandFrames($request);
        $sh_string = 'video_width=' . $request->videoWidth . ' && ';
        $sh_string = $sh_string . 'video_height=' . $request->videoHeight . ' && ';
        $sh_string = $sh_string . 'frames_per_second=' . $request->framesPerSecond . ' && ';
        $sh_string = $sh_string . 'quality_ratio=' . $request->qualityRatio . ' && ';
        $sh_string = $sh_string . 'dirname=' . $userId . ' && ';
        $sh_string = $sh_string . 'echo dirname=\$dirname && ';
        $sh_string = $sh_string . '/usr/bin/ffmpeg -y -i ' . $userDataPath . '/\$dirname/expanded_frames/%05d.morph.jpg' . ' -r "\$frames_per_second" -crf \"\$quality_ratio\" -s \"\$video_width\"x\"\$video_height\" ' . $userDataPath . '/"\$dirname"/output.mp4';
        error_log('echo "' . $sh_string  . ' "> ' . "$userDataPath/" . $userId . '/run.sh');
        exec('echo "' . $sh_string  . ' "> ' . "$userDataPath/" . $userId . '/run.sh');
        exec("chmod +x $userDataPath/" . $userId . '/run.sh', $std_out);
        exec('data/user_data/' . $userId . '/run.sh 2>&1', $std_out);
        foreach ($std_out as $out) {
            error_log($out);
        }
        exec('rm -r data/user_data/' . $userId . '/expanded_frames 2>&1', $std_out);

        return 'data/user_data/' . $userId . '/output.mp4' ;
    }

    public function run(Request $request) {
        error_log('$request=' . $request);
        error_log('$request->videoWidth=' . $request->videoWidth);
        error_log('$request->videoRecipeId =' . $request->videoRecipeId);

        return $this->makeVideo($request);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\VideoRecipe  $videoRecipe
     * @return \Illuminate\Http\Response
     */
    public function destroy(VideoRecipe $videoRecipe)
    {
        //
    }
}
