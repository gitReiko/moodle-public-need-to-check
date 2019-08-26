<?php


class TeacherMainClass 
{

    function __construct()
    {
        $grades = new TeacherGradeGradesGetter();
        $grades->get_grades();
    }

    public function get_gui()
    {
        return "I'm work!!!";
    }




}
