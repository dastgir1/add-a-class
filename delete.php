<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * TODO describe file delete
 *
 * @package    block_teachertiming
 * @copyright  2023 YOUR NAME <your@email.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require('../../config.php');
require_once($CFG->dirroot .'\mod\bigbluebuttonbn\lib.php');

$url = new moodle_url('/blocks/teachertiming/delete.php', []);
$PAGE->set_url($url);
$PAGE->set_context(context_system::instance());

$PAGE->set_heading($SITE->fullname);
echo $OUTPUT->header();

global $DB;
//getting id's
$cmid = required_param('id', PARAM_INT);
$gid = required_param('gid', PARAM_INT);
$cid = required_param('cid', PARAM_INT);

//Query for checking if any student active in a group
 
 $result =$DB->get_records_sql("SELECT gm.userid FROM {groups_members} gm WHERE groupid = $gid");
//make a condition for checking any user have active in a group
if (!empty($result)) {
notice("This class has active students and cannot be deleted.", 
new moodle_url('/course/view.php', array('id' => $cid))) ;
}else{

    //delete a group if group is empty
   
    if ($DB->execute("DELETE FROM {groups} g WHERE g.id=$gid") === TRUE) {
        echo "Slected group deleted successfully";
    } else {
        echo "Error deleting record: " ;
    }

    // for  deleting class  
    try {
        course_delete_module($cmid);
    } catch (\Exception $e) {
        throw new \coding_exception("The course module {$cmid} could not be deleted. "
            . "{$e->getMessage()}: {$e->getFile()}({$e->getLine()}) {$e->getTraceAsString()}");
        echo 'Slected class deleted successfully';
    }
}

echo $OUTPUT->footer();
