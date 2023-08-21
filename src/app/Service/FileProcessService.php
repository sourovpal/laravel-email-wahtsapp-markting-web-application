<?php

namespace App\Service;

use App\Models\Contact;
use App\Models\EmailContact;
use App\Models\Import;
use Illuminate\Support\LazyCollection;
use Shuchkin\SimpleXLSX;
class FileProcessService
{

  

    public function processExel($file)
    {
        $results =  LazyCollection::make(function () use ($file) {
            $xlsx = SimpleXLSX::parse($file);
            foreach ($xlsx->rows() as $row) {
                yield $row;
            }
        })->skip(1);
        $list = [];
        foreach($results->chunk(500) as $chunks)
        {
            foreach($chunks as $row){
                if(isset($row[1]) &&  isset($row[0])){
                    $list[$row[1]] = $row[0];
                }
            }
        }
        $resArr [0] = $list;
        return $resArr;
    }
    public function processCsv($file)
    {
        $results  =  LazyCollection::make(function () use ($file) {
            $handle = fopen($file, 'r');
            while (($row = fgetcsv($handle, 100000)) !== false) {
                yield  $row;
            }
            fclose($handle);
        })->skip(1);
        $list = [];
        foreach($results->chunk(500) as $chunks)
        {
            foreach($chunks as $row){
                if(isset($row[1]) &&  isset($row[0])){
                    $list[$row[1]] = $row[0];
                }
            }
        }
        $resArr [0] = $list;
        return $resArr;
    }

  
}
