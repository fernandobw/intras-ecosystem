<?php

// Mostrar campo id

$dominio_actual = $_SERVER['HTTP_HOST'];

if ($dominio_actual == 'temasdevanguardia.com') {
    add_action('autor_edit_form_fields', array('Autores_Controller', 'sipgo_id_field') );
} elseif ($dominio_actual == 'gestion.com.do') {
    echo 'Estás en dominio2.com';
}

//add_action('edited_autor', array('Autores_Controller', 'intras_guardar_campo_personalizado_taxonomy'));

$sipgo_id = get_term_meta($term->term_id, 'sipgo_id', true);
        ?>
    <tr class="form-field">
        <th scope="row" valign="top"><label for="sipgo_id">ID de Sipgo:</label></th>
    	<td>
        	<input type="text" name="sipgo_id" id="sipgo_id" value="<?php echo esc_attr($sipgo_id); ?>">
            <p class="description">Ingrese el ID de Sipgo para este autor.</p>
        </td>
    </tr>
<?php

