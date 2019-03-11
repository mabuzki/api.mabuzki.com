<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;
use App\Http\Controllers\Controller;
use Redirect, Response;
use File;
use Goutte\Client;

class TestController extends Controller
{
    public $all;

    function test($name, $html, $nums) {
        preg_match('/\d+\.\d+/', $html, $matches);
        $total = $matches[0] * (int) $nums;
        $this->all += (int)$total;
        echo '<div><h2>'.$name.'<span> x '.$nums.' ≈ ￥'.$total.'</span></h2><div>'.$html.'</div></div>';
    }

    // public function __construct() {
    //     $this->$all = (int)0;
    // } 

    public function run()
    {
        $coins = array(
            // 'ALIS' => array(
            //     'name' => 'ALIS',
            //     'site' => 'http://www.feixiaohao.com/currencies/alis/',
            //     'num' => 1300,
            //     'total' => ''
            // ),

            'AMB' => array(
                'name' => 'AMB',
                'site' => 'http://www.feixiaohao.com/currencies/amber/',
                'num' => 1044,
                'total' => ''
            ),
            
            'BMC' => array(
                'name' => 'BMC',
                'site' => 'http://www.feixiaohao.com/currencies/blackmoon-crypto/',
                'num' => 155,
                'total' => ''
            ),

            'CAPP' => array(
                'name' => 'CAPP',
                'site' => 'http://www.feixiaohao.com/currencies/cappasity/',
                'num' => 33932,
                'total' => ''
            ),

            'DLT' => array(
                'name' => 'DLT',
                'site' => 'http://www.feixiaohao.com/currencies/agrello-delta/',
                'num' => 475,
                'total' => ''
            ),

            'KICK' => array(
                'name' => 'KICK',
                'site' => 'http://www.feixiaohao.com/currencies/kickico/',
                'num' => 3813,
                'total' => ''
            ),

            'OPT' => array(
                'name' => 'OPT',
                'site' => 'http://www.feixiaohao.com/currencies/opus/',
                'num' => 3570,
                'total' => ''
            ),

            'PIX' => array(
                'name' => 'PIX',
                'site' => 'http://www.feixiaohao.com/currencies/lampix/',
                'num' => 902,
                'total' => ''
            ),

            // 'SNT' => array(
            //     'name' => 'SNT',
            //     'site' => 'http://www.feixiaohao.com/currencies/status/',
            //     'num' => 1461,
            //     'total' => ''
            // ),

            'XNN' => array(
                'name' => 'XNN',
                'site' => 'http://www.feixiaohao.com/currencies/xenon/',
                'num' => 511,
                'total' => ''
            )
        );

        foreach ( $coins as $coin ) {
            $client = new Client();
            $crawler = $client->request('GET', $coin['site']);
            $crawler->filter('.coinprice')->each(function ($node) use ( $coin ) {
                $this->test($coin['name'], $node->html(), $coin['num']);
            });
            sleep(1);
        }

        echo $this->all;

        // $this->test('ALIS','￥3.32<span class="tags-green">7.6%</span>',1300);

        return view('test');
    }
}
