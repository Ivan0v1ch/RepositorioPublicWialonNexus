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

$latitud2=0.0;
$longitud2=0.0;
$statusgps2="";
$ignition2="";

$latitud3=0.0;
$longitud3=0.0;
$statusgps3="";
$ignition3="";

$latitud4=0.0;
$longitud4=0.0;
$statusgps4="";
$ignition4="";

$latitud5=0.0;
$longitud5=0.0;
$statusgps5="";
$ignition5="";







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


function obtenerIdUnidad2(){    
    $apiUrl="https://hst-api.wialon.com/wialon/ajax.html?svc=core/search_items&params={%22spec%22:{%22itemsType%22:%22avl_unit%22,%22propName%22:%22sys_name%22,%22propValueMask%22:%22*T600Verde*%22,%22sortType%22:%22sys_name%22},%22force%22:1,%22flags%22:1439,%22from%22:0,%22to%22:0}&sid=". $GLOBALS['eidGlobal'];
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


function obtenerIdUnidad3(){    
    $apiUrl="https://hst-api.wialon.com/wialon/ajax.html?svc=core/search_items&params={%22spec%22:{%22itemsType%22:%22avl_unit%22,%22propName%22:%22sys_name%22,%22propValueMask%22:%22*FreightlinerNaranja*%22,%22sortType%22:%22sys_name%22},%22force%22:1,%22flags%22:1439,%22from%22:0,%22to%22:0}&sid=". $GLOBALS['eidGlobal'];
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

function obtenerIdUnidad4(){    
    $apiUrl="https://hst-api.wialon.com/wialon/ajax.html?svc=core/search_items&params={%22spec%22:{%22itemsType%22:%22avl_unit%22,%22propName%22:%22sys_name%22,%22propValueMask%22:%22*ProstarJocelyn07*%22,%22sortType%22:%22sys_name%22},%22force%22:1,%22flags%22:1439,%22from%22:0,%22to%22:0}&sid=". $GLOBALS['eidGlobal'];
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

function obtenerIdUnidad5(){    
    $apiUrl="https://hst-api.wialon.com/wialon/ajax.html?svc=core/search_items&params={%22spec%22:{%22itemsType%22:%22avl_unit%22,%22propName%22:%22sys_name%22,%22propValueMask%22:%22*KenworthBlanco01*%22,%22sortType%22:%22sys_name%22},%22force%22:1,%22flags%22:1439,%22from%22:0,%22to%22:0}&sid=". $GLOBALS['eidGlobal'];
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







function obtenerPosicion2(){    
    $apiUrl="https://hst-api.wialon.com/wialon/ajax.html?svc=core/search_items&params={%22spec%22:{%22itemsType%22:%22avl_unit%22,%22propName%22:%22sys_name%22,%22propValueMask%22:%22*T600Verde*%22,%22sortType%22:%22sys_name%22},%22force%22:1,%22flags%22:1439,%22from%22:0,%22to%22:0}&sid=". $GLOBALS['eidGlobal'];
    $response=file_get_contents($apiUrl);
    if($response!==false){
        $data=json_decode($response,false);        
        $posx=$data->items[0]->pos->x;
        $posy=$data->items[0]->pos->y;
        $GLOBALS['longitud2']=$posx;
        $GLOBALS['latitud2']=$posy;      
        echo $GLOBALS['longitud2'] ."<br>";
        echo $GLOBALS['latitud2'] ."<br>";
    }else{
        echo 'Error en la solicitud';
    }

}

function obtenerPosicion3(){    
    $apiUrl="https://hst-api.wialon.com/wialon/ajax.html?svc=core/search_items&params={%22spec%22:{%22itemsType%22:%22avl_unit%22,%22propName%22:%22sys_name%22,%22propValueMask%22:%22*FreightlinerNaranja*%22,%22sortType%22:%22sys_name%22},%22force%22:1,%22flags%22:1439,%22from%22:0,%22to%22:0}&sid=". $GLOBALS['eidGlobal'];
    $response=file_get_contents($apiUrl);
    if($response!==false){
        $data=json_decode($response,false);        
        $posx=$data->items[0]->pos->x;
        $posy=$data->items[0]->pos->y;
        $GLOBALS['longitud3']=$posx;
        $GLOBALS['latitud3']=$posy;    
        echo $GLOBALS['longitud3'] ."<br>";
        echo $GLOBALS['latitud3'] ."<br>";                    
    }else{
        echo 'Error en la solicitud';
    }

}

function obtenerPosicion4(){    
    $apiUrl="https://hst-api.wialon.com/wialon/ajax.html?svc=core/search_items&params={%22spec%22:{%22itemsType%22:%22avl_unit%22,%22propName%22:%22sys_name%22,%22propValueMask%22:%22*ProstarJocelyn07*%22,%22sortType%22:%22sys_name%22},%22force%22:1,%22flags%22:1439,%22from%22:0,%22to%22:0}&sid=". $GLOBALS['eidGlobal'];
    $response=file_get_contents($apiUrl);
    if($response!==false){
        $data=json_decode($response,false);        
        $posx=$data->items[0]->pos->x;
        $posy=$data->items[0]->pos->y;
        $GLOBALS['longitud4']=$posx;
        $GLOBALS['latitud4']=$posy;    
        echo $GLOBALS['longitud4'] ."<br>";
        echo $GLOBALS['latitud4'] ."<br>";                    
    }else{
        echo 'Error en la solicitud';
    }

}

function obtenerPosicion5(){    
    $apiUrl="https://hst-api.wialon.com/wialon/ajax.html?svc=core/search_items&params={%22spec%22:{%22itemsType%22:%22avl_unit%22,%22propName%22:%22sys_name%22,%22propValueMask%22:%22*KenworthBlanco01*%22,%22sortType%22:%22sys_name%22},%22force%22:1,%22flags%22:1439,%22from%22:0,%22to%22:0}&sid=". $GLOBALS['eidGlobal'];
    $response=file_get_contents($apiUrl);
    if($response!==false){
        $data=json_decode($response,false);        
        $posx=$data->items[0]->pos->x;
        $posy=$data->items[0]->pos->y;
        $GLOBALS['longitud5']=$posx;
        $GLOBALS['latitud5']=$posy;    
        echo $GLOBALS['longitud5'] ."<br>";
        echo $GLOBALS['latitud5'] ."<br>";                    
    }else{
        echo 'Error en la solicitud';
    }

}






function statusMotor2(){    
    $crearSesion='https://hst-api.wialon.com/wialon/ajax.html?svc=events/update_units&params={"mode":"add","units":[{"id":27157216,"detect":{"trips":0,"lls":0,"sensors":0,"ignition":0,"counters":0,"speedings":0,"trg":0}}]}&sid='.$GLOBALS['eidGlobal'];
    $response=file_get_contents($crearSesion);
    $apiMotor='https://hst-api.wialon.com/wialon/ajax.html?svc=events/check_updates&params={"detalization":2}&sid='.$GLOBALS['eidGlobal'];
    $responseMotor=file_get_contents($apiMotor);    
    if($responseMotor!==false){
        $data=json_decode($responseMotor,true);        
        $status = $data["27157216"][1]["ignition"]["1"]["state"];        
        if($status==0){
            $GLOBALS['ignition2']=0;    
            echo $GLOBALS['ignition2']."<br>";                     
        }else{
            $GLOBALS['ignition2']=1;   
            echo $GLOBALS['ignition2']."<br>";             
        }        
    }else{
        echo 'Error en la solicitud';
    }    
}

function statusMotor3(){    
    $crearSesion='https://hst-api.wialon.com/wialon/ajax.html?svc=events/update_units&params={"mode":"add","units":[{"id":27157166,"detect":{"trips":0,"lls":0,"sensors":0,"ignition":0,"counters":0,"speedings":0,"trg":0}}]}&sid='.$GLOBALS['eidGlobal'];
    $response=file_get_contents($crearSesion);
    $apiMotor='https://hst-api.wialon.com/wialon/ajax.html?svc=events/check_updates&params={"detalization":2}&sid='.$GLOBALS['eidGlobal'];
    $responseMotor=file_get_contents($apiMotor);    
    if($responseMotor!==false){
        $data=json_decode($responseMotor,true);        
        $status = $data["27157166"][1]["ignition"]["1"]["state"];        
        if($status==0){
            $GLOBALS['ignition3']=0;    
            echo $GLOBALS['ignition3']."<br>";                     
        }else{
            $GLOBALS['ignition3']=1;   
            echo $GLOBALS['ignition3']."<br>";             
        }        
    }else{
        echo 'Error en la solicitud';
    }    
}


function statusMotor4(){    
    $crearSesion='https://hst-api.wialon.com/wialon/ajax.html?svc=events/update_units&params={"mode":"add","units":[{"id":27168757,"detect":{"trips":0,"lls":0,"sensors":0,"ignition":0,"counters":0,"speedings":0,"trg":0}}]}&sid='.$GLOBALS['eidGlobal'];
    $response=file_get_contents($crearSesion);
    $apiMotor='https://hst-api.wialon.com/wialon/ajax.html?svc=events/check_updates&params={"detalization":2}&sid='.$GLOBALS['eidGlobal'];
    $responseMotor=file_get_contents($apiMotor);    
    if($responseMotor!==false){
        $data=json_decode($responseMotor,true);        
        $status = $data["27168757"][1]["ignition"]["1"]["state"];        
        if($status==0){
            $GLOBALS['ignition4']=0;    
            echo $GLOBALS['ignition4']."<br>";                     
        }else{
            $GLOBALS['ignition4']=1;   
            echo $GLOBALS['ignition4']."<br>";             
        }        
    }else{
        echo 'Error en la solicitud';
    }    
}

function statusMotor5(){    
    $crearSesion='https://hst-api.wialon.com/wialon/ajax.html?svc=events/update_units&params={"mode":"add","units":[{"id":27222101,"detect":{"trips":0,"lls":0,"sensors":0,"ignition":0,"counters":0,"speedings":0,"trg":0}}]}&sid='.$GLOBALS['eidGlobal'];
    $response=file_get_contents($crearSesion);
    $apiMotor='https://hst-api.wialon.com/wialon/ajax.html?svc=events/check_updates&params={"detalization":2}&sid='.$GLOBALS['eidGlobal'];
    $responseMotor=file_get_contents($apiMotor);    
    if($responseMotor!==false){
        $data=json_decode($responseMotor,true);        
        $status = $data["27222101"][1]["ignition"]["1"]["state"];        
        if($status==0){
            $GLOBALS['ignition5']=0;    
            echo $GLOBALS['ignition5']."<br>";                     
        }else{
            $GLOBALS['ignition5']=1;   
            echo $GLOBALS['ignition5']."<br>";             
        }        
    }else{
        echo 'Error en la solicitud';
    }    
}


function statusDispositivo2(){    
    $apiUrl='https://hst-api.wialon.com/wialon/ajax.html?svc=core/search_items&params={"spec":{"itemsType":"avl_unit","propName":"sys_name","propValueMask":"*T600Verde*","sortType":"sys_name"},"force":1,"flags":2097152,"from":0,"to":0}&sid='. $GLOBALS['eidGlobal'];
    $response=file_get_contents($apiUrl);
    if($response!==false){
        $data=json_decode($response,false);        
        $status=$data->items[0]->netconn;                        
        if($status==0){
            echo $GLOBALS['statusgps2']=0 ."<br>";            
        }else{
            echo $GLOBALS['statusgps2']=1 ."<br>";            
        }        
    }else{
        echo 'Error en la solicitud';
    }
}

function statusDispositivo3(){    
    $apiUrl='https://hst-api.wialon.com/wialon/ajax.html?svc=core/search_items&params={"spec":{"itemsType":"avl_unit","propName":"sys_name","propValueMask":"*FreightlinerNaranja*","sortType":"sys_name"},"force":1,"flags":2097152,"from":0,"to":0}&sid='. $GLOBALS['eidGlobal'];
    $response=file_get_contents($apiUrl);
    if($response!==false){
        $data=json_decode($response,false);        
        $status=$data->items[0]->netconn;                        
        if($status==0){
            echo $GLOBALS['statusgps3']=0 ."<br>";            
        }else{
            echo $GLOBALS['statusgps3']=1 ."<br>";            
        }        
    }else{
        echo 'Error en la solicitud';
    }
}

function statusDispositivo4(){    
    $apiUrl='https://hst-api.wialon.com/wialon/ajax.html?svc=core/search_items&params={"spec":{"itemsType":"avl_unit","propName":"sys_name","propValueMask":"*KenworthBlanco01*","sortType":"sys_name"},"force":1,"flags":2097152,"from":0,"to":0}&sid='. $GLOBALS['eidGlobal'];
    $response=file_get_contents($apiUrl);
    if($response!==false){
        $data=json_decode($response,false);        
        $status=$data->items[0]->netconn;                        
        if($status==0){
            echo $GLOBALS['statusgps5']=0 ."<br>";            
        }else{
            echo $GLOBALS['statusgps5']=1 ."<br>";            
        }        
    }else{
        echo 'Error en la solicitud';
    }
}


function statusDispositivo5(){    
    $apiUrl='https://hst-api.wialon.com/wialon/ajax.html?svc=core/search_items&params={"spec":{"itemsType":"avl_unit","propName":"sys_name","propValueMask":"*ProstarJocelyn07*","sortType":"sys_name"},"force":1,"flags":2097152,"from":0,"to":0}&sid='. $GLOBALS['eidGlobal'];
    $response=file_get_contents($apiUrl);
    if($response!==false){
        $data=json_decode($response,false);        
        $status=$data->items[0]->netconn;                        
        if($status==0){
            echo $GLOBALS['statusgps4']=0 ."<br>";            
        }else{
            echo $GLOBALS['statusgps4']=1 ."<br>";            
        }        
    }else{
        echo 'Error en la solicitud';
    }
}



function enviarPosNx2(){
    $url="https://monitoreo.mastrace.com:40051/wsNexus/api/Eventos/Post";
    $data=[
        "eventos"=>[[
            "device_id"=>"864893036178294",            
            "event_time"=>$GLOBALS['UTC'],
            "lat"=>$GLOBALS['latitud2'],
            "lon"=>$GLOBALS['longitud2'],
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

function enviarPosNx3(){
    $url="https://monitoreo.mastrace.com:40051/wsNexus/api/Eventos/Post";
    $data=[
        "eventos"=>[[
            "device_id"=>"864893037414045",            
            "event_time"=>$GLOBALS['UTC'],
            "lat"=>$GLOBALS['latitud3'],
            "lon"=>$GLOBALS['longitud3'],
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

function enviarPosNx4(){
    $url="https://monitoreo.mastrace.com:40051/wsNexus/api/Eventos/Post";
    $data=[
        "eventos"=>[[
            "device_id"=>"863844051008354",            
            "event_time"=>$GLOBALS['UTC'],
            "lat"=>$GLOBALS['latitud4'],
            "lon"=>$GLOBALS['longitud4'],
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


function enviarPosNx5(){
    $url="https://monitoreo.mastrace.com:40051/wsNexus/api/Eventos/Post";
    $data=[
        "eventos"=>[[
            "device_id"=>"864893038348267",            
            "event_time"=>$GLOBALS['UTC'],
            "lat"=>$GLOBALS['latitud5'],
            "lon"=>$GLOBALS['longitud5'],
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










function enviarStatusMotorNx2(){
    $url="https://monitoreo.mastrace.com:40051/wsNexus/api/Eventos/Post";
    if($GLOBALS['ignition2']==0){
        $data=[
            "eventos"=>[[
                "device_id"=>"864893036178294",            
                "event_time"=>$GLOBALS['UTC'],
                "lat"=>$GLOBALS['latitud2'],
                "lon"=>$GLOBALS['longitud2'],
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
                "device_id"=>"864893036178294",            
                "event_time"=>$GLOBALS['UTC'],
                "lat"=>$GLOBALS['latitud2'],
                "lon"=>$GLOBALS['longitud2'],
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

function enviarStatusMotorNx3(){
    $url="https://monitoreo.mastrace.com:40051/wsNexus/api/Eventos/Post";
    if($GLOBALS['ignition3']==0){
        $data=[
            "eventos"=>[[
                "device_id"=>"864893037414045",            
                "event_time"=>$GLOBALS['UTC'],
                "lat"=>$GLOBALS['latitud3'],
                "lon"=>$GLOBALS['longitud3'],
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
                "device_id"=>"864893037414045",            
                "event_time"=>$GLOBALS['UTC'],
                "lat"=>$GLOBALS['latitud3'],
                "lon"=>$GLOBALS['longitud3'],
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

function enviarStatusMotorNx4(){
    $url="https://monitoreo.mastrace.com:40051/wsNexus/api/Eventos/Post";
    if($GLOBALS['ignition4']==0){
        $data=[
            "eventos"=>[[
                "device_id"=>"863844051008354",            
                "event_time"=>$GLOBALS['UTC'],
                "lat"=>$GLOBALS['latitud4'],
                "lon"=>$GLOBALS['longitud4'],
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
                "device_id"=>"863844051008354",            
                "event_time"=>$GLOBALS['UTC'],
                "lat"=>$GLOBALS['latitud4'],
                "lon"=>$GLOBALS['longitud4'],
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


function enviarStatusMotorNx5(){
    $url="https://monitoreo.mastrace.com:40051/wsNexus/api/Eventos/Post";
    if($GLOBALS['ignition4']==0){
        $data=[
            "eventos"=>[[
                "device_id"=>"864893038348267",            
                "event_time"=>$GLOBALS['UTC'],
                "lat"=>$GLOBALS['latitud5'],
                "lon"=>$GLOBALS['longitud5'],
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
                "device_id"=>"864893038348267",            
                "event_time"=>$GLOBALS['UTC'],
                "lat"=>$GLOBALS['latitud5'],
                "lon"=>$GLOBALS['longitud5'],
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

function statusDispositivoNx2(){
    $url="https://monitoreo.mastrace.com:40051/wsNexus/api/Eventos/Post";
    if($GLOBALS['statusgps2']==0){
        $data=[
            "eventos"=>[[
                "device_id"=>"864893036178294",            
                "event_time"=>$GLOBALS['UTC'],
                "lat"=>$GLOBALS['latitud2'],
                "lon"=>$GLOBALS['longitud2'],
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
                "device_id"=>"864893036178294",            
                "event_time"=>$GLOBALS['UTC'],
                "lat"=>$GLOBALS['latitud2'],
                "lon"=>$GLOBALS['longitud2'],
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

function statusDispositivoNx3(){
    $url="https://monitoreo.mastrace.com:40051/wsNexus/api/Eventos/Post";
    if($GLOBALS['statusgps3']==0){
        $data=[
            "eventos"=>[[
                "device_id"=>"864893037414045",            
                "event_time"=>$GLOBALS['UTC'],
                "lat"=>$GLOBALS['latitud3'],
                "lon"=>$GLOBALS['longitud3'],
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
                "device_id"=>"864893037414045",            
                "event_time"=>$GLOBALS['UTC'],
                "lat"=>$GLOBALS['latitud3'],
                "lon"=>$GLOBALS['longitud3'],
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

function statusDispositivoNx4(){
    $url="https://monitoreo.mastrace.com:40051/wsNexus/api/Eventos/Post";
    if($GLOBALS['statusgps4']==0){
        $data=[
            "eventos"=>[[
                "device_id"=>"863844051008354",            
                "event_time"=>$GLOBALS['UTC'],
                "lat"=>$GLOBALS['latitud4'],
                "lon"=>$GLOBALS['longitud4'],
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
                "device_id"=>"863844051008354",            
                "event_time"=>$GLOBALS['UTC'],
                "lat"=>$GLOBALS['latitud4'],
                "lon"=>$GLOBALS['longitud4'],
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


function statusDispositivoNx5(){
    $url="https://monitoreo.mastrace.com:40051/wsNexus/api/Eventos/Post";
    if($GLOBALS['statusgps4']==0){
        $data=[
            "eventos"=>[[
                "device_id"=>"864893038348267",            
                "event_time"=>$GLOBALS['UTC'],
                "lat"=>$GLOBALS['latitud5'],
                "lon"=>$GLOBALS['longitud5'],
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
                "device_id"=>"864893038348267",            
                "event_time"=>$GLOBALS['UTC'],
                "lat"=>$GLOBALS['latitud5'],
                "lon"=>$GLOBALS['longitud5'],
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




obtenerEid();
obtenerIdUnidad();
obtenerPosicion();
statusDispositivo();
obtenerTokenNexus();
enviarPosNx();
enviarStatusMotorNx();
statusDispositivoNx();


obtenerIdUnidad2();
obtenerPosicion2();
statusDispositivo2();
statusMotor2();
enviarPosNx2();
enviarStatusMotorNx2();
statusDispositivoNx2();

obtenerIdUnidad3();
obtenerPosicion3();
statusDispositivo3();
statusMotor3();
enviarPosNx3();
enviarStatusMotorNx3();
statusDispositivoNx3();


obtenerIdUnidad4();
obtenerPosicion4();
statusDispositivo4();
statusMotor4();
enviarPosNx4();
enviarStatusMotorNx4();
statusDispositivoNx4();


obtenerIdUnidad5();
obtenerPosicion5();
statusDispositivo5();
statusMotor5();
enviarPosNx5();
enviarStatusMotorNx5();
statusDispositivoNx5();









?>