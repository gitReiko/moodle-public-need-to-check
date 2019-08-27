<?php

class NeedToCheckManagerGUI 
{
    private $courses;

    function __construct($grades)
    {
        $data = new GuiDataGetter($grades);
        $this->courses = $data->get_manager_courses_array();
    }

    public function get_gui() : string 
    {
        $gui = '';
        $gui.= '<h6><u>'.get_string('teachers_works', 'block_need_to_check').':</u></h6>';

        foreach($this->courses as $course)
        {
            $gui.= $this->get_course_row($course);

            foreach($course->get_teachers() as $teacher)
            {
                $teacherid = $this->get_teacherid($course, $teacher);

                $gui.= $this->get_teacher_row($teacher, $teacherid);

                $gui.= $this->get_items_container_begin($teacherid);
                foreach($teacher->get_items() as $item)
                {
                    $gui.= $this->get_item_row($item);
                }
                $gui.= $this->get_items_container_end();
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

    private function get_teacher_row($teacher, $teacherid) : string 
    {
        $row = '<div class="chekingTeacher" onclick="hide_or_show_block(`'.$teacherid.'`)" ';
        if(!empty($teacher->get_contacts())) $row.= 'title="'.$teacher->get_contacts().'"';
        $row.= '>⇩';

        if(!empty($teacher->get_name())) $row.= $teacher->get_name();
        else $row .= get_string('not_assigned', 'block_need_to_check');

        $row.= $this->get_unchecked_and_expired_string($teacher);
        $row.= '</div>';
        return $row;
    }

    private function get_item_row($item) : string 
    {
        $row = '<a href="'.$item->get_link().'" target="_blank">';
        $row.= '<div class="chekingItem">';
        $row.= $item->get_name();
        $row.= $this->get_unchecked_and_expired_string($item);
        $row.= '</div>';
        $row.= '</a>';
        return $row;
    }

    private function get_unchecked_and_expired_string($value) : string 
    {
        // (xx - xx) - xx - works count.
        $str = ' ('.$value->get_unchecked_works_count().' - ';
        $str.= '<span style="color: red;">'.$value->get_expired_works_count().'</span>)';
        return $str;
    }

    private function get_items_container_begin($teacherid) : string 
    {
        return "<div id='{$teacherid}' style='display: none;'>";
    }

    private function get_items_container_end() : string 
    {
        return '</div>';
    }

    private function get_teacherid($course, $teacher) : string 
    {
        return "{$course->get_id()}+{$teacher->get_id()}";
    }

}
