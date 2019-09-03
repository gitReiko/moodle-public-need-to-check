<?php 

use need_to_check_lib as nlib;

class GuiDataGetter 
{
    private $ungradedGrades;
    private $courses;

    function __construct(array $grades)
    {
        $this->ungradedGrades = $grades;
        $this->courses = array(); 
    }

    public function get_manager_courses_array() 
    {
        $this->parse_ungraded_grades();
        $this->calculate_unchecked_and_expired_works();
        $this->sort_teachers_and_items();

        return $this->courses;
    }

    private function parse_ungraded_grades() 
    {
        foreach($this->ungradedGrades as $grade)
        {
            if($this->is_item_finished($grade))
            {
                $coursekey = null;
                $teacherkey = null;
                $itemkey = null;

                $this->handle_course($grade, $coursekey);
    
                $cm = $this->get_course_module($grade);
                $teachers = $this->get_teachers($grade, $cm);

                if(!count($teachers))
                {
                    $teacher = 0;
                    $this->handle_teacher($grade, $coursekey, $teacherkey, $teacher);
                    $this->handle_item($grade, $coursekey, $teacherkey, $itemkey);
                }

                foreach($teachers as $teacher)
                {
                    $this->handle_teacher($grade, $coursekey, $teacherkey, $teacher);

                    if($this->is_student_grade_belong_to_teacher($grade, $coursekey, $teacherkey, $teacher))
                    {
                        $this->handle_item($grade, $coursekey, $teacherkey, $itemkey);
                    }
                }
            }
        }
    }
    
    private function handle_course($grade, &$coursekey)
    {
        $coursekey = $this->get_course_key($grade->courseid);
        if(!isset($coursekey))
        {
            $this->add_course_to_array($grade);
            $coursekey = $this->get_course_key($grade->courseid);
        }   
    }

    private function handle_teacher($grade, $coursekey, &$teacherkey, $teacher)
    {
        $teacherkey = $this->get_teacher_key($coursekey, $teacher);
        if(!isset($teacherkey))
        {
            $this->add_teacher_to_array($coursekey, $grade, $teacher);
            $teacherkey = $this->get_teacher_key($coursekey, $teacher);
        }
    }

    private function is_student_grade_belong_to_teacher($grade, $coursekey, $teacherkey, $teacherid) : bool 
    {
        $studentid = $grade->userid;

        $studentGroups = $this->get_user_groups_from_course($grade->courseid, $studentid);
        $teacherGroups = $this->get_user_groups_from_course($grade->courseid, $teacherid);

        foreach($teacherGroups as $teacherGroup)
        {
            foreach($studentGroups as $studentGroup)
            {
                if($teacherGroup == $studentGroup) return true;
            }
        }

        return false;
    }

    private function get_user_groups_from_course($courseid, $userid)
    {
        global $DB;
        $sql = 'SELECT g.id
                FROM {groups} as g
                INNER JOIN {groups_members} as gm
                ON g.id = gm.groupid
                WHERE g.courseid = ? AND gm.userid = ?';
        $conditions = array($courseid, $userid);
        $queryGroups = $DB->get_records_sql($sql, $conditions);

        $groups = array();
        foreach($queryGroups as $group)
        {
            $groups[] = $group->id;
        }

        return $groups;
    }

    private function handle_item($grade, $coursekey, $teacherkey, $itemkey)
    {
        $itemkey = $this->get_item_key($coursekey, $teacherkey, $grade);
        if(!isset($itemkey))
        {
            $this->add_item_to_array($coursekey, $teacherkey, $grade);
        }
        else
        {
            $this->update_item($coursekey, $teacherkey, $itemkey, $grade);
        }      
    }

    /**
     * Looks for an array key for this course and returns it.
     */
    private function get_course_key(int $courseid)
    {
        foreach($this->courses as $key => $course)
        {
            if($course->get_id() == $courseid) return $key;
        }

        return null;
    }

    private function add_course_to_array(stdClass $grade) : void 
    {
        $course = new CheckingCourse($grade);
        $this->courses[] = $course;
    }

    private function get_course_module($grade)
    {   
        $cmid = nlib\get_course_module_id($grade);
        return get_coursemodule_from_id($grade->itemmodule, $cmid, 0, false, MUST_EXIST);
    }

    private function get_teachers(stdClass $grade, stdClass $cm) : array 
    {
        $teacherRoles = nlib\get_archetypes_roles(array('teacher', 'editingteacher'));
        $studentGroups = groups_get_activity_allowed_groups($cm);
        $users = $this->get_groups_members($studentGroups);
        
        $teachers = array();
        foreach($users as $user)
        {
            if(isset($user->id))
            {
                $cmid = nlib\get_course_module_id($grade);
                $userRoles = get_user_roles(\context_module::instance($cmid), $user->id);

                if(nlib\is_user_have_role($teacherRoles, $userRoles))
                {
                    $teachers[] = $user->id;
                }
            }

        }

        $teachers = array_unique($teachers);

        return $teachers;
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

    /**
     * Looks for an array key for this teacher and returns it.
     */
    private function get_teacher_key($coursekey, $teacherid)
    {
        foreach($this->courses[$coursekey]->get_teachers() as $key => $teacher)
        {
            if($teacher->get_id() == $teacherid) return $key;
        }

        return null;
    }

    private function add_teacher_to_array($coursekey, $grade, $teacherid) : void 
    {
        $teacher = new CheckingTeacher($grade, $teacherid);
        $this->courses[$coursekey]->add_teacher($teacher);
    }

    /**
     * Looks for an array key for this teacher and returns it.
     */
    private function get_item_key($coursekey, $teacherkey, $grade)
    {
        foreach($this->courses[$coursekey]->get_teachers()[$teacherkey]->get_items() as $key => $item)
        {
            if($item->get_id() == $grade->itemid) return $key;
        }

        return null;
    }

    private function add_item_to_array($coursekey, $teacherkey, $grade) : void 
    {
        $item = new CheckingItem($grade);
        $this->courses[$coursekey]->get_teachers()[$teacherkey]->add_item($item);
    }

    private function update_item($coursekey, $teacherkey, $itemkey, $grade) 
    {
        $item = $this->courses[$coursekey]->get_teachers()[$teacherkey]->get_items()[$itemkey];
        $item->update_works_count($grade);
    }

    private function calculate_unchecked_and_expired_works() : void
    {
        foreach($this->courses as $course)
        {
            $courseUncheckedCount = 0;
            $courseExpiredCount = 0;

            foreach($course->get_teachers() as $teacher)
            {
                $teacherUncheckedCount = 0;
                $teacherExpiredCount = 0;

                foreach($teacher->get_items() as $item)
                {
                    $teacherUncheckedCount += $item->get_unchecked_works_count();
                    $teacherExpiredCount += $item->get_expired_works_count();
                }

                $teacher->set_unchecked_works_count($teacherUncheckedCount);
                $teacher->set_expired_works_count($teacherExpiredCount);

                $courseUncheckedCount += $teacherUncheckedCount;
                $courseExpiredCount += $teacherExpiredCount;
            }

            $course->set_unchecked_works_count($courseUncheckedCount);
            $course->set_expired_works_count($courseExpiredCount);
        }
    }

    private function sort_teachers_and_items() : void 
    {
        foreach($this->courses as $course)
        {
            $teachers = $course->get_teachers();
            usort($teachers, array($this, 'cmp_gui_data_teachers_or_items'));
            $course->set_teachers($teachers);

            foreach($teachers as $teacher)
            {
                $items = $teacher->get_items();
                usort($items, array($this, 'cmp_gui_data_teachers_or_items'));
                $teacher->set_items($items);
            }
        }
    }

    private function cmp_gui_data_teachers_or_items($a, $b)
    {
        return strcmp($a->get_name(), $b->get_name());
    }

    private function is_item_finished($grade)
    {
        $item = new CheckingItem($grade);
        if($item->is_item_finished()) return true;
        else return false;
    }
}

