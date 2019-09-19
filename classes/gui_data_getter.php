<?php 

use need_to_check_lib as nlib;

class GuiDataGetter 
{
    private $ungradedGrades;
    private $courses;
    private $dbManager;

    function __construct(array $grades)
    {
        $this->ungradedGrades = $grades;
        $this->courses = array();
        $this->dbManager = new DatabaseTeacherTableManager();
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
                // Using keys significantly speeds up the function.
                $coursekey = null;
                $teacherkey = null;
                $itemkey = null;

                $this->handle_grade_course($grade, $coursekey);
                $this->handle_grade_teachers($grade, $coursekey, $teacherkey, $itemkey);
                // Item handle in handle_grade_teachers because one grade can have several teachers
            }
        }
    }

    private function is_item_finished($grade)
    {
        $item = new CheckingItem($grade);
        if($item->is_item_finished()) return true;
        else return false;
    }
    
    private function handle_grade_course($grade, &$coursekey)
    {
        $coursekey = $this->get_course_key($grade->courseid);
        
        // If the key is missing, then there is no course in the array.
        // Therefore, we add the course to the array and find its key.
        if(!isset($coursekey))
        {
            $this->add_course_to_array($grade);
            $coursekey = $this->get_course_key($grade->courseid);
        }   
    }

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

    private function handle_grade_teachers($grade, $coursekey, &$teacherkey, $itemkey)
    {
        $teachers = $this->get_grade_teachers($grade);

        foreach($teachers as $teacher)
        {
            $teacherkey = $this->get_teacher_key($coursekey, $teacher);

            // If the key is missing, then there is no teacher in the array.
            // Therefore, we add the teacher to the array and find its key.
            if(!isset($teacherkey))
            {
                $this->add_teacher_to_array($coursekey, $grade, $teacher);
                $teacherkey = $this->get_teacher_key($coursekey, $teacher);
            }

            $this->handle_grade_item($grade, $coursekey, $teacherkey, $itemkey);
        }
    }

    /**
     * Returns all teachers which belongs to current student grade.
     */
    private function get_grade_teachers(stdClass $grade)
    {
        $teachers = $this->get_item_teachers($grade);

        $gradeTeachers = array();
        foreach($teachers as $teacher)
        {
            if($this->is_student_grade_belong_to_teacher($grade, $teacher))
            {
                $gradeTeachers[] = $teacher;
            }
        }

        // Non-existent teacher is required to correctly display information  
        // about a course in which there is no teacher.
        if(empty($gradeTeachers))
        {
            $gradeTeachers[] = $this->get_non_existent_teacher();
        }

        return $gradeTeachers;
    }

    /**
     * Returns all teachers which belongs to current item.
     */
    private function get_item_teachers(stdClass $grade) : array
    {
        $teachers = $this->get_teachers_from_database($grade);

        if(empty($teachers))
        {
            $this->dbManager->update_grade_teachers($grade);
            $teachers = $this->get_teachers_from_database($grade);
        }

        return $teachers;
    }

    /**
     * Returns teachers from block_need_to_check_teachers database table.
     */
    private function get_teachers_from_database(stdClass $grade) : array 
    {
        global $DB;
        $conditions = array('itemid'=>$grade->itemid);
        $queries = $DB->get_records('block_need_to_check_teachers', $conditions);

        $teachers = array();
        foreach($queries as $query)
        {
            $teachers[] = $query->teacherid;
        }

        return $teachers;
    }

    private function is_student_grade_belong_to_teacher($grade, $teacherid) : bool 
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

    /**
     * Non-existent teacher is required to correctly display information  
     * about a course in which there is no teacher.
     */
    private function get_non_existent_teacher() : int
    {
        return 0;
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

    private function handle_grade_item($grade, $coursekey, $teacherkey, $itemkey)
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

}

