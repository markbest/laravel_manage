<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Folder;
use App\File;
use Redirect;
use Illuminate\Support\Facades\Response;

class FilesController extends Controller
{
    public function index(){
        $folders = Folder::where('parent_id','0')->get();
        $files = File::where('folders_id','0')->get();
        return view('files',['files'=>$files, 'folders'=>$folders, 'current_id'=>0]);
    }

    public function upload(Request $request){
        $result = array();
        $message = '';
        $file = $request->file('file');
        try{
            $destinationPath = 'uploads/files/'.date('Y-m',time()).'/';
            $extension = $file->getClientOriginalExtension();
            $fileName = str_random(10) . '.' . $extension;
            $file->move($destinationPath, $fileName);
            $status = 'ok';
            $message = 'success upload';

            $document = New File;
            $document->folders_id = 0;
            $document->name = $file->getClientOriginalName();
            $document->size = $file->getClientSize();
            $document->link = $destinationPath.$fileName;
            $document->type = $extension;
            $document->created_at = date('Y-m-d h:i:s', time());
            $document->save();

        }catch (\Exception $e){
            $message = 'Some error occurs when uploading file';
            $status = 'nok';
        }

        $result['message'] = $message;
        $result['status'] = $status;
        return json_encode($result);
    }

    public function move(Request $request){
        $folder = $request->input('folder');
        $files = $request->input('files');

        if($folder && is_array($files) && count($files)){
            foreach($files as $file){
                $object = File::find($file);
                $object->folders_id = $folder;
                $object->save();
            }
        }
        return Redirect::to('files');
    }

    public function delete(Request $request){
        $ids = $request->input('del-file-ids');
        $ids_array = explode(',',$ids);
        foreach($ids_array as $id){
            $file = File::find($id);
            unlink($file->link);
            $file->delete();
        }
        return Redirect::to('files');
    }

    public function folder($id){
        $folders = Folder::where('parent_id',$id)->get();
        $current_folders = Folder::find($id);
        $files = File::where('folders_id',$id)->get();
        return view('files',['files'=>$files, 'folders'=>$folders, 'current_id'=>$current_folders->id, 'parent_id'=>$current_folders->parent_id]);
    }

    public function download($id){
        $file = File::find($id);
        $newfile = public_path(). '/'. $file->link;

        $headers = array(
            'Content-Type: application/'.$file->type,
        );

        return Response::download($newfile, $file->name, $headers);
    }
}
