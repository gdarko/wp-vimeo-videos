<?php

/**
 * Class WP_DGV_Settings_Helper
 *
 * Note: The prefix dgv_ in the input is no longer needed and is removed during the sanitization.
 *
 * eg. If you call the methods ->get('dgv_setting_name') it translates to ->get('setting_name')
 *
 * @since 1.4.0
 */
class WP_DGV_Settings_Helper
{

    /**
     * The settings data.
     * @var array
     */
    private $data;

    /**
     * If data was found. This may tell us if this is a first time install.
     * @var bool
     */
    private $found;

    /**
     * Updates count
     * @var
     */
    private $updates_count = 0;

    /**
     * WP_DGV_Settings constructor.
     */
    public function __construct()
    {

        $this->data = get_option('dgv_settings');

        if ( ! is_array($this->data) ) {
            $this->data = array();
            $this->found = false;
        } else {
            $this->found = true;
        }

    }

    /**
     * All the settings
     *
     * @return array
     */
    public function all()
    {
        return $this->data;
    }

    /**
     * Update setting
     *
     * @param $key
     * @param $value
     */
    public function set($key, $value)
    {
        $key = $this->prepare_key($key);
        $value = $this->prepare_value($value);

        $this->data[$key] = $value;
        $this->updates_count++;
    }

    /**
     * Remove setting
     *
     * @param $key
     */
    public function remove($key)
    {
        $key = $this->prepare_key($key);
        if (isset($this->data[$key])) {
            unset($this->data[$key]);
            $this->updates_count++;
        }
    }

    /**
     * Retrieve single setting.
     *
     * @param $key
     * @param  null  $default
     *
     * @return mixed|null
     */
    public function get($key, $default = null)
    {
        $key = $this->prepare_key($key);

        return isset($this->data[$key]) ? $this->data[$key] : $default;
    }

    /**
     * Save settings
     */
    public function save()
    {
        update_option('dgv_settings', $this->data);
        $this->updates_count = 0;
    }

    /**
     * Sanitize key
     *
     * @param $key
     *
     * @return string|string[]
     */
    private function prepare_key($key)
    {
        $key = str_replace('dgv_', '', $key);

        return $key;
    }

    /**
     * Prepare Values
     *
     * @param $value
     *
     * @return array|string
     */
    public function prepare_value($value)
    {
        if ( ! is_array($value)) {
            $option_value = sanitize_text_field($value);
        } else {
            $option_value = $value;
        }

        return $option_value;
    }

    /**
     * Check if the settings require migration
     */
    public function requires_migration() {
        return ! $this->found;
    }

    /**
     * Updates count
     * @return int
     */
    public function updates_count() {
        return $this->updates_count;
    }
}