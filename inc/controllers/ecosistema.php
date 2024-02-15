<?php

class Ecosistema_Controller{

    public static function get_sites(){

        include '../sites.php';

        return $sites;
    }

    static function api_call( $id ){

        $curl = curl_init();

        curl_setopt_array($curl, array(
          CURLOPT_URL => 'https://api.sipgoerp.com/v1/facilitators_support_materials/?sort=published_date%7Cdesc&page=1&limit=50&q=(facilitators_id:'. $id .')',
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => '',
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 0,
          CURLOPT_FOLLOWLOCATION => true,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => 'GET',
          CURLOPT_HTTPHEADER => array(
            'token: d43f55c4-b90a-4a98-bf2d-0b765d2fe138',
            'Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJleHAiOjE1NzU5MjgxMzgsImlzcyI6InBoYWxjb24tand0LWF1dGgiLCJkaXNwbGF5bmFtZSI6ImRjaGF2ZXoiLCJ1c2VyX2lkIjoiNjciLCJpYXQiOjE1NzUzMjMzMzh9.otErL3G8DCx0bqSvblhTfJSvbbyxocE_sPc6m6pcSik',
            'Cookie: PHPSESSID=255f1c04e535aebc0cabd6e6b821051d'
          ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);

        return json_decode( $response );
    }

    public static function hola() {
        return "Hola";
    }

    public static function get_facilitator($data) {

        $facilitator_id = isset($_GET['facilitator_id']) ? $_GET['facilitator_id'] : null;
        $site_url = isset($_GET['site']) ? $_GET['site'] : null;

        if (!$facilitator_id || !$site_url) {
            return; // Retorna si falta algún parámetro
        }

        // Definir información de los sitios
        $sites = self::get_sites();

        // Llamada a la API para obtener información del facilitador
        $facilitator_info = self::api_call($facilitator_id);
        $results = array();

        // Procesar la información de los facilitadores
        foreach ($facilitator_info->data as $item) {

            foreach ($sites as $site) {

                if ($site['active'] && str_contains($item->url, self::check_site_exists($site['link'], $sites)) && !str_contains($item->url, check_site_exists($site_url, $sites) ) && !$site['onlist']) {

                    $result = array('title' => $site['title']);

                    if (isset($site['person_name'])) {
                        $result['link'] = $site['link'] . "/redirect/facilitator/{$facilitator_id}?facilitator_id=" . $facilitator_id;
                    }

                    $results[] = $result;
                    $sites[$site['link']]['onlist'] = true;
                }
            }
        }

        // Agregar eventos de INTRAS si están activos
        if (isset($intras_events->data) && $sites['intras.com.do']['active']) {
            $intras_result = array('title' => 'INTRAS');
            if (isset($sites['intras.com.do']['person_name'])) {
                $intras_result['link'] = $sites['intras.com.do']['link'] . "/eventos?{$sites['intras.com.do']['person_name']}_id=" . $facilitator_id;
            }
            array_unshift($results, $intras_result);
        }

        return rest_ensure_response($results);
    }


    public static function check_intras_events( $facilitator_id ){

        $curl = curl_init();

        curl_setopt_array($curl, array(
          CURLOPT_URL => 'https://api.sipgoerp.com/v1/events_versions_facilitators?sort=id%7Cdesc&page=1&limit=50&q=(facilitators_id%3A'. $facilitator_id . ')&agencies_id=1',
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => '',
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 0,
          CURLOPT_FOLLOWLOCATION => true,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => 'GET',
          CURLOPT_HTTPHEADER => array(
            'token: d43f55c4-b90a-4a98-bf2d-0b765d2fe138',
            'Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJleHAiOjE1NzU5MjgxMzgsImlzcyI6InBoYWxjb24tand0LWF1dGgiLCJkaXNwbGF5bmFtZSI6ImRjaGF2ZXoiLCJ1c2VyX2lkIjoiNjciLCJpYXQiOjE1NzUzMjMzMzh9.otErL3G8DCx0bqSvblhTfJSvbbyxocE_sPc6m6pcSik',
            'Cookie: PHPSESSID=5c7375f3ee82ef3b520a8d47ae6681ee'
          ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);

        $data = json_decode( $response );
        return $data;
    }

    public static function get_intras_links( $id ){

        $current_site = str_replace( 'https://', '', get_home_url() );

        $result = file_get_contents( 'https://intras.com.do/wp-json/intras/ecosystem?facilitator_id='. $id .'&site=' . $current_site );

        return $result;
    }

    // Inicialiar campo
    public static function init() {

        $dominio_actual = $_SERVER['HTTP_HOST'];

        if( $dominio_actual == 'temasdevanguardia.com' ||  $dominio_actual == 'dev.temasdevanguardia.com' ) {

            add_action('experto_edit_form_fields', array('Ecosistema_Controller', 'sipgo_id_field') );
            add_action('edited_experto', array('Ecosistema_Controller', 'intras_guardar_campo_personalizado_taxonomy'));

        }elseif( $dominio_actual == 'gestion.com.do' ) {

            add_action('autor_edit_form_fields', array('Ecosistema_Controller', 'sipgo_id_field') );
            add_action('edited_autor', array('Ecosistema_Controller', 'intras_guardar_campo_personalizado_taxonomy'));
        }
       
    }

    static function sipgo_id_field( $term ){

        $sipgo_id = get_term_meta($term->term_id, 'sipgo_id', true);
        ?>
        <tr class="form-field">
            <th scope="row" valign="top"><label for="sipgo_id">ID de Sipgo:</label></th>
            <td>
                <input type="text" name="sipgo_id" id="sipgo_id" value="<?php echo esc_attr($sipgo_id); ?>">

                <?php if( $_SERVER['HTTP_HOST'] == 'gestion.com.do' ): ?>
                    <p class="description">Ingrese el ID de Sipgo para este autor.</p>
                <?php elseif( $_SERVER['HTTP_HOST'] == 'temasdevanguardia.com' ): ?>
                    <p class="description">Ingrese el ID de Sipgo para este experto.</p>
                <?php endif; ?>
            </td>
        </tr>
        <?php
    }

    // Guardar el valor del campo personalizado al actualizar término de la taxonomía "autor"
    public static function intras_guardar_campo_personalizado_taxonomy($term_id) {
        if (isset($_POST['sipgo_id'])) {
            $sipgo_id = sanitize_text_field($_POST['sipgo_id']);
            update_term_meta($term_id, 'sipgo_id', $sipgo_id);
        }
    }

    public static function check_site_exists($site_link, $sites) {

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
}

Ecosistema_Controller::init();