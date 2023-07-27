<?php

//Varaibles Globales
$FechayhoraUTC=date("Y/m/d H:i:s");
$UTC=(string)$FechayhoraUTC;

$tokenNexus="";
$velocidad=0;
$latitud=0.0;
$longitud=0.0;
$statusgps="";
$ignition="";
$velocidadMaxima=0;




//Metodo para obtener el Eid de la sesion *Cuando se haya hecho el metodo de obtener el token, este metodo resivira como parametro el token*
function obtenerEid(){
    $token=
    $apiUrl = 'https://hst-api.wialon.com/wialon/ajax.html?svc=token/login&params={"token":"a090e35b2022c9ef3774d8b887afabf23B624379CEBAB3285C069F482BF444FA27481738"}';    
    $response = file_get_contents($apiUrl);
    if ($response !== false) {
        $data = json_decode($response, false);
        $eid=$data->eid;        
        echo $eid . "<br>";        
        $GLOBALS['eidGlobal']=$eid;
    } else {
        echo 'Error en la solicitud';
    }
}

//Regresa el id de la unidad
function obtenerIdUnidad(){    
    $apiUrl="https://hst-api.wialon.com/wialon/ajax.html?svc=core/search_items&params={%22spec%22:{%22itemsType%22:%22avl_unit%22,%22propName%22:%22sys_name%22,%22propValueMask%22:%22*KenworthManuel0208*%22,%22sortType%22:%22sys_name%22},%22force%22:1,%22flags%22:1439,%22from%22:0,%22to%22:0}&sid=". $GLOBALS['eidGlobal'];
    $response=file_get_contents($apiUrl);
    if($response!==false){
        $data=json_decode($response,false);        
        $idUnidad=$data->items[0]->id;
        echo $idUnidad . "<br>";
        return $idUnidad;
    }else{
        echo 'Error en la solicitud';
    }
}

//Obtiene la posicion de la unidad
function obtenerPosicion(){    
    $apiUrl="https://hst-api.wialon.com/wialon/ajax.html?svc=core/search_items&params={%22spec%22:{%22itemsType%22:%22avl_unit%22,%22propName%22:%22sys_name%22,%22propValueMask%22:%22*KenworthManuel0208*%22,%22sortType%22:%22sys_name%22},%22force%22:1,%22flags%22:1439,%22from%22:0,%22to%22:0}&sid=". $GLOBALS['eidGlobal'];
    $response=file_get_contents($apiUrl);
    if($response!==false){
        $data=json_decode($response,false);        
        $posx=$data->items[0]->pos->x;
        $posy=$data->items[0]->pos->y;
        $GLOBALS['longitud']=$posx;
        $GLOBALS['latitud']=$posy;                        
    }else{
        echo 'Error en la solicitud';
    }

}
//Obtiene el status del GPS
function statusDispositivo(){    
    $apiUrl='https://hst-api.wialon.com/wialon/ajax.html?svc=core/search_items&params={"spec":{"itemsType":"avl_unit","propName":"sys_name","propValueMask":"*KenworthManuel0208*","sortType":"sys_name"},"force":1,"flags":2097152,"from":0,"to":0}&sid='. $GLOBALS['eidGlobal'];
    $response=file_get_contents($apiUrl);
    if($response!==false){
        $data=json_decode($response,false);        
        $status=$data->items[0]->netconn;                        
        if($status==0){
            $GLOBALS['statusgps']=0;            
        }else{
            $GLOBALS['statusgps']=1;            
        }        
    }else{
        echo 'Error en la solicitud';
    }
}

//Indica si el motor esta encendido u apagado
function statusMotor(){    
    $crearSesion='https://hst-api.wialon.com/wialon/ajax.html?svc=events/update_units&params={"mode":"add","units":[{"id":27152606,"detect":{"trips":0,"lls":0,"sensors":0,"ignition":0,"counters":0,"speedings":0,"trg":0}}]}&sid='.$GLOBALS['eidGlobal'];
    $response=file_get_contents($crearSesion);
    $apiMotor='https://hst-api.wialon.com/wialon/ajax.html?svc=events/check_updates&params={"detalization":2}&sid='.$GLOBALS['eidGlobal'];
    $responseMotor=file_get_contents($apiMotor);    
    if($responseMotor!==false){
        $data=json_decode($responseMotor,true);        
        $status = $data["27152606"][1]["ignition"]["1"]["state"];        
        if($status==0){
            $GLOBALS['ignition']=0;    
            echo $GLOBALS['ignition'];                     
        }else{
            $GLOBALS['ignition']=1;   
            echo $GLOBALS['ignition'];             
        }        
    }else{
        echo 'Error en la solicitud';
    }    
}

//Obtiene la velocidad de la unidad
function obtenerVelocidad(){
    $crearSesion='https://hst-api.wialon.com/wialon/ajax.html?svc=events/update_units&params={"mode":"add","units":[{"id":27152606,"detect":{"trips":0,"lls":0,"sensors":0,"ignition":0,"counters":0,"speedings":0,"trg":0}}]}&sid='.$GLOBALS['eidGlobal'];
    $response=file_get_contents($crearSesion);
    $apiVelocidad='https://hst-api.wialon.com/wialon/ajax.html?svc=events/check_updates&params={"detalization":2}&sid='.$GLOBALS['eidGlobal'];
    $responseVelocidad=file_get_contents($apiVelocidad);
    if($responseVelocidad!==false){
        $data=json_decode($responseVelocidad,true);        
        $velocidad = $data["27152606"][4]["trips"]["curr_speed"];
        $GLOBALS['velocidad']=$velocidad;   
        echo $GLOBALS['velocidad'];      
    }else{
        echo "Error en la solicitud";
    }
}


function obtenerMaxVelocidad(){
    $url='https://hst-api.wialon.com/wialon/ajax.html?svc=unit/get_report_settings&params={%22itemId%22:27152606}&sid='.$GLOBALS['eidGlobal'];
    $response=file_get_contents($url);
    if($response!==false){
        $data=json_decode($response,false);
        $GLOBALS['velocidadMaxima']=$data->urbanMaxSpeed;        
    }else{
        echo "Error en la solicitud";
    }
}



//Obtiene el token para realizar los metodos POST
function obtenerTokenNexus() {
    $url = 'https://monitoreo.mastrace.com:40051/wsNexus/api/login/Authenticate';
    $datos = [
        "usuario" => "maulet",
        "password" => "m4u13t"
    ];

    $ch = curl_init();

    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($datos));
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));

    $resultado = curl_exec($ch);

    if ($resultado === false) {
        echo "Error en la petición: " . curl_error($ch);
    } else {
        // Manejar la respuesta exitosa aquí        
        $data=json_decode($resultado,false);
        $GLOBALS['tokenNexus']=$data->Token;
        //echo $GLOBALS['tokenNexus']."<br>";        
    }

    curl_close($ch);
}

//Envia la posicion de la unidad a la plataforma Nexus
function enviarPosNx(){
    $url="https://monitoreo.mastrace.com:40051/wsNexus/api/Eventos/Post";
    $data=[
        "eventos"=>[[
            "device_id"=>"863844052704860",
            "alias"=>"KenworthManuel0208",
            "event_time"=>$GLOBALS['UTC'],
            "lat"=>$GLOBALS['latitud'],
            "lon"=>$GLOBALS['longitud'],
            "speed"=>$GLOBALS['velocidad'],
            "even"=>500
        ]]               
    ];
    

    $options=[
        "http"=>[
            "header"=>"Content-type: application/json\r\n".
            "Authorization:".$GLOBALS['tokenNexus']."\r\n",
            "method" => "POST",
            "content" => json_encode($data)
        ]
    ];    
    $context = stream_context_create($options);
    $result = file_get_contents($url, false, $context);
    if ($result !== false) {
        // Manejar la respuesta exitosa aquí
        echo "Respuesta: " . $result."<br>";
    } else {
        echo "Error en la solicitud";
    }

}

//Envia el estado del motor a la plataforma Nexus
function enviarStatusMotorNx(){
    $url="https://monitoreo.mastrace.com:40051/wsNexus/api/Eventos/Post";
    if($GLOBALS['ignition']==0){
        $data=[
            "eventos"=>[[
                "device_id"=>"863844052704860",            
                "event_time"=>$GLOBALS['UTC'],
                "lat"=>$GLOBALS['latitud'],
                "lon"=>$GLOBALS['longitud'],
                "speed"=>$GLOBALS['velocidad'],
                "ignicion"=>0,
                "even"=>501
            ]]               
        ];

        $options=[
            "http"=>[
                "header"=>"Content-type: application/json\r\n".
                "Authorization:".$GLOBALS['tokenNexus']."\r\n",
                "method" => "POST",
                "content" => json_encode($data)
            ]
        ];

        $context = stream_context_create($options);
        $result = file_get_contents($url, false, $context);
        if ($result !== false) {
        // Manejar la respuesta exitosa aquí
        echo "Respuesta: " . $result."<br>";
        } else {
        echo "Error en la solicitud";
        }
    }else{
        $data=[
            "eventos"=>[[
                "device_id"=>"863844052704860",            
                "event_time"=>$GLOBALS['UTC'],
                "lat"=>$GLOBALS['latitud'],
                "lon"=>$GLOBALS['longitud'],
                "speed"=>$GLOBALS['velocidad'],
                "ignicion"=>1,
                "even"=>502
            ]]               
        ];

        $options=[
            "http"=>[
                "header"=>"Content-type: application/json\r\n".
                "Authorization:".$GLOBALS['tokenNexus']."\r\n",
                "method" => "POST",
                "content" => json_encode($data)
            ]
        ];

        $context = stream_context_create($options);
        $result = file_get_contents($url, false, $context);
        if ($result !== false) {
        // Manejar la respuesta exitosa aquí
        echo "Respuesta: " . $result."<br>";
        } else {
        echo "Error en la solicitud";
        }

    }
        
}
//Envia la ubicacion del motor a la plataforma Nexus
function ubicacionMotorNx(){
    $url="https://monitoreo.mastrace.com:40051/wsNexus/api/Eventos/Post";
    if($GLOBALS['ignition']==0){
        $data=[
            "eventos"=>[[
                "device_id"=>"863844052704860",            
                "event_time"=>$GLOBALS['UTC'],
                "lat"=>$GLOBALS['latitud'],
                "lon"=>$GLOBALS['longitud'],
                "speed"=>$GLOBALS['velocidad'],            
                "even"=>503
            ]]               
        ];

        $options=[
            "http"=>[
                "header"=>"Content-type: application/json\r\n".
                "Authorization:".$GLOBALS['tokenNexus']."\r\n",
                "method" => "POST",
                "content" => json_encode($data)
            ]
        ];

        $context = stream_context_create($options);
        $result = file_get_contents($url, false, $context);
        if ($result !== false) {
        // Manejar la respuesta exitosa aquí
        echo "Respuesta: " . $result."<br>";
        } else {
        echo "Error en la solicitud";
        }
    }else{
        $data=[
            "eventos"=>[[
                "device_id"=>"863844052704860",            
                "event_time"=>$GLOBALS['UTC'],
                "lat"=>$GLOBALS['latitud'],
                "lon"=>$GLOBALS['longitud'],
                "speed"=>$GLOBALS['velocidad'],            
                "even"=>504
            ]]               
        ];

        $options=[
            "http"=>[
                "header"=>"Content-type: application/json\r\n".
                "Authorization:".$GLOBALS['tokenNexus']."\r\n",
                "method" => "POST",
                "content" => json_encode($data)
            ]
        ];

        $context = stream_context_create($options);
        $result = file_get_contents($url, false, $context);
        if ($result !== false) {
        // Manejar la respuesta exitosa aquí
        echo "Respuesta: " . $result."<br>";
        } else {
        echo "Error en la solicitud";
        }
    }

}
//Envia el status del gps a la plataforma Nexus
function statusDispositivoNx(){
    $url="https://monitoreo.mastrace.com:40051/wsNexus/api/Eventos/Post";
    if($GLOBALS['statusgps']==0){
        $data=[
            "eventos"=>[[
                "device_id"=>"863844052704860",            
                "event_time"=>$GLOBALS['UTC'],
                "lat"=>$GLOBALS['latitud'],
                "lon"=>$GLOBALS['longitud'],
                "speed"=>$GLOBALS['velocidad'],
                "fix"=>"OFF",            
                "even"=>528
            ]]               
        ];

        $options=[
            "http"=>[
                "header"=>"Content-type: application/json\r\n".
                "Authorization:".$GLOBALS['tokenNexus']."\r\n",
                "method" => "POST",
                "content" => json_encode($data)
            ]
        ];

        $context = stream_context_create($options);
        $result = file_get_contents($url, false, $context);
        if ($result !== false) {
        // Manejar la respuesta exitosa aquí
        echo "Respuesta: " . $result."<br>";
        } else {
        echo "Error en la solicitud";
        }
    }else{
        $data=[
            "eventos"=>[[
                "device_id"=>"863844052704860",            
                "event_time"=>$GLOBALS['UTC'],
                "lat"=>$GLOBALS['latitud'],
                "lon"=>$GLOBALS['longitud'],
                "speed"=>$GLOBALS['velocidad'],
                "fix"=>"ON",            
                "even"=>529
            ]]               
        ];

        $options=[
            "http"=>[
                "header"=>"Content-type: application/json\r\n".
                "Authorization:".$GLOBALS['tokenNexus']."\r\n",
                "method" => "POST",
                "content" => json_encode($data)
            ]
        ];

        $context = stream_context_create($options);
        $result = file_get_contents($url, false, $context);
        if ($result !== false) {        
        echo "Respuesta: " . $result."<br>";
        } else {
        echo "Error en la solicitud";
        }
    }
}

function arrastreNx(){
    if($GLOBALS['ignition']==0 && $GLOBALS['velocidad']>0){
        $url="https://monitoreo.mastrace.com:40051/wsNexus/api/Eventos/Post";
        $data=[
            "eventos"=>[[
                "device_id"=>"863844052704860",            
                "event_time"=>$GLOBALS['UTC'],
                "lat"=>$GLOBALS['latitud'],
                "lon"=>$GLOBALS['longitud'],
                "speed"=>$GLOBALS['velocidad'],                
                "even"=>518
            ]]               
        ];

        $options=[
            "http"=>[
                "header"=>"Content-type: application/json\r\n".
                "Authorization:".$GLOBALS['tokenNexus']."\r\n",
                "method" => "POST",
                "content" => json_encode($data)
            ]
        ];
        $context = stream_context_create($options);
        $result = file_get_contents($url, false, $context);
        if ($result !== false) {        
        echo "Respuesta: " . $result."<br>";
        } else {
        echo "Error en la solicitud";
        }
    }else{
        echo "No hay arraste";
    }
}

function statusActividadNx(){
    $url="https://monitoreo.mastrace.com:40051/wsNexus/api/Eventos/Post";
    if($GLOBALS['ignition']==1 && $GLOBALS['velocidad']==0){
        $data=[
            "eventos"=>[[
                "device_id"=>"863844052704860",            
                "event_time"=>$GLOBALS['UTC'],
                "lat"=>$GLOBALS['latitud'],
                "lon"=>$GLOBALS['longitud'],
                "speed"=>$GLOBALS['velocidad'],                
                "even"=>520
            ]]               
        ];

        $options=[
            "http"=>[
                "header"=>"Content-type: application/json\r\n".
                "Authorization:".$GLOBALS['tokenNexus']."\r\n",
                "method" => "POST",
                "content" => json_encode($data)
            ]
        ];

        $context = stream_context_create($options);
        $result = file_get_contents($url, false, $context);
        if ($result !== false) {        
            echo "Respuesta: " . $result."<br>";
            return 1;
        } else {
            echo "Error en la solicitud";
        }
    }else{
        return 0;
    }    
}

function finalizaActividadNx(){
    $url="https://monitoreo.mastrace.com:40051/wsNexus/api/Eventos/Post";
    $status=statusActividad();
    if($GLOBALS['ignition']==0){
        echo "No hay actividad";
    }else{
        if($status==1 && $GLOBALS['velocidad']>0){
            $data=[
                "eventos"=>[[
                    "device_id"=>"863844052704860",            
                    "event_time"=>$GLOBALS['UTC'],
                    "lat"=>$GLOBALS['latitud'],
                    "lon"=>$GLOBALS['longitud'],
                    "speed"=>$GLOBALS['velocidad'],                
                    "even"=>519
                ]]               
            ];
            $options=[
                "http"=>[
                    "header"=>"Content-type: application/json\r\n".
                    "Authorization:".$GLOBALS['tokenNexus']."\r\n",
                    "method" => "POST",
                    "content" => json_encode($data)
                ]
            ];
            $context = stream_context_create($options);
            $result = file_get_contents($url, false, $context);
            if ($result !== false) {        
                echo "Respuesta: " . $result."<br>";
                return 1;
            } else {
                echo "Error en la solicitud";
            }
        }else{
            echo "La uninidad no se inmobilizo";
        }

    }
    
}

function iniciaExcesoVelNx(){
    $url="https://monitoreo.mastrace.com:40051/wsNexus/api/Eventos/Post";
    if( $GLOBALS['velocidad']>$GLOBALS['velocidadMaxima']){
        $data=[
            "eventos"=>[[
                "device_id"=>"863844052704860",            
                "event_time"=>$GLOBALS['UTC'],
                "lat"=>$GLOBALS['latitud'],
                "lon"=>$GLOBALS['longitud'],
                "speed"=>$GLOBALS['velocidad'],                
                "even"=>517
            ]]               
        ];

        $options=[
            "http"=>[
                "header"=>"Content-type: application/json\r\n".
                "Authorization:".$GLOBALS['tokenNexus']."\r\n",
                "method" => "POST",
                "content" => json_encode($data)
            ]
        ];

        $context = stream_context_create($options);
        $result = file_get_contents($url, false, $context);
        if ($result !== false) {        
            echo "Respuesta: " . $result."<br>";
            return 1;
        } else {
            echo "Error en la solicitud";
        }
    }else{
        echo "No se ha superado el limite de velocidad";
    }
}


function finalizaExcesoVelNx(){
    $url="https://monitoreo.mastrace.com:40051/wsNexus/api/Eventos/Post";
    $data=[
        "eventos"=>[[
            "device_id"=>"863844052704860",            
            "event_time"=>$GLOBALS['UTC'],
            "lat"=>$GLOBALS['latitud'],
            "lon"=>$GLOBALS['longitud'],
            "speed"=>$GLOBALS['velocidad'],                
            "even"=>514
        ]]               
    ];

    $options=[
        "http"=>[
            "header"=>"Content-type: application/json\r\n".
            "Authorization:".$GLOBALS['tokenNexus']."\r\n",
            "method" => "POST",
            "content" => json_encode($data)
        ]
    ];

    $context = stream_context_create($options);
    $result = file_get_contents($url, false, $context);
    if ($result !== false) {       
        echo "Respuesta: " . $result."<br>";        
    } else {
        echo "Error en la solicitud";
    }


}

obtenerEid();
obtenerIdUnidad();
obtenerPosicion();
statusDispositivo();
statusMotor();
obtenerVelocidad();
obtenerMaxVelocidad();
obtenerTokenNexus();
enviarPosNx();
enviarStatusMotorNx();
statusDispositivoNx(); 



?>
