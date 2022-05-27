<?php
    $capabilities = array(

        'report/coursestatistic:viewweights' => array(
            'captype' => 'read',
            'contextlevel' => CONTEXT_SYSTEM,
            'archetypes' => array(
                'guest' => CAP_PROHIBIT,
                'student' => CAP_PROHIBIT,
                'teacher' => CAP_ALLOW,
                'editingteacher' => CAP_ALLOW,
                'coursecreator' => CAP_ALLOW,
                'admin' => CAP_ALLOW,
            ),
        ),

        'report/coursestatistic:editweights' => array(
            'captype' => 'write',
            'contextlevel' => CONTEXT_SYSTEM,
            'archetypes' => array(
                'guest' => CAP_PROHIBIT,
                'student' => CAP_PROHIBIT,
                'teacher' => CAP_PROHIBIT,
                'editingteacher' => CAP_PROHIBIT,
                'coursecreator' => CAP_PROHIBIT,
                'admin' => CAP_ALLOW,
            ),
        ),

        'report/coursestatistic:startcalculation' => array(
            'captype' => 'write',
            'contextlevel' => CONTEXT_SYSTEM,
            'archetypes' => array(
                'guest' => CAP_PROHIBIT,
                'student' => CAP_PROHIBIT,
                'teacher' => CAP_PROHIBIT,
                'editingteacher' => CAP_PROHIBIT,
                'coursecreator' => CAP_PROHIBIT,
                'admin' => CAP_ALLOW,
            ),
        ),

        'report/coursestatistic:getcoursereport' => array(
            'captype' => 'read',
            'contextlevel' => CONTEXT_COURSE,
            'archetypes' => array(
                'guest' => CAP_PROHIBIT,
                'student' => CAP_PROHIBIT,
                'teacher' => CAP_ALLOW,
                'editingteacher' => CAP_ALLOW,
                'coursecreator' => CAP_ALLOW,
                'admin' => CAP_ALLOW,
            )
        )

    );
