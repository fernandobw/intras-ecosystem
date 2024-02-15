<?php

include 'inc/sites.php';

function check_site_exists( $url, $sites ){

    if( array_key_exists( $url, $sites ) ){
        return $url;
    }else{

        if( str_contains( $url, 'dev.' ) ){

            $prod_url = str_replace('dev.', '', $url);

            return $prod_url;
        }
    }

    return false;
}

// Obtener los items segun el id de
function get_facilitator_items( $id ){

    $results;
    global $sites;

    $wp_sipgo_id;

    // En $id viene el id del post, ahora saco los autores o expertos
    $dominio_actual = $_SERVER['HTTP_HOST'];
    
    if( $dominio_actual ){

        $facilitadores = get_the_terms( $id , $sites[check_site_exists($dominio_actual, $sites)]['person_name']);

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
                'taxonomy' => $sites[check_site_exists($_SERVER['SERVER_NAME'], $sites)]['person_name'],
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

            header( 'Location: ' . get_home_url() . "/" . $sites[check_site_exists($_SERVER['SERVER_NAME'], $sites)]['person_name'] . "/" . $facilitator[0]->slug );
        }
    }

    return $template;
}
add_filter('template_include', 'facilitator_redirect_template');