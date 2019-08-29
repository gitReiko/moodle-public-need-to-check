<?php

use need_to_check_lib as nlib;

class CheckingTeacher extends ParentType
{

    // params neccessary for gui
    private $contacts;
    private $items;

    function __construct(stdClass $grade, $teacherid)
    {
        $this->id = $teacherid;
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

    public function set_items(array $items)
    {
        $this->items = $items;
    }


    private function get_teacher_contacts()
    {
        $contacts = '';

        if(!empty($this->id))
        {
            global $DB;
            $teacher = $DB->get_record('user', array('id'=>$this->id), 'email, phone1, phone2');
            
            $newline = '&#013;';
            if(!empty($teacher->email))
            {
                $contacts.= get_string('email', 'block_need_to_check').': '.$teacher->email.$newline;
            }
            if(!empty($teacher->phone1))
            {
                $contacts.= get_string('phone', 'block_need_to_check').' 1: '.$teacher->phone1.$newline;
            }
            if(!empty($teacher->phone2))
            {
                $contacts.= get_string('phone', 'block_need_to_check').' 2: '.$teacher->phone2;
            }
        }

        return $contacts;
    }

    private function get_teacher_name() : string 
    {
        if(empty($this->id))
        {
            return '';
        } 
        else
        {
            return $this->get_user_name($this->id);
        }
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
