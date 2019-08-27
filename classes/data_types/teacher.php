<?php

use need_to_check_lib as nlib;

class CheckingTeacher extends ParentType
{

    // params neccessary for gui
    private $contacts;
    private $items;

    // technical params
    private $studentid;
    private $teacherRoles;
    private $activityTeachers;

    function __construct(stdClass $grade)
    {
        // Because teacher id is based on student’s id.
        $this->studentid = $grade->userid;
        $this->teacherRoles = nlib\get_archetypes_roles(array('teacher', 'editingteacher'));

        $this->id = $this->get_teacher_id($grade->courseid, $grade->userid, $grade->iteminstance);
        $this->name = $this->get_teacher_name();
        $this->contacts = $this->get_teacher_contacts();

        $this->uncheckedWorksCount = 0;
        $this->expiredWorksCount = 0;

        $this->items = array();
    }

    public function get_contacts() 
    {
        return $this->contacts;
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

    private function get_teacher_contacts()
    {
        global $DB;
        $teacher = $DB->get_record('user', array('id'=>$this->id), 'email, phone1, phone2');

        $contacts = '';
        $newline = '&#013;';
        if(!empty($teacher->email)) $contacts.= 'email: '.$teacher->email.$newline;
        if(!empty($teacher->phone1)) $contacts.= 'phone1: '.$teacher->phone1.$newline;
        if(!empty($teacher->phone2)) $contacts.= 'phone2: '.$teacher->phone2;

        return $contacts;
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

                if(nlib\is_user_have_role($this->teacherRoles, $userRoles))
                {
                    $teachers[] = $user->id;
                }
            }

        }

        $teachers = array_unique($teachers);

        $this->activityTeachers = $teachers;

        return $teachers;
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
