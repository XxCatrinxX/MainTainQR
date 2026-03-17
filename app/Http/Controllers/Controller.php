<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;
}
// | Este es el controlador base de tu aplicación. Todos los demás controladores deberían extender este controlador. En este controlador puedes colocar cualquier lógica común que quieras compartir entre todos tus controladores.
