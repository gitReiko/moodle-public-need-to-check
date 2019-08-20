<?php

namespace need_to_check;

class ManagerGUI 
{
    private $courses;

    function __construct(array $courses)
    {
        $this->courses = $courses;
    }

    public function display() : string 
    {
        $gui = '';

        foreach($this->courses as $course)
        {
            $gui.= $this->get_course_string($course);

            foreach($course->get_teachers() as $teacher)
            {
                $gui.= $this->get_teacher_string($teacher);

                foreach($teacher->get_items() as $item)
                {
                    $gui.= $this->get_item_string($item);
                }
            }
        }

        return $gui;
    }

    private function get_course_string($course) : string 
    {
        $str = '<p>'.$course->get_name().' (';
        $str.= $course->get_unchecked_works_count().' - ';
        $str.= '<span style="color: red;">'.$course->get_expired_works_count().'</span>)</p>';
        return $str;
    }

    private function get_teacher_string($teacher) : string 
    {
        $str = '<p style="margin-left: 10px;">'.$teacher->get_name().' (';
        $str.= $teacher->get_unchecked_works_count().' - ';
        $str.= '<span style="color: red;">'.$teacher->get_expired_works_count().'</span>)</p>';
        return $str;
    }

    private function get_item_string($item) : string 
    {
        $str = '<a href="'.$item->get_link().'" target="_blank"><p style="margin-left: 20px;">'.$item->get_name().' (';
        $str.= $item->get_unchecked_works_count().' - ';
        $str.= '<span style="color: red;">'.$item->get_expired_works_count().'</span>)</p></a>';
        return $str;
    }

}
