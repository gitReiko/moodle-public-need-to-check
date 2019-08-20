<?php

abstract class ParentType 
{
    protected $id;
    protected $name;
    protected $uncheckedWorksCount;
    protected $expiredWorksCount;

    public function get_id()
    {
        return $this->id;
    }

    public function get_name() : string 
    {
        return $this->name;
    }

    public function get_unchecked_works_count() : int 
    {
        return $this->uncheckedWorksCount;
    }

    public function get_expired_works_count() : int
    {
        return $this->expiredWorksCount;
    }

    public function set_unchecked_works_count(int $count) : void 
    {
        $this->uncheckedWorksCount = $count;
    }

    public function set_expired_works_count(int $count) : void 
    {
        $this->expiredWorksCount = $count;
    }


}
