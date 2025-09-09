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
 * @package    mod_aichatbot
 * @copyright  2025 think modular <support@think-modular.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
$string['pluginname'] = 'AI Chatbot';
$string['modulename'] = 'AI Chatbot';
$string['modulenameplural'] = 'AI Chatbots';
$string['pluginadministration'] = 'AI Chatbot Administration';

$string['howcanihelpyou'] = 'Hello! How can I help you?';
$string['prompttext'] = 'System prompt';
$string['channel'] = 'Channel';
$string['channel_help'] = 'Select the channel to use for this chatbot. The channel is where the conversation will be sent to.';
$string['nochannels'] = 'No channels available';
$string['attempts'] = 'Number of attempts';
$string['attempts_help'] = 'Number of attempts allowed for this chatbot. The maximum number of attempts is set in the admin settings.';
$string['err_maxattempts'] = 'The maximum number of attempts is {$a}';
$string['err_numeric'] = 'Please enter a valid number';
$string['interactions'] = 'Number of interactions';
$string['interactions_help'] = 'Number of interactions allowed for this chatbot. The maximum number of interactions is set in the admin settings.';
$string['err_maxinteractions'] = 'The maximum number of interactions is {$a}';
$string['completionattemptsenabled'] = 'Finish attempts';
$string['completionattempts'] = 'Number of attempts required';
$string['completionattemptscount'] = 'Number of attempts';
$string['completionattemptscount_help'] = 'The number of attempts required to complete this activity.';
$string['completionshare'] = 'Share one attempt';
$string['noattemptsremaining'] = 'You have no attempts remaining for this chatbot.';

$string['maxattempts'] = 'Maximum attempts';
$string['maxattempts_desc'] = 'Maximum number of attempts allowed for this chatbot.';
$string['maxinteractions'] = 'Maximum interactions';
$string['maxinteractions_desc'] = 'Maximum number of interactions allowed for this chatbot.';

$string['remainingattempts'] = 'Remaining attempts:';
$string['typeyourmessage'] = 'Type your message here...';
$string['send'] = 'Send';
$string['finishbutton'] = 'Mark dialog as finished';
$string['managedialogs'] = 'Manage dialogs';

$string['confirmation'] = 'Confirmation';
$string['confirmationtext'] = 'Are you sure you want to finish this conversation?';
$string['confirmationyes'] = 'Confirm';
$string['closemodal'] = 'Close';

$string['shareconfirmation'] = 'Share confirmation';
$string['shareconfirmationtext'] = 'Are you sure you want to share this conversation with your teacher?';

$string['attemptsheader'] = 'Attempts';
$string['attempt'] = 'Attempt';
$string['actions'] = 'Actions';
$string['comment'] = 'Comment';
$string['viewall'] = "View all public dialogs";
$string['newdialog'] = 'Start a new dialog ({$a} available)';

$string['noaccess'] = 'This resource is not available to you.';
$string['sharenotavailbe'] = 'Sharing is limited to one dialog. Share buttons will be re-enabled once the teacher revokes the current share.';
$string['alreadyshared'] = 'You have already shared a dialog. Please wait for the teacher to revoke the current share.';
$string['warningfinished'] = 'Maximum amount of interactions reached, dialog has been marked as finished.';

$string['initalsdropdown'] = 'Filter by name';
$string['firstnamelastname'] = 'First name / Last name';
$string['email'] = 'Email address';
$string['status'] = 'Status';
$string['submitted'] = 'Submitted';
$string['comments'] = 'Comments';
$string['attemptshared'] = 'Attempt shared';
$string['nosubmission'] = 'No submission';

$string['revokeconfirmation'] = 'Revoke confirmation';
$string['revokeconfirmationtext'] = 'Are you sure you want to revoke the sharing of this conversation?';

$string['writecomment'] = 'Write a comment';

$string['completiondetail:attempts'] = 'Finish {$a->attempts} attempt(s)';
$string['completiondetail:share'] = 'Share one attempt';

$string['submittedby'] = 'Dialog submitted by ';

$string['sharedsuccess'] = 'The dialog has been successfully shared with your teacher!';
$string['publicsuccess'] = 'The dialog has been successfully made public!';
$string['privatesuccess'] = 'The dialog has been successfully made private!';
$string['commentupdated'] = 'The comment has been successfully updated!';

$string['publicdialogs'] = 'Public dialogs';
$string['backtoactivity'] = 'Back to the activity';

$string['preview'] = 'preview';
$string['download'] = 'download';
$string['share'] = 'share w/teacher';
$string['makepublic'] = 'make public';
$string['revoke'] = 'revoke';

$string['studentsection'] = 'Your conversations';
$string['teachersection'] = 'Conversations from students';
$string['manage_dialogs'] = 'Manage dialogs';