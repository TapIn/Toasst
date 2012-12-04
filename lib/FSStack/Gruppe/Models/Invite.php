<?php

namespace FSStack\Gruppe\Models;

class Invite extends \TinyDb\Orm
{
    public static $table_name = 'invites';
    public static $primary_key = 'code';

    protected $code;
    protected $to_join_groupIDs;

    protected $created_at;
    protected $modified_at;

    public function __get_to_join_groups()
    {
        $groups = array();
        foreach (explode(',', $this->to_join_groupIDs) as $groupID) {
            $groups[] = new Group($groupID);
        }

        return $groups;
    }

    public static function all()
    {
        return new \TinyDb\Collection('\FSStack\Gruppe\Models\Invite', \TinyDb\Sql::create()
                                      ->order_by('modified_at'));
    }
}
