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
use Illuminate\Support\Facades\Storage;

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

        if($request->operation) {            
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

        $document_historyArray = [
            "_token" => $request->_token, 
            "removeNumbering" => $request->removeNumbering,
            "header" => $request->header,
            "cover" => $request->cover,
            "operation" => $request->operation,
            "footer" => $request->footer,
            "newFileNmae" => $request->newFileName,
            "items" => $request->items
        ];

        $document_history =json_encode($document_historyArray);
        
        if ($fileName !== "preview") {
            Document::create([
                'path' => $path,
                'user_id' => auth()->user()->id,
                'name' => $fileName,
                'document_history' => $document_history,
            ]);
        }
        // $documentsQuery = Document::select('document_history')
        //                     ->where('id', 1403)
        //                     ->get();
        // $documentsQuery = stripslashes(json_decode($documentsQuery, true));
        // return $documentsQuery;
        return url(\Storage::url($path));
    }

    public function data()
    {
        $user = Auth::user();

        if ($user) {
            // Retrieve the documents belonging to the user
            $documentsQuery = Document::where('user_id', $user->id);
            // $documentsQuery = Document::where('user_id', $user->id)
            //                 ->orderBy('created_at', 'desc')
            //                 ->get();
            // Loop through each document and decode the document_history
            // Return the response in DataTables' format
            // return $documentsQuery;
            return DataTables::eloquent($documentsQuery)->make(true);
        }
    }

    public function deleteDocFile(Request $request)
    {
        $documents = Document::query(); 
        $documentFilePath = str_replace('public/','',Document::find($request->document[0])->path);
        Storage::disk('public')->delete($documentFilePath);
        $documents->whereIn('id', $request->document)->delete(); 
        return response()->json('Document deleted');
    }
}