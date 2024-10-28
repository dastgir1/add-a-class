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
 * TODO describe file form
 *
 * @package    block_teachertiming
 * @copyright  2023 YOUR NAME <your@email.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require('../../config.php');

require_once($CFG->libdir.'/formslib.php');
// require_once($CFG->libdir.'/adminlib.php');
// require_once($CFG->dirroot.'/mod/page/locallib.php');
require_once($CFG->dirroot.'/group/lib.php');
require_once $CFG->dirroot . '/course/lib.php';
require_once $CFG->dirroot . '/course/modlib.php';
require_once($CFG->dirroot.'/mod/bigbluebuttonbn/locallib.php');
global $CFG, $DB, $USER;
require_login();
$url = new moodle_url('/blocks/teachertiming/formedit.php', []);
$PAGE->set_pagelayout('admin');
$PAGE->set_url($url);
$PAGE->set_context(context_system::instance());

$PAGE->set_heading($SITE->fullname);
/**
 * [Description formedit]
 */
class formedit extends moodleform {
    /**
     * [Description for definition]
     *
     * @return [type]
     * 
     */
    function definition() {
        global $USER, $DB, $OUTPUT ;
        $mform = $this->_form;
 
            $cid = required_param('id', PARAM_INT);
            
          /* TEXTBOX (HIDDEN)
		   courseid
		   
		 */
		    $mform->addElement('hidden', 'id');
            $mform->setType('id', PARAM_INT);		
		    $mform->setDefault('id', $cid );

          /*  TIME SELECTOR (ENABLE OPTION)
		   start time of class
         */
          for ($i = 0; $i <= 23; $i++) {
            $hours[$i] =  sprintf("%02d", $i) ;
          }
         for ($i = 0; $i < 60; $i++) {
            $minutes[$i] ="   " .  sprintf("%02d", $i);
         }
        
          $stimearray=array();
          $stimearray[]=& $mform->createElement('select', 'shours', '', $hours);
          $stimearray[]=& $mform->createElement('select', 'sminutes', '', $minutes);
          $mform->addGroup( $stimearray,'timearr',' Start Time' ,' ',false);
          $mform->setDefault('starttime', 0);
          $mform->addHelpButton('starttime', 'starttime', 'block_teachertiming');

		/*  TIME SELECTOR (ENABLE OPTION)
		   end time of class
         */
          $etimearray=array();
          $etimearray[]=& $mform->createElement('select', 'ehours', '', $hours);
          $etimearray[]=& $mform->createElement('select', 'eminutes', '', $minutes);
          $mform->addGroup( $etimearray,'timearr',' End Time' ,' ',false);
          $mform->setDefault('endtime', 0);
          $mform->addHelpButton('endtime', 'endtime', 'block_teachertiming');
        // Action buttons.
        $buttonarray = array();
        $buttonarray[] = $mform->createElement('submit', 'submitbutton', get_string('submit', 'block_teachertiming'));
        $buttonarray[] = $mform->createElement('cancel');
        $mform->addGroup($buttonarray, 'buttonar', '', '', false);
    }
	/**
     *
     * The data_preprocessing() function defines the html editor data.
     *
     * @param My_Type $defaultvalues
     */
    public function data_preprocessing($defaultvalues) {
        if ($this->current->instance) {
            $draftitemid = file_get_submitted_draft_itemid('page');
            $defaultvalues['page']['format'] = $defaultvalues['contentformat'];
            $defaultvalues['page']['text']   = file_prepare_draft_area($draftitemid, $context->id,
                                                'blocks_teachertiming', 'content', 0,
                                                page_get_editor_options($context), $defaultvalues['content']);
            $defaultvalues['page']['itemid'] = $draftitemid;
        }
        if (!empty($defaultvalues['displayoptions'])) {
            $displayoptions = unserialize($defaultvalues['displayoptions']);
            if (isset($displayoptions['printintro'])) {
                $defaultvalues['printintro'] = $displayoptions['printintro'];
            }
            if (isset($displayoptions['printheading'])) {
                $defaultvalues['printheading'] = $displayoptions['printheading'];
            }
            if (!empty($displayoptions['popupwidth'])) {
                $defaultvalues['popupwidth'] = $displayoptions['popupwidth'];
            }
            if (!empty($displayoptions['popupheight'])) {
                $defaultvalues['popupheight'] = $displayoptions['popupheight'];
            }
        }
    }
}

//form handling
$mform = new formedit('', array('id' => $id));

if ($mform->is_cancelled()) {
   // getting data from form
} else if ($data = $mform->get_data()) {
	 
	$cid=$data->id;
  
    $sth = $data->shours;  
    $stm =$data->sminutes;
   
    $eth = $data->ehours;  
    $etm=$data->eminutes;
    $st= $sth . ':' . $stm ;
    $et= $eth . ':' . $etm ;
    // group name
    $gname =  $st . ' to ' . $et;
    // class name
    $cname =  $st . ' to ' . $et;

    $sts=($sth*60+$stm)*60;
    $ets=($eth*60+$etm)*60;
    // Create a new group
    $groupData = new stdClass();
    $groupData->courseid = $cid; 
    $groupData->name = $gname;
    $groupData->description = 'Group Description';
    $group = groups_create_group($groupData);   
    if ($group) {
         echo "Group created successfully!";
            //  notice("Group created successfully!.", new moodle_url('/course/view.php', array('id' => $cid))) ;
    } else {
            echo "Error creating group.";
    }
         // creating a class bigbluebutton activity
    $course=get_course($cid);
    $encodedseed = bigbluebuttonbn_unique_meetingid_seed();
    //  Set the meetingid column in the bigbluebuttonbn table.
    $section=1;
    $moduleinfo = new stdClass();
    $moduleinfo->section = $section;
    $moduleinfo->module = $DB->get_field('modules','id',array('name' => 'bigbluebuttonbn'));
    $moduleinfo->modulename = 'bigbluebuttonbn';
    $moduleinfo->visible = 1;
    $moduleinfo->visibleold = 1;
    $moduleinfo->visibleoncoursepage = 1;
    $moduleinfo->type=0;
    $moduleinfo->course = $course;
    $moduleinfo->name = 'Class Room';
    $moduleinfo->intro = 'Click  classroom to join with your teacher to take your class.';
    $moduleinfo->introformat = 1;
    $moduleinfo->meetingid =  $encodedseed;
    $moduleinfo->moderatorpass = 0;
    $moduleinfo->viewerpass = 0;
    $moduleinfo->wait = 1;
    $moduleinfo->record = 1;
    $moduleinfo->recordallfromstart = 0;
    $moduleinfo->recordhidebutton = 0;
    $moduleinfo->welcome = ' Assalam o Alaikum dear student';
    $moduleinfo->voicebridge = 0;
    $moduleinfo->openingtime =  0;
    $moduleinfo->closingtime = 0;
    $moduleinfo->timecreated = time();
    $moduleinfo->timemodified = 0;
    $moduleinfo->participants= 0;
    $moduleinfo->userlimit= 0;
    $moduleinfo->recordings_html = 0;
    $moduleinfo->recordings_deleted = 1;
    $moduleinfo->recordings_imported = 0;
    $moduleinfo->recordings_preview = 1;
    $moduleinfo->clienttype = 0;
    $moduleinfo->muteonstart = 0;
    $moduleinfo->disablecam = 0;
    $moduleinfo->disablemic = 0;
    $moduleinfo->disableprivatechate = 0;
    $moduleinfo->disablenote = 0;
    $moduleinfo->hideuserlist = 0;
    $moduleinfo->lockedlayout = 0;
    $moduleinfo->lockonjoin = 0;
    $moduleinfo->lockonjoinconfigurable = 0;
    $moduleinfo->completionattendance = 0;
    $moduleinfo->completionengagementchats = 0;
    $moduleinfo->completionengagementtalks = 0;
    $moduleinfo->completionengagementraisehand = 0;
    $moduleinfo->completionengagementpollvotes = 0;
    $moduleinfo->completionengagementemojis = 0;
    $moduleinfo->availability = '{"op":"&","c":[{"type":"group","id": '.$group.'},{"type":"time","from": '.$sts.',"to":'.$ets.'}],"showc":[true,true]}';

    $moduleinfo = add_moduleinfo($moduleinfo, $course);
    list($module, $context, $cw, $cm, $data) = prepare_new_moduleinfo_data($course, $moduleinfo->modulename, $section);
      
} else {
    // this branch is executed if the form is submitted but the data doesn't validate and the form should be redisplayed
    // or on the first display of the form.
    
    //Set default data (if any)
    $mform->set_data($toform);
           
} 
echo $OUTPUT->header();

$mform->display();
          
echo $OUTPUT->footer();
