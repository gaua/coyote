<?php

namespace Coyote\Repositories\Criteria\Topic;

use Coyote\Repositories\Contracts\RepositoryInterface as Repository;

class OnlyThoseWithAccess extends \Coyote\Repositories\Criteria\Forum\OnlyThoseWithAccess
{
    /**
     * @param \Illuminate\Database\Eloquent\Builder $model
     * @param Repository $repository
     * @return mixed
     */
    public function apply($model, Repository $repository)
    {
        return $this->subQuery($model, $repository, 'topics.forum_id');
    }
}
