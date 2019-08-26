<?php

use need_to_check_lib as nlib;

abstract class ForumUnratedPostsGetter
{

    public function get_grades()
    {
        $forums = $this->get_graded_forums();

        if($this->is_rated_forums_exist($forums))
        {
            $grades = $this->get_unrated_posts($forums);
            $grades = $this->filter_out_all_non_student_posts($grades);
            $this->add_neccessary_grades_params($grades);
            
            return $grades;
        }
    }

    abstract protected function get_graded_forums();

    private function is_rated_forums_exist($forums) : bool 
    {
        if(count($forums)) return true;
        else return false;
    }

    private function get_unrated_posts($forums)
    {
        $posts = $this->get_forum_posts($forums);

        global $DB;
        $unratedPosts = array();
        foreach($posts as $post)
        {
            if(!$DB->record_exists('rating', array('itemid'=>$post->id)))
            {
                $unratedPosts[] = $post;
            }
        }

        return $unratedPosts;
    }

    private function get_forum_posts($forums) 
    {
        global $DB;
        $sql = "SELECT fp.id, fd.forum, fp.userid, fd.course as courseid, c.fullname as coursename, fp.modified as timefinish
                FROM {forum_posts} AS fp
                INNER JOIN {forum_discussions} AS fd
                ON fp.discussion = fd.id
                INNER JOIN {course} AS c
                ON fd.course = c.id";
        $sql.= $this->get_forums_posts_where_clause($forums);
        return $DB->get_records_sql($sql, array());
    }

    private function get_forums_posts_where_clause($forums) : string
    {
        $str = ' WHERE ';
        foreach($forums as $forum)
        {
            $str.= "( fd.forum={$forum->iteminstance} ";
            $str.= "AND fp.userid NOT IN (SELECT gg.userid FROM {grade_grades} AS gg ";
            $str.= "WHERE gg.itemid={$forum->itemid}))";

            if(end($forums) != $forum)
            {
                $str.= ' OR ';
            }
        }

        return $str;
    }

    private function filter_out_all_non_student_posts(array $users)
    {
        $studentRoles = nlib\get_archetypes_roles(array('student'));

        $posts = array();
        foreach($users as $user) 
        {
            $userRoles = get_user_roles(\context_course::instance($user->courseid), $user->userid);

            if(nlib\is_user_have_role($studentRoles, $userRoles))
            {
                $posts[] = $user;
            }
        }

        return $posts;
    }

    private function add_neccessary_grades_params(array $grades) 
    {
        foreach($grades as $grade)
        {
            $params = $this->get_neccessary_grade_params($grade);

            $grade->itemid = $params->itemid;
            $grade->itemname = $params->itemname;
            $grade->itemmodule = $params->itemmodule;
            $grade->iteminstance = $params->iteminstance;
        }
    }

    private function get_neccessary_grade_params(stdClass $grade)
    {
        global $DB;
        $sql = "SELECT id AS itemid, itemname, itemmodule, iteminstance 
                FROM {grade_items}
                WHERE itemmodule='forum' AND iteminstance=?";
        return $DB->get_record_sql($sql, array($grade->forum));
    }


}

class GlobalManagerForumUnratedPostsGetter extends ForumUnratedPostsGetter
{
    protected function get_graded_forums()
    {
        global $DB;
        $sql = "SELECT gi.id as itemid, gi.itemname, gi.iteminstance, gi.itemmodule
                FROM {forum} AS f 
                INNER JOIN {grade_items} AS gi
                ON f.id = gi.iteminstance
                WHERE assessed <> 0 AND gi.itemmodule='forum' AND gi.hidden=0";
        return $DB->get_records_sql($sql, array());
    }
}

abstract class LocalForumUnratedPostsGetter extends ForumUnratedPostsGetter 
{
    protected $archetypeRoles;

    protected function get_graded_forums()
    {
        global $DB;
        $sql = "SELECT gi.id as itemid, gi.itemname, gi.iteminstance, gi.itemmodule
                FROM {forum} AS f 
                INNER JOIN {grade_items} AS gi
                ON f.id = gi.iteminstance
                WHERE assessed <> 0 AND gi.itemmodule='forum' AND gi.hidden=0";
        
        $forums = $DB->get_records_sql($sql, array());
        $forums = $this->filter_out_all_non_user_forums($forums);

        return $forums;
    }

    private function filter_out_all_non_user_forums($forums)
    {
        global $USER;

        $userForums = array();
        foreach($forums as $forum)
        {
            $cmid = nlib\get_course_module_id($forum);
            $userRoles = get_user_roles(\context_module::instance($cmid), $USER->id);

            if(nlib\is_user_have_role($this->archetypeRoles, $userRoles))
            {
                $userForums[] = $forum;
            }

        }
        return $userForums;
    }
}

class LocalManagerForumUnratedPostsGetter extends LocalForumUnratedPostsGetter 
{
    function __construct() 
    {
        $this->archetypeRoles = nlib\get_archetypes_roles(array('manager'));
    }
}

class LocalTeacherForumUnratedPostsGetter extends LocalForumUnratedPostsGetter 
{
    function __construct() 
    {
        $this->archetypeRoles = nlib\get_archetypes_roles(array('teacher', 'editingteacher'));
    }
}


