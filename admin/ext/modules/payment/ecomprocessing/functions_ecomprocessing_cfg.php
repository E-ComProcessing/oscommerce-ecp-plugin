<?php
/*
 * Copyright (C) 2018 E-ComProcessing Ltd.
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * @author      E-ComProcessing
 * @copyright   2018 E-ComProcessing Ltd.
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU General Public License, version 2 (GPL-2.0)
 */

/**
 * Call Payment Class Method
 * @param string $paymentMethod
 * @param string $classMethod
 * @return mixed|null
 */
function ecp_call_payment_class_method($paymentMethod, $classMethod)
{
    if (!class_exists($paymentMethod)) {
        return null;
    }

    $paymentMethodInstance = new $paymentMethod;

    if (!method_exists($paymentMethodInstance, $classMethod)) {
        return null;
    }

    return call_user_func(
        array(
            $paymentMethodInstance,
            $classMethod
        ),
        array()
    );
}

/**
 * Prints Multi Select HTML Bootstrap Control
 * @param array $attributes
 * @param array $select_array
 * @param string $key_value
 * @param string $key
 * @return string
 */
function ecp_zfg_select_drop_down_multiple_from_object($attributes, $paymentMethod, $classMethod, $key_value, $key = '')
{
    $select_array = ecp_call_payment_class_method($paymentMethod, $classMethod);

    return ecp_zfg_select_drop_down_multiple($attributes, $select_array, $key_value, $key);
}

/**
 * Prints Multi Select HTML Bootstrap Control
 * @param array $attributes
 * @param array $select_array
 * @param string $key_value
 * @param string $key
 * @return string
 */
function ecp_zfg_select_drop_down_multiple($attributes, $select_array, $key_value, $key = '')
{
    $hiddenFieldName = (tep_not_null($key)
        ? 'configuration[' . $key . ']'
        : 'configuration_value'
    );

    $selectFieldName = (tep_not_null($key)
                ? 'configuration[' . $key . '_TMP][]'
                : 'configuration_value'
    );


    return
        tep_draw_hidden_field($hiddenFieldName, $key_value) .
        ecp_zfg_draw_pull_down_menu(
            $selectFieldName,
            $select_array,
            $key_value,
            "class=\"form-control\" data-target=\"{$hiddenFieldName}\" multiple=\"multiple\"" .
                (is_array($attributes)
                    ? ecp_convert_attributes_array_to_html($attributes)
                    : ""
                ),
            (is_array($attributes) && in_array('required', $attributes))
        );
}

/**
 * Get Available Options for the Admin Zone Settings
 * @param string $zone_class_id
 * @param string $key
 * @return string
 */
function ecp_cfg_pull_down_zone_classes($zone_class_id, $key = '') {
    $name = (($key) ? 'configuration[' . $key . ']' : 'configuration_value');

    $zone_class_array = array(array('id' => '0', 'text' => TEXT_NONE));
    $zone_class_query = tep_db_query("select geo_zone_id, geo_zone_name from " . TABLE_GEO_ZONES . " order by geo_zone_name");
    while ($zone_class = tep_db_fetch_array($zone_class_query)) {
        $zone_class_array[] = array('id' => $zone_class['geo_zone_id'],
            'text' => $zone_class['geo_zone_name']);
    }

    return ecp_zfg_select_drop_down_single($zone_class_array, $name, $zone_class_id);
}

/**
 * Prints Orders Select HTML Bootstrap Control
 * @param string $order_status_id
 * @param string $key
 * @return string
 */
function ecp_zfg_pull_down_order_statuses($order_status_id, $key = '')
{
    $statuses_array = array(array('id' => '0', 'text' => TEXT_DEFAULT));
    $statuses_query = tep_db_query("select orders_status_id, orders_status_name
                              from " . TABLE_ORDERS_STATUS . "
                              where language_id = '" . (int)$_SESSION['languages_id'] . "'
                              order by orders_status_id");

    while ($statuses = tep_db_fetch_array($statuses_query)) {
        $statuses_array[] = array('id' => $statuses['orders_status_id'],
            'text' => $statuses['orders_status_name'] . ' [' . $statuses['orders_status_id'] . ']');
    }

    return ecp_zfg_select_drop_down_single($statuses_array, $order_status_id, $key);
}

/**
 * Prints Select HTML Bootstrap Control
 * @param array $paymentMethod
 * @param string $classMethod
 * @param string $key_value
 * @param string $key
 * @return string
 */
function ecp_zfg_select_drop_down_single_from_object($paymentMethod, $classMethod, $key_value, $key = '')
{
    $select_array = ecp_call_payment_class_method($paymentMethod, $classMethod);

    return ecp_zfg_select_drop_down_single($select_array, $key_value, $key);
}

/**
 * Prints Select HTML Bootstrap Control
 * @param array $select_array
 * @param string $key_value
 * @param string $key
 * @return string
 */
function ecp_zfg_select_drop_down_single($select_array, $key_value, $key = '')
{
    $name = ((tep_not_null($key)) ? 'configuration[' . $key . ']' : 'configuration_value');
    return ecp_zfg_draw_pull_down_menu($name, $select_array, $key_value, "class=\"form-control\"");
}

/**
 * Prints Common Select HTML Control
 * @param string $name
 * @param array $values
 * @param string $default
 * @param string $parameters
 * @param bool $required
 * @return string
 */
function ecp_zfg_draw_pull_down_menu($name, $values, $default = '', $parameters = '', $required = false)
{
    $field = '<div class="form-group"><select rel="dropdown" name="' . tep_output_string($name) . '"';

    if (tep_not_null($parameters)) {
        $field .= ' ' . $parameters;
    }

    $field .= '>' . "\n";

    if (empty($default) && isset($GLOBALS[$name]) && is_string($GLOBALS[$name])) {
        $default = stripslashes($GLOBALS[$name]);
    }

    if (!is_array($default)) {
        $default = array_map(
            'trim',
            explode(
                ",",
                $default
            )
        );
    }

    for ($i=0, $n=sizeof($values); $i<$n; $i++) {
        $field .= '<option value="' . tep_output_string($values[$i]['id']) . '"';
        if (in_array($values[$i]['id'], $default)) {
            $field .= ' selected="selected"';
        }

        $field .= '>' .
            tep_output_string(
                $values[$i]['text'],
                array(
                    '"' => '&quot;',
                    '\'' => '&#039;',
                    '<' => '&lt;',
                    '>' => '&gt;'
                )
            ) . '</option>' . "\n";
    }
    $field .= '</select></div>' . "\n";

    if ($required == true) {
        $field .= TEXT_FIELD_REQUIRED;
    }

    return $field;
}

/**
 * Prints Bootstrap Toggle Control
 * @param string $value
 * @param string $key
 * @return string
 */
function ecp_zfg_draw_toggle($value, $key)
{
    $name = ((tep_not_null($key)) ? 'configuration[' . $key . ']' : 'configuration_value');
    ob_start();
    ?>
    <div class="form-group toggle-container">
        <input type="hidden" name="<?php echo $name;?>" value="<?php echo $value;?>"/>
        <input type="checkbox" class="bootstrap-checkbox"
            <?php if (strtolower($value) == 'true') { ?>
                checked="checked"
            <?php } ?>
        />
    </div>
    <?php
    $content = ob_get_contents();
    ob_end_clean();
    return $content;
}

/**
 * Prints Bootstrap Input Text Control with jQuery Number Validations
 * @param array $attributes
 * @param string $value
 * @param string $key
 * @return string
 */
function ecp_zfg_draw_number_input($attributes, $value, $key)
{
    if (!is_array($attributes)) {
        $attributes = array();
    }

    $attributes['class'] = "form-number-input" . (isset($attributes['class']) ? " " . $attributes['class'] : '');

    return ecp_zfg_draw_input(
        $attributes,
        $value,
        $key
    );
}

/**
 * Prints Bootstrap Input Text Control
 * @param array $attributes
 * @param string $value
 * @param string $key
 * @return string
 */
function ecp_zfg_draw_input($attributes, $value, $key)
{
    $name = ((tep_not_null($key)) ? 'configuration[' . $key . ']' : 'configuration_value');
    $class = "form-control";

    if (!empty($attributes)) {
        $attributes['class'] = $class . (isset($attributes['class']) ? " " . $attributes['class'] : '');
    } else {
        $attributes = array(
            'class' => $class
        );
    }

    $attributes_html = ecp_convert_attributes_array_to_html($attributes);

    ob_start();
    ?>
    <div class="form-group">
        <input
            type="text" <?php echo ($attributes_html ?: '');?>
            name="<?php echo $name;?>"
            value="<?php echo $value;?>"
            placeholder="<?php echo ecp_get_module_setting_placeholder($key);?>"
        />
    <?php
    if (is_array($attributes) && in_array('required', $attributes)) {
        echo TEXT_FIELD_REQUIRED;
    }
    ?>
    </div>
    <?php
    $content = ob_get_contents();
    ob_end_clean();

    return $content;
}

/**
 * Prints Bootstrap Toggle Display Value
 * @param string $value
 * @return string
 */
function ecp_zfg_get_toggle_value($value)
{
    $value = (strtolower($value) == 'true');
    ob_start();
    ?>
    <div class="form-group">
        <span class="ecomprocessing-toggle <?php echo ($value ? "toggle-on" : "toggle-off");?>">
            <?php echo ($value ? "YES" : "NO");?>
        </span>
    </div>
    <?php
    $content = ob_get_contents();
    ob_end_clean();

    return $content;
}
