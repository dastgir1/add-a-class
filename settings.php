<?php
/*  DOCUMENTATION
    .............

    settings.php defines the additional settings for your plugin.

    You are adding a heading or a text setting, you need to know what are the arguments, the constructor
    admin_setting_configtext would take.
    Syntax: public function __construct($name, $visiblename, $description, $defaultsetting, $paramtype, $size=null).

    $name is a 'block_slack_heading',
    $visiblename is get_string('settings_heading', 'block_teachertiming'), a string heading for your settings,
    $description is get_string('settings_content', 'block_teachertiming'), which is another string, this is a shorttext displayed
        underneath the setting to explain it further,
    $defaultsetting is the default value for this setting. This value is used when Moodle is installed,
    $paramtype is the type of the input text. The inserted value will be sanitised according to the declared type,
    $size allows to specify custom size of the field in the user interface.
*/
/**
 * Version information for techertiming
 *
 * @package    block_teachertiming
 * @copyright  2023 YOUR NAME <your@email.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
$settings->add(new admin_setting_heading('block_teachertiming_heading', 
                                          get_string('settings_heading', 'block_teachertiming'),
                                          get_string('settings_content', 'block_teachertiming')));

$settings->add(new admin_setting_configtext('block_teachertiming/Label',
                                             get_string('label', 'block_teachertiming'),
                                             get_string('label_desc', 'block_teachertiming'), '', PARAM_TEXT));
