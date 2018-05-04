<?php
namespace App\Http\Controllers\Auth;
use App\Music;
use Illuminate\Support\Facades\DB;
use \App\Http\Controllers\Controller;
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/4/20 0020
 * Time: 下午 4:01
 */

class IndexController extends Controller{

    public function __construct()
    {
        /*表示表名*/
        $this->table='ims_hr_photo_music';
    }

    public function index(){

//        $data=DB::table($this->table)->get();
//  $data=Music::all()->toArray();
// dd($data);


return view('index.index');

    }


}