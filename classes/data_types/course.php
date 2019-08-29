<?php

class CheckingCourse extends ParentType
{
    private $link;
    private $teachers;

    function __construct(stdClass $grade)
    {
        $this->id = $grade->courseid;
        $this->name = $grade->coursename;

        $this->uncheckedWorksCount = 0;
        $this->expiredWorksCount = 0;

        $this->link = $this->get_course_link($grade->courseid);
        $this->teachers = array();
    }

    public function get_link() : string 
    {
        return $this->link;
    }

    public function get_teachers() : array
    {
        return $this->teachers;
    }

    public function set_teachers(array $teachers) : void 
    {
        $this->teachers = $teachers;
    }

    public function add_teacher(CheckingTeacher $teacher) : void 
    {
        $this->teachers[] = $teacher;
    }

    private function get_course_link(int $courseid) : string 
    {
        return "/course/view.php?id=$courseid";
    }

}
