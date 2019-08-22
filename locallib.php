<?php namespace need_to_check_lib;

function is_user_have_manager_role_in_course()
{
    global $USER;

    $courses = get_user_courses($USER->id);
    $archetypeRoles = get_archetypes_roles(array('manager'));

    foreach($courses as $courseid)
    {
        $userRoles = get_user_roles(\context_course::instance($courseid), $USER->id);

        if(is_user_have_role($archetypeRoles, $userRoles)) return true;
    }

    return false;
}

function get_user_courses(int $userid)
{
    $courses = array();
    $courses = array_merge($courses, get_user_courses_from_groups($userid));
    $courses = array_merge($courses, get_user_courses_from_enrollments($userid));
    array_unique($courses);
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







