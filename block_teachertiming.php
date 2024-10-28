<?php
/*  DOCUMENTATION
    .............

    The actual display of your block is block_slack.php

    init() method is essential part to pass the class variables:
    $this->title: to display the title in the header of your block.
    $this->version (optional unless you need Moodle to perform automatic updates) and there is no return value to be expected
    from init().

    $CFG stands for Configuration. CFG is a global variable can be used in any moodle page, contains Moodle's
    root, data(moodledata) and database configuration settings and other config values.

    get_string converts an array of string names to localised strings for a specific plugin. It looks formal when you code
    with language strings instead of manual text. It's a good habit of writing manual text to strings.

    has_config() method states that the block has a settings.php file. This method specifies whether your block wants to
    present additional configuration settings.

    get_content method should define $this->content variable of your block.
    If $this->content_type is BLOCK_TYPE_TEXT, then $this->content is expected to have the following member variables:
    text - a string of arbitrary length and content displayed inside the main area of the block, and can contain HTML.
    footer - a string of arbitrary length and content displayed below the text, using a smaller font size.
    It can also contain HTML.

    instance_allow_multiple() method indicates whether you want to allow multiple block instances in the same page or not.
    If you do allow multiple instances, it is assumed that you will also be providing per-instance configuration for the
    block.

*/

// Class name must be named exactly the block folder name.



/**
 * TODO describe file block_teachertiming
 *
 * @package    block_teachertiming
 * @copyright  2023 YOUR NAME <your@email.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class block_teachertiming extends block_base {
    /**
     * [Description for init]
     *
     * @return [type]
     * 
     */
    function init() {
        $this->title = get_string('teachertiming', 'block_teachertiming'); // (Title of your block).
    }
    /**
     * [Description for has_config]
     *
     * @return [type]
     * 
     */
    function has_config() {
        return true;
    }

    /**
     * [Description for get_content]
     *
     * @return [type]
     * 
     */
    function get_content() {
        global $DB,$CFG;

        if ($this->content !== null) {
            return $this->content;
        }

        // Get course id.
        $cid = required_param('id', PARAM_INT);

        // Make a button for creating a class.
        $button="<button type='button' class='btn btn-primary'><a href='$CFG->wwwroot/blocks/teachertiming/formedit.php?id=$cid'  
        style='color:white;'>Creat a Class</a></button>";
        // Query for getting id's.
        $result = $DB->get_records_sql(
            "SELECT cm.id,cm.availability 
               FROM {course_modules} cm 
               JOIN {modules} m ON m.id = cm.module
               JOIN {bigbluebuttonbn} bbb ON bbb.id = cm.instance
              WHERE m.name like 'bigbluebuttonbn'
                    AND cm.course =  $cid"
        );
        // $rid=$DB->get_field_select('enrol','roleid',"courseid=$cid");
        $this->content = new stdClass;
        $this->content->text = '';

        // Add a html table in a block.
        $table = new html_table();
        $table->head = ['Sr No', 'Class Name', 'Delete class'];

        foreach ($result as $row) {
            $cmid= $row->id ;

            // Make an object from a string.
            $availability = json_decode($row->availability);
        
            // Getting group id from avialability column.
            foreach ($availability->c as $condition) {
                if ($condition->type == 'group') {
                    $gid = $condition->id;
                    // Query for getting group name.
                    $gname = $DB->get_field_select('groups', 'name', "id = $gid");

                }
            }

            $button2 = "<a href='$CFG->wwwroot/blocks/teachertiming/delete.php?id=$cmid&gid=$gid&cid=$cid'><button type='button' class='close ' aria-label='Close'>
            <i class='fa fa-times' aria-hidden='true'></i></button></a>";
        }

        // Code for auto encrement of serial no.
        for ($i = 1; $i <= count($result); $i++) {
            $table->data[] = [$i, $gname, $button2];
        }

        $this->content->text .= html_writer::table($table);

        // Add a button in block.
        // is (!isadmin()) { $this->content = ""; }
        $this->content->text .= $button;
        return $this->content;
    }
    // Create multiple instances on a page.
    /**
     * [Description for instance_allow_multiple]
     *
     * @return [type]
     * 
     */
    public function instance_allow_multiple() {
        return true;
    }
}
