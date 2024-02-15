<?php

// Register the custom API route
add_action('rest_api_init', 'register_custom_api_route');

function register_custom_api_route() {

    register_rest_route('intras', '/ecosystem', array(
        'methods' => 'GET',
        'callback' => array('Ecosistema_Controller', 'get_facilitator'),
    ));

    // Get facilitator
    register_rest_route('intras', '/autores/(?P<id>\d+)', array(
        'methods' => 'GET',
        'callback' => array('Autores_Controller', 'get_facilitator'),
    ));
}