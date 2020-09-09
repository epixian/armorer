<?php

namespace App\Http\Controllers;

use App\Parser;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ParserController extends Controller
{
    /**
     * @param  Request  $request
     * @return Response $response
     */
    public function parse(Request $request) 
    {

    }

    /**
     * @param  Request  $request
     * @return Response $response
     */
    public function train(Request $request)
    {
        $blazon = $request->get('blazon');
        $output = $request->get('parsed');

        $parser = new Parser();
        $parser->train($blazon, $output);
    }

    /**
     * @param  Request  $request
     * @return Response $response
     */
    public function predict(Request $request)
    {

    }
}
