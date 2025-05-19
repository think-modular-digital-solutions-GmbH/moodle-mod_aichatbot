<?php
function xmldb_aichatbot_upgrade($oldversion) {
    global $DB;

    $dbman = $DB->get_manager();


    if ($oldversion < 2025051503) {

        // Define field completionattemptscount to be added to aichatbot.
        $table = new xmldb_table('aichatbot');
        $field = new xmldb_field('completionattemptscount', XMLDB_TYPE_INTEGER, '2', null, null, null, null, 'completionattempts');

        // Conditionally launch add field completionattemptscount.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Aichatbot savepoint reached.
        upgrade_mod_savepoint(true, 2025051503, 'aichatbot');
    }


    return true;
}