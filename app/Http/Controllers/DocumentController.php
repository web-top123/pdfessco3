<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\File;
use App\Http\Requests\CreatePdfRequest;
use App\Services\Pdf;
use App\Services\PdfCounter;
use TCPDF;
use setasign\Fpdi\Tcpdf\Fpdi;
use Illuminate\Http\Request;
use App\Models\User;
use DataTables;
use Auth;


class DocumentController extends Controller
{

   public function index($id) {
       try{
        $pdfImporter = new Fpdi();
        $file = File::findOrFail($id);
        $mainpath = storage_path() .'/app' .'/public/uploaded/'  . basename($file->getOriginal()["path"]);
        $pageCount = $pdfImporter->setSourceFile($mainpath);
        return response()->json(['count' => $pageCount]);
        } catch (\Exception $e) {
            return response()->json(['status' => false ,'error' => $e->getMessage()], 200);
        }
    } 
    public function store(CreatePdfRequest $request)
    {
        $document = new Pdf;
        if ($request->removeNumbering) {
            $document->removeNumbering();
        }
        $document->addHeader($request->header);
        $document->addFooter($request->footer);

        if($request->cover){
            $document->addCover($request->cover);
        }

        if($request->operation['project']) {            
            $document->addOperation($request->operation);
        }

        foreach($request->items as $item) {

            if ($item['type'] === 'file') {
                $pages = array_key_exists('pages', $item) ? $item['pages'] : [];
                $document->addFile(File::findOrFail($item['id']), $pages);
            }
            if ($item['type'] === 'divider') {$document->addDivider($item['text']);}
            
        }

        $path = $document->save();

        Document::create([
            'path' => $path,
            'user_id' => auth()->user()->id,
        ]);

        return url(\Storage::url($path));
    }
    public function storeNameFile(CreatePdfRequest $request) {
        $document = new Pdf;
        if ($request->removeNumbering) {
            $document->removeNumbering();
        }
        $document->addHeader($request->header);
        $document->addFooter($request->footer);

        if($request->cover){
            $document->addCover($request->cover);
        }

        if($request->operation['project']) {            
            $document->addOperation($request->operation);
        }

        foreach($request->items as $item) {

            if ($item['type'] === 'file') {
                $pages = array_key_exists('pages', $item) ? $item['pages'] : [];
                $document->addFile(File::findOrFail($item['id']), $pages);
            }
            if ($item['type'] === 'divider') {$document->addDivider($item['text']);}
            
        }
        $fileName = $request->newFileName??"Pdfessco-Document";
        $path = $document->save($fileName);

        Document::create([
            'path' => $path,
            'user_id' => auth()->user()->id,
            'name' => $fileName,
        ]);
        return url(\Storage::url($path));
    }

    public function data()
    {
        $user = Auth::user();

        // if ($user) {
        //     // Retrieve the documents belonging to the user and format them as an array
        //     $documentsArray = Document::whereHas('user', function ($query) use ($user) {
        //         $query->where('id', $user->id);
        //     })->select('name', 'path', 'created_at')->get()->toArray();

        //     // Return the array of documents
        //     $responseData = ['data' => $documentsArray];

        //     // Return the response as JSON
        //     return response()->json($responseData);
        // }
        if ($user) {
            // Retrieve the documents belonging to the user
            $documentsQuery = Document::where('user_id', $user->id);
        
            // Return the response in DataTables' format
            return DataTables::eloquent($documentsQuery)->make(true);
        }
    }
}
