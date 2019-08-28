<?php 

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
                $coursekey = $this->get_course_key($grade->courseid);
                if(!isset($coursekey))
                {
                    $this->add_course_to_array($grade);
                    $coursekey = $this->get_course_key($grade->courseid);
                }
    
                $teacherkey = $this->get_teacher_key($coursekey, $grade);
                if(!isset($teacherkey))
                {
                    $this->add_teacher_to_array($coursekey, $grade);
                    $teacherkey = $this->get_teacher_key($coursekey, $grade);
                }
    
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

    /**
     * Looks for an array key for this teacher and returns it.
     */
    private function get_teacher_key($coursekey, $grade)
    {
        foreach($this->courses[$coursekey]->get_teachers() as $key => $teacher)
        {
            if(empty($teacherid))
            {
                $teacherid = $teacher->get_teacher_id($grade->courseid, $grade->userid, $grade->iteminstance);
            }

            if($teacher->get_id() == $teacherid) return $key;
            
        }

        return null;
    }

    private function add_teacher_to_array($coursekey, $grade) : void 
    {
        $teacher = new CheckingTeacher($grade);
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

