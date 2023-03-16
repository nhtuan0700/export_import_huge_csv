<?php

namespace App\Http\Controllers;

use App\Jobs\ExportCSVJob;

class ExportCSVController extends Controller
{
    public function index() {
        // return $this->exportCSV();
        ExportCSVJob::dispatch();
        return response()->json('export success');
    }

    public function exportCSV() {
        $name = 'users.csv';
        $headers = [
            'Content-Disposition' => 'attachment; filename='. $name,
        ];
        $colom = \Illuminate\Support\Facades\Schema::getColumnListing("users");
        $temp_colom = array_flip($colom);
        unset($temp_colom['id']);
        $colom = array_flip($temp_colom);
        return response()->stream(function() use($colom){
            $file = fopen('php://output', 'w+');
            fputcsv($file, $colom);
            $data = \App\Models\User::cursor();
            
            foreach ($data as $key => $value) {
                $data = $value->toArray();
                
                unset($data['id']);

                fputcsv($file, $data);
            }
            $blanks = array("\t","\t","\t","\t");
            fputcsv($file, $blanks);
            $blanks = array("\t","\t","\t","\t");
            fputcsv($file, $blanks);
            $blanks = array("\t","\t","\t","\t");
            fputcsv($file, $blanks);
            fclose($file);
        }, 200, $headers);
    }
}
