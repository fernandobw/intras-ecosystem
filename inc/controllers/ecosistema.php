<?php

class Ecosistema_Controller{

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

    public static function get_facilitator( $data ){

        if( isset( $_GET['facilitator_id'] ) ){

            $facilitator_id = $_GET['facilitator_id'];
            $site_url = $_GET['site'];
            $intras_events = self::check_intras_events( $facilitator_id );

            // Esto es porque dependiendo del sitio se le tiene un nombre a la persona (autor, experto, etc)
            $sites = array(
                'intras.com.do'=>array(
                    'title'=>'INTRAS',
                    'link'=>'intras.com.do',
                    'person_name'=>'expert',
                    'onlist'=>false,
                    'active'=>false
                ),
                'temasdevanguardia.com'=>array(
                    'title'=>'Temas de Vanguardia',
                    'link'=>'temasdevanguardia.com',
                    'person_name'=>'expert',
                    'onlist'=>false,
                    'active'=>true
                ),
                'managementupdate.com.do'=>array(
                    'title'=>'Management Update',
                    'link'=>'managementupdate.com.do',
                    'person_name'=>'autor',
                    'onlist'=>false,
                    'active'=>false
                ),
                'gestion.com.do'=>array(
                    'title'=>'Gestion',
                    'link'=>'gestion.com.do',
                    'person_name'=>'autor',
                    'onlist'=>false,
                    'active'=>true
                ),
                'intrasbookstore.com'=>array(
                    'title'=>'INTRAS Bookstore',
                    'link'=>'intrasbookstore.com',
                    'onlist'=>false,
                    'active'=>false
                )
            );

            $facilitator_info = self::api_call( $facilitator_id );

            foreach( $facilitator_info->data as $item ){

                foreach( $sites as $site ){

                    if( $site['active'] ){

                        if( str_contains($item->url, $site['link'] ) && !str_contains($item->url, $site_url) && !$site['onlist'] ){

                            $result = array(
                                'title'=>$site['title']
                            );

                            if( isset( $site['person_name'] ) ){

                                $result['link'] = $site['link'] . "/redirect/facilitator/{$facilitator_id}?facilitator_id=" . $facilitator_id;
                            }

                            $results[] = $result;

                            $sites[$site['link']]['onlist'] = true;
                        }
                    }
                }
            }

            // INTRAS events
            if( isset( $intras_events->data ) && $sites['intras.com.do']['active'] ){

                $intras_result = array(
                    'title'=>'INTRAS'
                );

                if( isset( $sites['intras.com.do']['person_name'] ) ){

                    $intras_result['link'] = $sites['intras.com.do']['link'] . "/eventos?{$sites['intras.com.do']['person_name']}_id=" . $facilitator_id;
                }

                array_unshift($results , $intras_result);
            }

            return rest_ensure_response( $results );
        }
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

        if( $dominio_actual == 'temasdevanguardia.com' ) {

            add_action('experto_edit_form_fields', array('Ecosistema_Controller', 'sipgo_id_field') );
            add_action('edited_experto', array('Ecosistema_Controller', 'intras_guardar_campo_personalizado_taxonomy'));

        }elseif($dominio_actual == 'gestion.com.do') {

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
}

Ecosistema_Controller::init();