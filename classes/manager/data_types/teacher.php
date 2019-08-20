<?php

class CheckingTeacher extends ParentType
{

    // params neccessary for gui
    private $items;

    // technical params
    private $studentid;
    private $teacherRoles;
    private $activityTeachers;

    function __construct(stdClass $grade)
    {
        // Because teacher id is based on student’s id.
        $this->studentid = $grade->userid;
        $this->teacherRoles = $this->get_teacher_roles();

        $this->id = $this->get_teacher_id($grade->courseid, $grade->userid, $grade->iteminstance);
        $this->name = $this->get_teacher_name();

        $this->uncheckedWorksCount = 0;
        $this->expiredWorksCount = 0;

        $this->items = array();
    }

    public function get_items() : array
    {
        return $this->items;
    }

    public function add_item(CheckingItem $item) : void
    {
        $this->items[] = $item;
    }

    /**
     * Returns teacher(s) id.
     * 
     * The function exists because id cannot be taken directly.
     */
    public function get_teacher_id(int $courseid, int $studentid, int $iteminstance)
    {
        $studentGroups = groups_get_user_groups($courseid, $studentid);
        $groupsMembers = $this->get_groups_members($studentGroups);
        $teachers = $this->get_teachers($groupsMembers, $courseid);

        $teachersCount = count($teachers);
        if($teachersCount)
        {
            $teacherid = '';
            for($i = 0; $i < $teachersCount; $i++)
            {
                $teacherid.= $teachers[$i];

                if($teachersCount > ($i+1))
                {
                    $teacherid.= '+';
                }
            }
        }
        else
        {
            $teacherid = 0;
        }

        return $teacherid;
    }

    private function get_teacher_roles() : array 
    {
        global $DB;
        $sql = "SELECT id FROM {role} WHERE archetype IN ('teacher', 'editingteacher')";
        return $DB->get_records_sql($sql);
    }

    private function get_groups_members(array $groups) : array
    {
        $users = array();
        foreach($groups as $group)
        {
            $users = array_merge($users, groups_get_members(reset($group), 'u.id'));
        }
        return $users;
    }

    private function get_teachers(array $users, int $courseid) : array 
    {
        $teachers = array();
        foreach($users as $user)
        {
            if(isset($user->id))
            {
                $userRoles = get_user_roles(context_course::instance($courseid), $user->id);

                if($this->is_user_teacher($userRoles))
                {
                    $teachers[] = $user->id;
                }
            }

        }

        $teachers = array_unique($teachers);

        $this->activityTeachers = $teachers;

        return $teachers;
    }

    private function is_user_teacher(array $userRoles) : bool 
    {
        foreach($userRoles as $userRole)
        {
            foreach($this->teacherRoles as $teacherRole)
            {
                
                if($userRole->roleid == $teacherRole->id) return true;
            }
        }
    
        return false;
    }

    private function get_teacher_name() : string 
    {
        $teachersCount = count($this->activityTeachers);
        $name = '';

        for($i = 0; $i < $teachersCount; $i++)
        {
            $name.= $this->get_user_name($this->activityTeachers[$i]);

            if($teachersCount > ($i+1))
            {
                $name.= ', ';
            }
        }

        return $name;
    }

    private function get_user_name(int $id) : string
    {
        global $DB;

        $user = $DB->get_record('user', array('id'=>$id), 'id, firstname, lastname');

        $temp = explode(' ', $user->firstname);
        $str = ' ';

        foreach($temp as $key2 => $name)
        {
            $str .= mb_substr($name, 0, 1).'.';
        }

        return $user->lastname.$str;
    }


}
