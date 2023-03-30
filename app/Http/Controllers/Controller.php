<?php

namespace App\Http\Controllers;

use App\Trait\HelperTrait;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use ValidatesRequests, HelperTrait;
}
