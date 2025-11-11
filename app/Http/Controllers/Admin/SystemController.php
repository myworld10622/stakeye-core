<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Artisan;
use App\Lib\CurlRequest;
use App\Lib\FileManager;
use App\Models\UpdateLog;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Laramin\Utility\VugiChugi;

class SystemController extends Controller
{
    public function systemInfo(){
        $laravelVersion = app()->version();
        $timeZone = config('app.timezone');
        $pageTitle = 'Application Information';
        return view('admin.system.info',compact('pageTitle', 'laravelVersion','timeZone'));
    }

    public function optimize(){
        $pageTitle = 'Clear System Cache';
        return view('admin.system.optimize',compact('pageTitle'));
    }

    public function optimizeClear(){
        Artisan::call('optimize:clear');
        $notify[] = ['success','Cache cleared successfully'];
        return back()->withNotify($notify);
    }

    public function systemServerInfo(){
        $currentPHP = phpversion();
        $pageTitle = 'Server Information';
        $serverDetails = $_SERVER;
        return view('admin.system.server',compact('pageTitle', 'currentPHP', 'serverDetails'));
    }

    public function systemUpdate() {
        $pageTitle = 'System Updates';
        return view('admin.system.update',compact('pageTitle'));
    }


    public function systemUpdateProcess(){
        if (gs('system_customized')) {
            return response()->json([
                'status'=>'error',
                'message'=>[
                    'The system already customized. You can\'t update the project'
                ]
            ]);
        }


        if (version_compare(systemDetails()['version'],gs('available_version'),'==')) {
            return response()->json([
                'status'=>'info',
                'message'=>[
                    'The system is currently up to date'
                ]
            ]);
        }


        if(!extension_loaded('zip')){
            return response()->json([
                'status'=>'error',
                'message'=>[
                    'Zip Extension is required to update the system'
                ]
            ]);
        }

        // $purchasecode = env('PURCHASECODE');
        // if (!$purchasecode) {
        //     return response()->json([
        //         'status'=>'error',
        //         'message'=>[
        //             'Invalid request. Please contact with support'
        //         ]
        //     ]);
        // }

        $purchasecode = config('system.purchase_code'); // ensure config/system.php exists
if (!$purchasecode) {
    return response()->json([
        'status' => 'error',
        'message' => ['Invalid request. Please contact support']
    ]);
}


        $website = @$_SERVER['HTTP_HOST'] . @$_SERVER['REQUEST_URI'] . ' - ' . env("APP_URL");

        $response = CurlRequest::curlPostContent(VugiChugi::upman(),[
            'purchasecode'=>$purchasecode,
            'product'=>systemDetails()['name'],
            'version'=>systemDetails()['version'],
            'website'=>$website,
        ]);

        $response = json_decode($response);
        if($response->status == 'error'){
            return response()->json([
                'status'=>'error',
                'message'=>$response->message->error
            ]);
        }

        if($response->remark == 'already_updated'){
            return response()->json([
                'status'=>'info',
                'message'=>$response->message->success
            ]);
        }

        // $directory = 'core/temp/';
        // $files = [];
        // foreach($response->data->files as $key => $fileUrl){

        //     $opts = [
        //         "http" => [
        //             "method" => "GET",
        //             "header" => "Purchase-Code: $purchasecode"
        //         ]
        //     ];

        //     $context = stream_context_create($opts);
        //     $fileContent = file_get_contents($fileUrl,false,$context);

        //     if(@json_decode($fileContent)->status == 'error'){
        //         return response()->json([
        //             'status'=>'error',
        //             'message'=>@json_decode($fileContent)->message->error
        //         ]);
        //     }
        //     file_put_contents($directory.$key.'.zip',$fileContent);
        //     $files[$key] = $fileContent;
        // }

        // Use storage path for updates (safe & consistent)
$directory = storage_path('app/updates');
if (!is_dir($directory)) {
    @mkdir($directory, 0755, true);
}

\Log::info('UPDATE: starting download of update files', ['dir' => $directory]);

$files = []; // will store full zip file paths

foreach ($response->data->files as $key => $fileUrl) {

    $zipPath = $directory . DIRECTORY_SEPARATOR . $key . '.zip';

    // Download via cURL (robust)
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $fileUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 60);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 15);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Purchase-Code: {$purchasecode}",
        "User-Agent: StakeyeUpdater/1.0"
    ]);

    $fileContent = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlErr  = curl_error($ch);
    curl_close($ch);

    if ($fileContent === false || $httpCode !== 200) {
        \Log::error('Update file download failed', compact('fileUrl','httpCode','curlErr'));
        return response()->json([
            'status' => 'error',
            'message' => ['Failed to download update file: '.$key]
        ]);
    }

    if (file_put_contents($zipPath, $fileContent) === false) {
        \Log::error('Failed to write update zip', ['path' => $zipPath]);
        return response()->json([
            'status' => 'error',
            'message' => ['Failed to save update file. Check permissions for: ' . $directory]
        ]);
    }

    // save path (not content) for later extraction
    $files[$key] = $zipPath;

    // DEBUG: keep a copy for inspection (optional)
    $debugCopy = $directory . DIRECTORY_SEPARATOR . 'debug-' . $key . '-' . now()->format('Ymd-His') . '.zip';
    @copy($zipPath, $debugCopy);
    \Log::info('Update zip saved', ['key'=>$key,'zip'=>$zipPath,'debug'=>$debugCopy]);
}


        // $fileNames = array_keys($files);
        // foreach($fileNames as $fileName){
        //     $rand    = Str::random(10);
        //     $dir     = base_path('temp/' . $rand);
        //     $extract = $this->extractZip(base_path('temp/' . $fileName.'.zip'), $dir);

        //     if ($extract == false) {
        //         $this->removeDir($dir);
        //         return response()->json([
        //             'status'=>'error',
        //             'message'=>['Something went wrong while extracting the update']
        //         ]);
        //     }

        //     if (!file_exists($dir . '/config.json')) {
        //         $this->removeDir($dir);
        //         return response()->json([
        //             'status'=>'error',
        //             'message'=>['Config file not found']
        //         ]);
        //     }

        //     $getConfig = file_get_contents($dir . '/config.json');
        //     $config    = json_decode($getConfig);

        //     $this->removeFile($directory . '/' . $fileName.'.zip');

        //     $mainFile = $dir . '/update.zip';
        //     if (!file_exists($mainFile)) {
        //         $this->removeDir($dir);
        //         return response()->json([
        //             'status'=>'error',
        //             'message'=>['Something went wrong while patching the update']
        //         ]);
        //     }


        //     //move file
        //     $extract = $this->extractZip(base_path('temp/' . $rand . '/update.zip'), base_path('../'));
        //     if ($extract == false) {
        //         return response()->json([
        //             'status'=>'error',
        //             'message'=>['Something went wrong while extracting the update']
        //         ]);
        //     }



        //     //Execute database
        //     if (file_exists($dir . '/update.sql')) {
        //         $sql = file_get_contents($dir . '/update.sql');
        //         DB::unprepared($sql);
        //     }

        //     $updateLog = new UpdateLog();
        //     $updateLog->version = $config->version;
        //     $updateLog->update_log = $config->changes;
        //     $updateLog->save();

        //     $this->removeDir($dir);

        // }

        $fileNames = array_keys($files);
foreach ($fileNames as $fileName) {
    $zipFilePath = $files[$fileName] ?? null;
    if (!$zipFilePath || !file_exists($zipFilePath)) {
        \Log::error('Expected update zip not found', ['expected' => $zipFilePath]);
        return response()->json(['status'=>'error','message'=>['Update package missing']]);
    }

    $rand = Str::random(10);
    $dir  = base_path('temp/' . $rand);
    @mkdir($dir, 0755, true);

    // extract the downloaded zip into $dir
    $extract = $this->extractZip($zipFilePath, $dir);
    if ($extract == false) {
        $this->removeDir($dir);
        \Log::error('Failed extracting first-level zip', ['zip'=>$zipFilePath,'dest'=>$dir]);
        return response()->json([
            'status'=>'error',
            'message'=>['Something went wrong while extracting the update']
        ]);
    }

    if (!file_exists($dir . '/config.json')) {
        $this->removeDir($dir);
        \Log::error('Config file not found in update package', ['dir'=>$dir]);
        return response()->json([
            'status'=>'error',
            'message'=>['Config file not found']
        ]);
    }

    $getConfig = file_get_contents($dir . '/config.json');
    $config    = json_decode($getConfig);

    // DEBUG: keep the zip for inspection (do not remove until verified)
    // $this->removeFile($zipFilePath); // comment out while debugging

    $mainFile = $dir . '/update.zip';
    if (!file_exists($mainFile)) {
        $this->removeDir($dir);
        \Log::error('update.zip missing inside package', ['dir'=>$dir]);
        return response()->json([
            'status'=>'error',
            'message'=>['Something went wrong while patching the update']
        ]);
    }

    // move/apply the inner update.zip into project root (be careful — this overwrites files)
    $extract = $this->extractZip($mainFile, base_path('../'));
    if ($extract == false) {
        \Log::error('Failed extracting main update.zip', ['file'=>$mainFile]);
        return response()->json([
            'status'=>'error',
            'message'=>['Something went wrong while extracting the update']
        ]);
    }

    // Execute database inside a transaction
    if (file_exists($dir . '/update.sql')) {
        $sql = file_get_contents($dir . '/update.sql');
        try {
            DB::beginTransaction();
            DB::unprepared($sql);
            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            \Log::error('Update SQL execution failed', ['error'=>$e->getMessage()]);
            return response()->json([
                'status'=>'error',
                'message'=>['DB update failed: ' . $e->getMessage()]
            ]);
        }
    }

    $updateLog = new UpdateLog();
    $updateLog->version = $config->version ?? null;
    $updateLog->update_log = $config->changes ?? null;
    $updateLog->save();

    // cleanup extracted temp dir
    $this->removeDir($dir);
}

        Artisan::call('optimize:clear');
        return response()->json([
            'status'=>'success',
            'message'=>['System updated successfully']
        ]);
    }

    public function systemUpdateLog(){
        $pageTitle = 'System Update Log';
        $updates = UpdateLog::orderBy('id','desc')->paginate(getPaginate());
        return view('admin.system.update_log',compact('pageTitle','updates'));
    }

   protected function extractZip($file, $extractTo)
{
    $zip = new \ZipArchive;
    $res = $zip->open($file);
    if ($res !== true) {
        \Log::error('Zip open failed', ['file'=>$file, 'code'=>$res]);
        return false;
    }

    for ($i = 0; $i < $zip->numFiles; $i++) {
        $name = $zip->getNameIndex($i);
        // reject dangerous paths
        if (strpos($name, '..') !== false || substr($name, 0, 1) === '/') {
            \Log::warning('Skipped suspicious zip entry', ['name'=>$name,'file'=>$file]);
            continue;
        }
        $zip->extractTo($extractTo, [$name]);
    }

    $zip->close();
    return true;
}


    protected function removeFile($path)
    {
        $fileManager = new FileManager();
        $fileManager->removeFile($path);
    }

    protected function removeDir($location)
    {
        $fileManager = new FileManager();
        $fileManager->removeDirectory($location);
    }
}
