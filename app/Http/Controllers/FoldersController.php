<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Folder;
use Redirect;

class FoldersController extends Controller
{
    public function index(){
        $folders = Folder::all();
        return view('folders')->with('folders',$folders);
    }

    public function store(Request $request){
        $this->validate($request, [
            'name' => 'required|unique:folders',
            'description' => 'required',
        ]);

        $folder = New Folder;
        $folder->name        = $request->input('name');
        $folder->description = $request->input('description');
        $folder->created_at  = date('Y-m-d h:i:s',time());
        if($folder->save()){
            return Redirect::to('folders');
        }else{
            return Redirect::back()->withInput()->withErrors('更新失败');
        }
    }

    public function destroy($id)
    {
        Folder::find($id)->delete();
        return Redirect::to('folders');
    }
}