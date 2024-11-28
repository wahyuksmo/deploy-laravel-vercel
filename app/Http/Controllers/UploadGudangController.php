<?php

namespace App\Http\Controllers;

use App\Repositories\UploadGudangRepository;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;


class UploadGudangController extends Controller
{
    //
    public function index(Request $request) {


        if ($request->ajax()) {
            $data = DB::select("SELECT * FROM stock_gudang");
            return DataTables::of($data)
                ->make(true);
        }

        return view('uploadgudang.index');
    }


    public function validationUpload(Request $request) {

        $request->validate([
            'file' => 'mimes:xlsx,xls,csv',
        ]);


        $uploadedFile = $request->file('file');
        $spreadsheet = IOFactory::load($uploadedFile);
        $worksheet = $spreadsheet->getActiveSheet();


        $data = [];

        $rules = [
            'kode_gudang'   => 'required',
            'nama_gudang'   => 'required',
            'kode_item'     => 'required',
            'quantity'      => 'int',
            'standard_stock'=> 'int',
            'death_stock'   => 'int'
        ];

        $headerMapping = [
            'Kode Gudang'   => 'kode_gudang',
            'Nama Gudang'   => 'nama_gudang',
            'Kode Item'     => 'kode_item',
            'Quantity'      => 'quantity',
            'Standard Stock'=> 'standard_stock',
            'Death Stock'   => 'death_stock'
        ];

        $rows = $worksheet->toArray();

        if (!empty($rows)) {
            $headerRow = array_shift($rows);
    
            foreach ($headerRow as $header) {
                if (array_key_exists($header, $headerMapping)) {
                    $key = $headerMapping[$header]; 
                    $keys[] = $key;
                }
            }
        }


        foreach ($rows as $index => $row) {
            $rowData = array_combine($keys, $row);
    
            $isValid = true;
            $validationMessage = '';
    
            foreach ($rules as $column => $rule) {
                $cellValue = $rowData[$column] ?? null;
                $validator = Validator::make([$column => $cellValue], [$column => $rule]);
    
                if ($validator->fails()) {
                    $isValid = false;
                    $validationMessage = $validator->errors()->first();
                }
            }
            $rowData['status_validation'] = $isValid ? 'Success' : 'Error';
            $rowData['message_validation'] = $isValid ? 'Row Data Is Valid' : $validationMessage;
    
    
            $data[] = $rowData;
        }

        
        return response()->json([
            'success' => true,
            'data' => $data,
            'message' => 'File processed successfully.'
        ]);

    }


    public function uploadAction(Request $request)
    {
        try{

            $data = UploadGudangRepository::upload($request->all());

            return response()->json([
                'message' => 'Data Berhasil Disimpan.',
                'data'    => $data
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], 500);
        }
    }

}
