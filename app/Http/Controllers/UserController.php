<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Jobs\UserCsvProcess;
use Illuminate\Support\Facades\Bus;

class UserController extends Controller
{
    public function import(Request $request)
    {
        try {
            //print_r($request->file());
            // $csvMimes = array('text/x-comma-separated-values', 'text/comma-separated-values', 'application/octet-stream', 'application/vnd.ms-excel', 'application/x-csv', 'text/x-csv', 'text/csv', 'application/csv', 'application/excel', 'application/vnd.msexcel', 'text/plain');
            $csvMimes = array('application/x-csv', 'text/x-csv', 'text/csv', 'application/csv');
            if (!empty($_FILES['file']['name']) && in_array($_FILES['file']['type'], $csvMimes)) {
                $csvFile = fopen($_FILES['file']['tmp_name'], 'r');
                fgetcsv($csvFile, 'r');
                $x = 0;
                $error = [];
                /*** Check special char  ***/
                $checkSpecialChar_userCode = '';
                $checkSpecialChar_userName = '';
                $checkSpecialChar_userAddress = "";

                /*** Check blank entry  ***/
                $checkBlankData_userCode = '';
                $checkBlankData_userName = '';
                $checkBlankData_userAddress = '';

                while (($line = fgetcsv($csvFile, 'r')) !== FALSE) {
                    $x++;
                    // Get row data
                    if ($line[0] == '') {
                        $checkBlankData_userCode .=  " row " . $x. " and";
                    } elseif ($line[1] == '') {
                        $checkBlankData_userName .= " row " . $x. " and";
                    } elseif ($line[2] == '') {
                        $checkBlankData_userAddress .= " row " . $x;
                    } elseif (preg_match('/[\^£$%&*()}{@#~?><>,|=_+¬-]/', $line[0])) {
                        $checkSpecialChar_userCode .= " row " . $x. " and";
                    } elseif (preg_match('/[\^£$%&*()}{@#~?><>,|=_+¬-]/', $line[1])) {
                        $checkSpecialChar_userName .= " row " . $x. " and";
                    } elseif (preg_match('/[\^£$%&*()}{@#~?><>,|=_+¬-]/', $line[2])) {
                        $checkSpecialChar_userAddress .= " row " . $x. " and";
                    }
                }

                if ($x > 1000) {
                    $error[] = "Number of rows greater than 1000";
                }
                /**** Message For Special Char *****/
                if ($checkSpecialChar_userCode !=''){
                    $error[] = "User Code contains symbols at ".rtrim($checkSpecialChar_userCode,"and");
                }
                if ($checkSpecialChar_userName !=''){
                    $error[] = "User Name contains symbols at ".rtrim($checkSpecialChar_userName,"and");
                }
                if ($checkSpecialChar_userAddress !=''){
                    $error[] = "User Address contains symbols at ".rtrim($checkSpecialChar_userAddress,"and");
                }

                /**** Message For Blank Entry *****/
                if ($checkBlankData_userCode !=''){
                    $error[] = "User Code blank at ".rtrim($checkBlankData_userCode,"and");
                }
                if ($checkBlankData_userName !=''){
                    $error[] = "User Name blank at ".rtrim($checkBlankData_userName,"and");
                }
                if ($checkBlankData_userAddress !=''){
                    $error[] = "User Address blank at ".rtrim($checkBlankData_userAddress,"and");
                }

                if (sizeof($error) == 0) {
                    /** usage of queue*/
                    $csvData = fopen($_FILES['file']['tmp_name'], 'r');
                    $temp = [];
                    $y = 0;
                    while (($newline = fgetcsv($csvData, 'r')) !== FALSE) {
                        $y++;
                        if ($y > 1) {
                            $arr['user_code'] = $newline[0];
                            $arr['user_name'] = $newline[1];
                            $arr['user_address'] = $newline[2];
                            array_push($temp, $arr);
                        }
                    }
                    $header = [];
                    $data = $temp; //csv file data
                    $batch = Bus::batch([])->dispatch();
                    $batch->add(new UserCsvProcess($data, $header));
                   // return $batch;
                    return ['status_code'=>200,'status'=>true,'message'=>"Data has been successfully uploaded."];
                }else{
                    return ['status_code'=>400,'status'=>false,'message'=>$error];
                }
            } else {
                return "incorrect file format";
            }
        }catch(\Exception $e){
            return ['status_code'=>500,'status'=>false,'message'=>$e->getMessage()];
        }
    }
}
