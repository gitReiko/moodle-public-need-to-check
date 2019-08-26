<?php

class CheckingCourse extends ParentType
{
    private $teachers;

    function __construct(stdClass $grade)
    {
        $this->id = $grade->courseid;
        $this->name = $grade->coursename;

        $this->uncheckedWorksCount = 0;
        $this->expiredWorksCount = 0;

        $this->teachers = array();
    }

    public function get_teachers() : array
    {
        return $this->teachers;
    }

    public function add_teacher(CheckingTeacher $teacher) : void 
    {
        $this->teachers[] = $teacher;
    }

}
