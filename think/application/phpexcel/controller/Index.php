<?php

namespace app\phpexcel\controller;

use think\Db;
use think\Loader;

class Index
{
    public function index()
    {
        echo 123;
        die;
        $file_name = './static/test.xls';
        Loader::import('PHPExcel.Classes.PHPExcel');
        Loader::import('PHPExcel.Classes.PHPExcel.IOFactory.PHPExcel_IOFactory');
        Loader::import('PHPExcel.Classes.PHPExcel.Reader.Excel5');


         $data=$this->read($file_name);


        foreach ($data  as $k=>$v){

          if (is_numeric($v[2])){

            $data[$k][2] =date('Y-m-d H:i',intval((($v[2]-25569)*3600*24)+14400+12*3600));
              $dataArray[]=explode(' ',date('Y-m-d H:i',intval((($v[2]-25569)*3600*24)+14400+12*3600)))[0];

          }
      }
       $dataArray= array_unique($dataArray);
       foreach ($data as $k=>$v){
           foreach ($dataArray as $kk=>$vv){

                   //var_dump($v[2]);

                  $dav =explode( ' ',$v[2]);
//                 var_dump($dav);
               if ($vv==$dav[0]){
//                   var_dump($dav[0]);

                   $scertData[$vv]=array();
                   $scertData[$vv][]['username']=$v[1];
                   $scertData[$vv][]['uid']=$v[0];
                   $scertData[$vv][]['time']=$v[2];
               }




           }

       }
       var_dump($scertData);
       die;









    }


    public function read($filename, $encode = 'utf-8')
    {
        Loader::import('PHPExcel.Classes.PHPExcel');
        Loader::import('PHPExcel.Classes.PHPExcel.IOFactory.PHPExcel_IOFactory');
        Loader::import('PHPExcel.Classes.PHPExcel.Reader.Excel5');

     $PHPExcel = new \PHPExcel();
     var_dump($PHPExcel);
     die();
        $objReader = \PHPExcel_IOFactory::createReader('Excel5');

//        $objReader = PHPExcel_IOFactory::createReader('Excel5');

        $objReader->setReadDataOnly(true);

        $objPHPExcel = $objReader->load($filename);

        $objWorksheet = $objPHPExcel->getActiveSheet();

        $highestRow = $objWorksheet->getHighestRow();
        $highestColumn = $objWorksheet->getHighestColumn();
        $highestColumnIndex = \PHPExcel_Cell::columnIndexFromString($highestColumn);
        $excelData = array();
        for ($row = 1; $row <= $highestRow; $row++) {
            for ($col = 0; $col < $highestColumnIndex; $col++) {
                //$excelData[$row][] = (string)$objWorksheet->getCellByColumnAndRow($col, $row)->getValue();
                $excelData[$row][] = (string)$objWorksheet->getCellByColumnAndRow($col, $row)->getValue();
                //$excelData[$row][] = date('Y/m/d',\PHPExcel_Shared_Date::ExcelToPHP((string)$objWorksheet->getCellByColumnAndRow($col, $row)->getValue()));
            }
        }
        return $excelData;

    }

}
