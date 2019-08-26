<?php

class NeedToCheckTeacherGUI 
{
    private $courses;

    function __construct($grades)
    {
        $data = new GuiDataGetter($grades);
        $this->courses = $data->get_manager_courses_array();
    }

    public function get_gui()
    {
        $gui = '';

        foreach($this->courses as $course)
        {
            $gui.= $this->get_course_row($course);

            foreach($course->get_teachers() as $teacher)
            {
                foreach($teacher->get_items() as $item)
                {
                    $gui.= $this->get_item_row($item);
                }
            }
        }

        return $gui;
    }

    private function get_course_row($course) : string 
    {
        $row = '<div>';
        $row.= $course->get_name();
        $row.= $this->get_unchecked_and_expired_string($course);
        $row.= '</div>';
        return $row;
    }

    private function get_unchecked_and_expired_string($value) : string 
    {
        // (xx - xx) - xx - works count.
        $str = ' ('.$value->get_unchecked_works_count().' - ';
        $str.= '<span style="color: red;">'.$value->get_expired_works_count().'</span>)';
        return $str;
    }

    private function get_item_row($item) : string 
    {
        $row = '<a href="'.$item->get_link().'" target="_blank">';
        $row.= '<div style="margin-left: 20px !important;">';
        $row.= $item->get_name();
        $row.= $this->get_unchecked_and_expired_string($item);
        $row.= '</div>';
        $row.= '</a>';
        return $row;
    }


}
