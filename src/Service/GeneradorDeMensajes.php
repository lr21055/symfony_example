<?php
namespace App\Service;

class GeneradorDeMensajes {

  public function getMensaje($message, $data) {
    $respuesta = ['message' => $message, 'data' => $data];
    return $respuesta;
}

}