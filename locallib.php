<?php namespace need_to_check_lib;

function is_user_have_role_in_course_module(array $archetypes)
{
    global $USER;

    $courses = get_user_courses($USER->id);
    $archetypeRoles = get_archetypes_roles($archetypes);

    foreach($courses as $courseid)
    {
        $courseModules = get_course_modules($courseid);

        foreach($courseModules as $courseModule)
        {
            $userRoles = get_user_roles(\context_module::instance($courseModule->id), $USER->id);
    
            if(is_user_have_role($archetypeRoles, $userRoles)) return true;
        }
    }

    return false;
}

function get_user_courses(int $userid)
{
    $courses = array();
    $courses = array_merge($courses, get_user_courses_from_groups($userid));
    $courses = array_merge($courses, get_user_courses_from_enrollments($userid));
    $courses = array_unique($courses);
    return $courses;
}

function get_user_courses_from_groups(int $userid)
{
    global $DB;
    $sql = 'SELECT DISTINCT g.courseid 
            FROM {groups} AS g, {groups_members} AS gm 
            WHERE gm.groupid = g.id AND gm.userid = ?';
    $conditions = array($userid);

    $queries = $DB->get_records_sql($sql, $conditions);

    return get_course_array_from_query($queries);
}

function get_user_courses_from_enrollments(int $userid)
{
    global $DB;
    $sql = 'SELECT DISTINCT e.courseid
            FROM {user_enrolments} AS ue, {enrol} AS e
            WHERE ue.enrolid = e.id AND ue.userid = ?';
    $conditions = array($userid);

    $queries = $DB->get_records_sql($sql, $conditions);

    return get_course_array_from_query($queries);
}

function get_course_array_from_query(array $queries)
{
    foreach($queries as $query)
    {
        $courses[] = $query->courseid;
    }
    return $courses;
}

function get_course_modules(int $courseid)
{
    global $DB;
    $sql = "SELECT cm.id
            FROM {course_modules} AS cm
            INNER JOIN {modules} AS m
            ON cm.module = m.id
            WHERE cm.course = ? AND cm.visible = 1 AND cm.deletioninprogress= 0
            AND m.name IN('assign', 'forum', 'quiz')";
    $conditions = array($courseid);
    return $DB->get_records_sql($sql, $conditions);
}

function get_archetypes_roles(array $archetypes)
{
    global $DB;

    $archetypesCount = count($archetypes);
    $sql = 'SELECT id FROM {role} WHERE archetype IN (';
    for($i = 0; $i < $archetypesCount; $i++)
    {
        $sql.= "'".$archetypes[$i]."'";

        if(($i+1) < $archetypesCount)
        {
            $sql.= ', ';
        }
    }

    $sql.= ')';

    return $DB->get_records_sql($sql);
}

function is_user_have_role(array $archetypeRoles, array $userRoles) : bool 
{
    foreach($userRoles as $userRole)
    {
        foreach($archetypeRoles as $archetypeRole)
        {
            if($userRole->roleid == $archetypeRole->id) return true;
        }
    }

    return false;
}

function get_course_module($grade)
{   
    $cmid = get_course_module_id($grade);
    return get_coursemodule_from_id($grade->itemmodule, $cmid, 0, false, MUST_EXIST);
}

function get_course_module_id($grade)
{
    $sql = "SELECT cm.id
            FROM {course_modules} AS cm
            INNER JOIN {modules} AS m
            ON cm.module=m.id
            WHERE cm.instance = ? AND m.name=?";
    $conditions = array($grade->iteminstance, $grade->itemmodule);
    global $DB;

    $query = $DB->get_record_sql($sql, $conditions);

    if(isset($query->id)) return $query->id;
    else return null;
}

function get_groups_members(array $groups) : array
{
    $members = array();
    foreach($groups as $group)
    {
        $members = array_merge($members, groups_get_members(reset($group), 'u.id'));
    }
    return $members;
}



