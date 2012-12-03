<?php

namespace FSStack\Gruppe\Controllers\post;

use \FSStack\Gruppe\Models;

class Vote extends PostController
{
    public function __post_index()
    {
        $vote = $this->request->post('vote');

        return Models\User::current()->vote($this->group, $this->post, $vote);
    }
}
