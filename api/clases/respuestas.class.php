<?php
    class respuestas{
        public $response = [
            'status' => "ok",
            "result" => array()
        ];


        public function error_405(){
            $this->response['status'] = "error";
            $this->response['result'] = array(
                "error_id" => "405",
                "error_msg" => "Metodo no permitido"
            );

            return $this->response;
        }

        public function error_200($string = "Datos incorrectos"){
            $this->response['status'] = "error";
            $this->response['result'] = array(
                "error_id" => "200",
                "error_msg" => $string
            );

            return $this->response;
        }

        public function error_400(){
            $this->response['status'] = "error";
            $this->response['result'] = array(
                "error_id" => "400",
                "error_msg" => "Datos enviados incompletos o con formato incorrecto"
            );

            return $this->response;
        }

        public function error_500($valor = "Error interno del servidor"){
            $this->response['status'] = "error";
            $this->response['result'] = array(
                "error_id" => "500",
                "error_msg" => $valor
            );
            return $this->response;
        }

        public function error_401($valor = "No autorizado"){
            $this->response['status'] = "error";
            $this->response['result'] = array(
                "error_id" => "401",
                "error_msg" => $valor
            );
            return $this->response;
        }

        public function error_422($valor = "Posible actividad sospechosa"){
            $this->response['status'] = "error";
            $this->response['result'] = array(
                "error_id" => "422",
                "error_msg" => $valor
            );
            return $this->response;
        }

        public function error_429($valor = "Exceso de peticiones, intente más tarde"){
            $this->response['status'] = "error";
            $this->response['result'] = array(
                "error_id" => "429",
                "error_msg" => $valor
            );
            return $this->response;
        }
    }


?>