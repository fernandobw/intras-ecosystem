<?php

$sites = array(
        'intras.com.do'=>array(
        'title'=>'INTRAS',
        'link'=>'intras.com.do',
        'person_name'=>'experto',
        'onlist'=>false
    ),
    'temasdevanguardia.com'=>array(
        'title'=>'Temas de Vanguardia',
        'link'=>'temasdevanguardia.com',
        'person_name'=>'experto',
        'onlist'=>false
        ),
    'managementupdate.com.do'=>array(
        'title'=>'Management Update',
        'link'=>'managementupdate.com.do',
        'onlist'=>false
        ),
    'gestion.com.do'=>array(
        'title'=>'Gestion',
        'link'=>'gestion.com.do',
        'person_name'=>'autor',
        'onlist'=>false
        ),
    'intrasbookstore.com'=>array(
        'title'=>'INTRAS Bookstore',
        'link'=>'intrasbookstore.com',
        'onlist'=>false
        )
    );

// Obtener los items segun el id de
function get_facilitator_items( $id ){

    $results;
    global $sites;

    $wp_sipgo_id;

    // En $id viene el id del post, ahora saco los autores o expertos
    $dominio_actual = $_SERVER['HTTP_HOST'];
    
    if( $dominio_actual ){

        $facilitadores = get_the_terms( $id , $sites[$dominio_actual]['person_name']);

        if( $facilitadores ){

            $wp_sipgo_id = $facilitadores[0]->term_id;

            $term_sipgo_id = get_term_meta( $wp_sipgo_id, 'sipgo_id');

            if( isset( $term_sipgo_id[0] ) ){

                $results = Ecosistema_Controller::get_intras_links( $term_sipgo_id[0] );

                return json_decode( $results );
            }
        }

    }

    return false;
}

// Redireccion a experto

// Agrega una regla de reescritura personalizada
function custom_rewrite_rule() {
    add_rewrite_rule('^redirect/facilitator/(\d+)/?', 'index.php?facilitator_id=$matches[1]', 'top');
}
add_action('init', 'custom_rewrite_rule');

// Registra el parámetro de consulta personalizado
function custom_query_vars($query_vars){

    $query_vars[] = 'facilitator_id';
    return $query_vars;
    
}
add_filter('query_vars', 'custom_query_vars');

// Maneja la plantilla para la página personalizada
function facilitator_redirect_template($template, ) {
    
    $facilitator_id = get_query_var('facilitator_id');

    global $sites;
    
    // Verifica si $expert_id tiene un valor y luego imprímelo
    if ($facilitator_id) {

        $facilitator = get_terms(
            array(
                'taxonomy' => $sites[$_SERVER['SERVER_NAME']]['person_name'],
                'meta_query' => array(
                    array(
                        'key' => 'sipgo_id',
                        'value' => $facilitator_id,
                        'compare' => '=',
                        'type' => 'NUMERIC',
                    ),
                ),
            )
        );

        if( $facilitator ){

            header( 'Location: ' . get_home_url() . "/" . $sites[$_SERVER['HTTP_HOST']]['person_name'] . "/" . $facilitator[0]->slug );

        }
    }

    return $template;
}
add_filter('template_include', 'facilitator_redirect_template');